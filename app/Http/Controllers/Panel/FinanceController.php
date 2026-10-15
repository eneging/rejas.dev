<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\FinancialCategory;
use App\Models\Payment;
use App\Models\Project;
use App\Models\SubscriptionCharge;
use App\Support\Audit;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function index(Request $request): View
    {
        $from = Carbon::parse($request->query('desde', now()->startOfYear()->toDateString()))->startOfDay();
        $to = Carbon::parse($request->query('hasta', now()->endOfMonth()->toDateString()))->endOfDay();
        $showMargins = $request->user()->can(Permissions::FINANCE_VIEW_MARGINS);

        // Ingresos = solo dinero efectivamente recibido (pagos registrados).
        $payments = Payment::with('client', 'project')->whereBetween('paid_at', [$from, $to])->orderByDesc('paid_at')->get();
        $expenses = $showMargins ? Expense::with('category', 'project')->whereBetween('spent_at', [$from, $to])->orderByDesc('spent_at')->get() : collect();

        $months = collect();
        for ($m = $from->copy()->startOfMonth(); $m->lte($to); $m->addMonth()) {
            $key = $m->format('Y-m');
            $months[$key] = [
                'label' => $m->translatedFormat('M Y'),
                'income' => $payments->filter(fn ($p) => $p->paid_at->format('Y-m') === $key)->sum('amount'),
                'expense' => $expenses->filter(fn ($e) => $e->spent_at->format('Y-m') === $key)->sum('amount'),
            ];
        }

        $receivables = Project::with(['client', 'payments', 'paymentSchedules'])
            ->where('status', '!=', 'cancelado')
            ->get()
            ->filter(fn (Project $p) => $p->balance() > 0)
            ->sortByDesc(fn (Project $p) => $p->paymentStatus() === 'vencido');

        return view('panel.finance.index', [
            'from' => $from, 'to' => $to,
            'payments' => $payments, 'expenses' => $expenses, 'months' => $months,
            'receivables' => $receivables,
            'pendingCharges' => SubscriptionCharge::with('subscription.client')->where('status', 'por_cobrar')->orderBy('due_date')->get(),
            'showMargins' => $showMargins,
            'margins' => $showMargins ? Project::with('client')->whereNotNull('estimated_cost')->latest()->limit(20)->get() : collect(),
            'categories' => FinancialCategory::where('type', 'gasto')->orderBy('name')->get(),
            'projects' => $showMargins ? Project::orderBy('name')->get(['id', 'code', 'name']) : collect(),
        ]);
    }

    public function storeExpense(Request $request): RedirectResponse
    {
        $expense = Expense::create($request->validate([
            'description' => ['required', 'string', 'max:200'],
            'spent_at' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'in:PEN,USD'],
            'financial_category_id' => ['nullable', 'exists:financial_categories,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
        ]) + ['recorded_by' => $request->user()->id]);
        Audit::record('gasto.registrado', $expense, ['monto' => $expense->amount]);

        return back()->with('status', 'Gasto registrado.');
    }
}

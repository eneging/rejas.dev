<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriptionCharge;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionBilling;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __construct(private SubscriptionBilling $billing) {}

    public function index(Request $request): View
    {
        return view('panel.subscriptions.index', [
            'subscriptions' => Subscription::with(['client', 'charges' => fn ($q) => $q->where('status', 'por_cobrar')])
                ->when($request->query('estado'), fn ($q, $s) => $q->where('status', $s))
                ->orderBy('next_due_date')
                ->paginate(25)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('panel.subscriptions.create', [
            'clients' => Client::orderBy('name')->get(),
            'plans' => SubscriptionPlan::where('is_active', true)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'subscription_plan_id' => ['nullable', 'exists:subscription_plans,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'service' => ['required', 'string', 'max:200'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'in:PEN,USD'],
            'periodicity' => ['required', Rule::in(array_keys(Subscription::PERIODICITIES))],
            'starts_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $subscription = Subscription::create($data + ['next_due_date' => $data['starts_at'], 'status' => 'activa']);
        Audit::record('suscripcion.creada', $subscription);
        $this->billing->generateDueCharges();

        return redirect()->route('panel.subscriptions.show', $subscription)->with('status', 'Suscripción registrada.');
    }

    public function show(Subscription $subscription): View
    {
        return view('panel.subscriptions.show', [
            'subscription' => $subscription->load('client', 'plan', 'project', 'charges.payments'),
            'paymentMethods' => Payment::METHODS,
        ]);
    }

    public function update(Request $request, Subscription $subscription): RedirectResponse
    {
        $subscription->update($request->validate([
            'status' => ['required', Rule::in(array_keys(Subscription::STATUSES))],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'next_due_date' => ['required', 'date'],
        ]));
        Audit::changes('suscripcion.actualizada', $subscription);

        return back()->with('status', 'Suscripción actualizada.');
    }

    public function generate(): RedirectResponse
    {
        $count = $this->billing->generateDueCharges();

        return back()->with('status', "Se generaron {$count} cargo(s) por cobrar.");
    }

    public function pay(Request $request, SubscriptionCharge $charge): RedirectResponse
    {
        abort_if($charge->status !== 'por_cobrar', 422, 'El cargo no está pendiente.');

        $this->billing->recordPayment($charge, $request->validate([
            'paid_at' => ['required', 'date', 'before_or_equal:today'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', Rule::in(array_keys(Payment::METHODS))],
            'reference' => ['nullable', 'string', 'max:120'],
        ]));

        return back()->with('status', 'Cobro registrado.');
    }
}

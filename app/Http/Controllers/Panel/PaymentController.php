<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Project;
use App\Support\Audit;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'payment_schedule_id' => ['nullable', Rule::exists('payment_schedules', 'id')->where('project_id', $project->id)],
            'paid_at' => ['required', 'date', 'before_or_equal:today'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', Rule::in(array_keys(Payment::METHODS))],
            'reference' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'receipt' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        if ($file = $request->file('receipt')) {
            $data['receipt_path'] = $file->store("comprobantes/{$project->id}", 'local');
        }
        unset($data['receipt']);

        $payment = $project->payments()->create($data + [
            'client_id' => $project->client_id,
            'currency' => $project->currency,
            'recorded_by' => $request->user()->id,
        ]);
        Audit::record('pago.registrado', $project, ['pago_id' => $payment->id, 'monto' => $payment->amount, 'metodo' => $payment->method]);

        return back()->with('status', 'Pago registrado. Saldo pendiente: '.Money::format($project->balance(), $project->currency).'.');
    }

    public function storeSchedule(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'schedules' => ['required', 'array', 'min:1'],
            'schedules.*.label' => ['required', 'string', 'max:120'],
            'schedules.*.amount' => ['required', 'numeric', 'min:0'],
            'schedules.*.due_date' => ['nullable', 'date'],
        ]);

        $total = collect($data['schedules'])->sum('amount');
        if (abs($total - (float) $project->contract_amount) > 0.01) {
            return back()->withErrors(['schedules' => 'La suma de los hitos debe coincidir con el monto del proyecto ('.$project->contract_amount.').']);
        }

        $project->paymentSchedules()->whereDoesntHave('payments')->delete();
        foreach (array_values($data['schedules']) as $i => $row) {
            $project->paymentSchedules()->create($row + [
                'position' => $i,
                'percentage' => $project->contract_amount > 0 ? round(100 * $row['amount'] / $project->contract_amount, 2) : null,
            ]);
        }
        Audit::record('plan_pagos.actualizado', $project, ['hitos' => $data['schedules']]);

        return back()->with('status', 'Plan de pagos actualizado.');
    }
}

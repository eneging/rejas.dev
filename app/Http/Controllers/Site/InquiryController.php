<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InquiryController extends Controller
{
    public function create(Request $request): View
    {
        return view('site.inquiry', [
            'serviceTypes' => config('rejas.service_types'),
            'selected' => $request->query('servicio'),
            'plans' => config('rejas.workshop.plans'),
            'selectedPlan' => $request->query('plan'),
        ]);
    }

    /**
     * Crea un prospecto con evidencia de aceptación de la política de privacidad.
     * El consentimiento comercial es opcional y se registra por separado (nunca premarcado).
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:30'],
            'company' => ['nullable', 'string', 'max:160'],
            'service_type' => ['required', Rule::in(array_keys(config('rejas.service_types')))],
            'plan' => ['nullable', Rule::in(array_keys(config('rejas.workshop.plans')))],
            'description' => ['required', 'string', 'min:10', 'max:3000'],
            'privacy' => ['accepted'],
            'marketing' => ['nullable', 'boolean'],
            'website' => ['prohibited'], // honeypot anti-spam
        ], [
            'privacy.accepted' => 'Debes aceptar la política de privacidad para enviar la solicitud.',
        ]);

        DB::transaction(function () use ($data, $request) {
            $lead = Lead::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'company' => $data['company'] ?? null,
                'service_type' => $data['service_type'],
                'plan_interest' => $data['plan'] ?? null,
                'description' => $data['description'],
                'source' => 'web',
            ]);

            $evidence = [
                'document_version' => config('rejas.legal.privacy_version'),
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
                'accepted_at' => now(),
            ];

            $lead->consents()->create($evidence + ['type' => 'privacy_policy', 'accepted' => true]);
            $lead->consents()->create($evidence + ['type' => 'marketing', 'accepted' => $request->boolean('marketing')]);
        });

        $message = $data['service_type'] === 'taller'
            ? '¡Listo! Te contactaremos para activar tu prueba gratis de '.config('rejas.workshop.trial_days').' días.'
            : '¡Gracias! Recibimos tu solicitud y te contactaremos pronto.';

        return back()->with('status', $message);
    }
}

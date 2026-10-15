<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Lead;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function index(Request $request): View
    {
        $leads = Lead::query()
            ->when($request->query('estado'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('q'), fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%$term%")->orWhere('email', 'like', "%$term%")->orWhere('company', 'like', "%$term%")))
            ->with('assignee')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('panel.leads.index', compact('leads'));
    }

    public function show(Lead $lead): View
    {
        return view('panel.leads.show', [
            'lead' => $lead->load('consents', 'client', 'assignee'),
            'users' => User::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Lead $lead): RedirectResponse
    {
        $lead->update($request->validate([
            'status' => ['required', Rule::in(array_keys(Lead::STATUSES))],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
        ]));
        Audit::changes('prospecto.actualizado', $lead);

        return back()->with('status', 'Prospecto actualizado.');
    }

    public function convert(Lead $lead): RedirectResponse
    {
        if (! $lead->client_id) {
            $client = Client::create([
                'name' => $lead->name,
                'business_name' => $lead->company,
                'email' => $lead->email,
                'phone' => $lead->phone,
            ]);
            $lead->update(['client_id' => $client->id, 'status' => $lead->status === 'nuevo' ? 'contactado' : $lead->status]);
            Audit::record('prospecto.convertido', $lead, ['cliente_id' => $client->id]);
        }

        return redirect()->route('panel.quotes.create', ['client_id' => $lead->client_id, 'lead_id' => $lead->id])
            ->with('status', 'Cliente creado. Ahora puedes preparar la cotización.');
    }
}

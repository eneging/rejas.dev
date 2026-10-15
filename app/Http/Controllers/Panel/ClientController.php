<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $clients = Client::query()
            ->when($request->query('q'), fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%$term%")->orWhere('business_name', 'like', "%$term%")->orWhere('document_number', 'like', "%$term%")))
            ->withCount(['projects', 'subscriptions'])
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('panel.clients.index', compact('clients'));
    }

    public function create(): View
    {
        return view('panel.clients.form', ['client' => new Client]);
    }

    public function store(Request $request): RedirectResponse
    {
        $client = Client::create($this->validated($request));
        Audit::record('cliente.creado', $client);

        return redirect()->route('panel.clients.show', $client)->with('status', 'Cliente registrado.');
    }

    public function show(Client $client): View
    {
        return view('panel.clients.show', [
            'client' => $client->load(['contacts', 'quotes' => fn ($q) => $q->latest(), 'projects', 'subscriptions', 'payments' => fn ($q) => $q->latest('paid_at')]),
        ]);
    }

    public function edit(Client $client): View
    {
        return view('panel.clients.form', compact('client'));
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $client->update($this->validated($request));
        Audit::changes('cliente.actualizado', $client);

        return redirect()->route('panel.clients.show', $client)->with('status', 'Cliente actualizado.');
    }

    public function storeContact(Request $request, Client $client): RedirectResponse
    {
        $client->contacts()->create($request->validate([
            'name' => ['required', 'string', 'max:120'],
            'position' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]));

        return back()->with('status', 'Contacto agregado.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'business_name' => ['nullable', 'string', 'max:200'],
            // El documento de identidad solo se solicita cuando es necesario para contratar/facturar.
            'document_type' => ['nullable', 'in:DNI,RUC,CE'],
            'document_number' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:30'],
            'sector' => ['nullable', 'string', 'max:80'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
    }
}

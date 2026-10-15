<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Quote;
use App\Services\QuoteWorkflow;
use App\Support\Audit;
use App\Support\Numbering;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QuoteController extends Controller
{
    public function __construct(private QuoteWorkflow $workflow) {}

    public function index(Request $request): View
    {
        $quotes = Quote::with('client')
            ->when($request->query('estado'), fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('panel.quotes.index', compact('quotes'));
    }

    public function create(Request $request): View
    {
        $quote = new Quote([
            'client_id' => $request->query('client_id'),
            'lead_id' => $request->query('lead_id'),
            'currency' => config('rejas.base_currency'),
            'valid_until' => now()->addDays(15),
            'tax_rate' => 0,
            'conditions' => "Forma de pago: 50 % de adelanto y 50 % a la entrega.\nVigencia de la propuesta: 15 días.\nCambios de alcance se cotizan por separado.",
        ]);

        if ($quote->lead_id && ($lead = Lead::find($quote->lead_id))) {
            $quote->title = config('rejas.service_types')[$lead->service_type] ?? '';
            $quote->scope = $lead->description;
        }

        return view('panel.quotes.form', ['quote' => $quote, 'clients' => Client::orderBy('name')->get(), 'items' => [['description' => '', 'quantity' => 1, 'unit_price' => '']]]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $quote = DB::transaction(function () use ($data, $request) {
            $quote = Quote::create($this->quoteFields($data, $request) + [
                'number' => Numbering::next(Quote::class, 'COT'),
                'created_by' => $request->user()->id,
            ]);
            $this->syncItems($quote, $data['items']);
            if ($quote->lead && $quote->lead->status !== 'ganado') {
                $quote->lead->update(['status' => 'cotizado']);
            }

            return $quote;
        });

        Audit::record('cotizacion.creada', $quote, ['numero' => $quote->number]);

        return redirect()->route('panel.quotes.show', $quote)->with('status', 'Cotización creada.');
    }

    public function show(Quote $quote): View
    {
        return view('panel.quotes.show', ['quote' => $quote->load('client', 'items', 'versions.author', 'contract', 'project', 'author')]);
    }

    public function edit(Quote $quote): View
    {
        abort_unless($quote->isEditable(), 403, 'Esta cotización ya no se puede editar.');

        return view('panel.quotes.form', [
            'quote' => $quote,
            'clients' => Client::orderBy('name')->get(),
            'items' => $quote->items->map->only(['description', 'quantity', 'unit_price'])->all(),
        ]);
    }

    public function update(Request $request, Quote $quote): RedirectResponse
    {
        abort_unless($quote->isEditable(), 403, 'Esta cotización ya no se puede editar.');
        $data = $this->validated($request);

        DB::transaction(function () use ($quote, $data, $request) {
            $quote->update($this->quoteFields($data, $request));
            $this->syncItems($quote, $data['items']);
        });
        Audit::record('cotizacion.editada', $quote);

        return redirect()->route('panel.quotes.show', $quote)->with('status', $quote->status === 'enviada'
            ? 'Cotización actualizada. Vuelve a enviarla para registrar la nueva versión.'
            : 'Cotización actualizada.');
    }

    public function print(Quote $quote): View
    {
        return view('panel.quotes.print', ['quote' => $quote->load('client', 'items')]);
    }

    public function send(Request $request, Quote $quote): RedirectResponse
    {
        abort_unless($quote->isEditable(), 403);
        $data = $request->validate(['channel' => ['required', 'in:correo,whatsapp,presencial,otro']]);
        $this->workflow->send($quote, $data['channel']);

        return back()->with('status', "Envío registrado (versión {$quote->version}).");
    }

    public function accept(Request $request, Quote $quote): RedirectResponse
    {
        $data = $request->validate([
            'channel' => ['required', 'in:correo,whatsapp,firma,presencial,otro'],
            'evidence' => ['required', 'string', 'min:5', 'max:2000'],
            'create_project' => ['nullable', 'boolean'],
        ], ['evidence.required' => 'Describe la evidencia de aceptación (abrir el PDF no cuenta como aceptación).']);

        $project = $this->workflow->accept($quote, $data['channel'], $data['evidence'], $request->boolean('create_project', true));

        return $project
            ? redirect()->route('panel.projects.show', $project)->with('status', 'Cotización aceptada. Se creó el contrato (borrador) y el proyecto.')
            : back()->with('status', 'Cotización aceptada y contrato en borrador creado.');
    }

    public function reject(Quote $quote): RedirectResponse
    {
        $quote->update(['status' => 'rechazada']);
        $quote->lead?->update(['status' => 'perdido']);
        Audit::record('cotizacion.rechazada', $quote);

        return back()->with('status', 'Cotización marcada como rechazada.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'lead_id' => ['nullable', 'exists:leads,id'],
            'title' => ['required', 'string', 'max:200'],
            'scope' => ['nullable', 'string', 'max:10000'],
            'conditions' => ['nullable', 'string', 'max:5000'],
            'currency' => ['required', 'in:PEN,USD'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'delivery_days' => ['nullable', 'integer', 'min:1', 'max:730'],
            'valid_until' => ['nullable', 'date'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);
    }

    /** @param array<string, mixed> $data */
    private function quoteFields(array $data, Request $request): array
    {
        $fields = collect($data)->except(['items', 'estimated_cost'])->all();

        // El costo estimado (margen) solo lo registra quien puede ver márgenes.
        if ($request->user()->can(Permissions::FINANCE_VIEW_MARGINS)) {
            $fields['estimated_cost'] = $data['estimated_cost'] ?? null;
        }

        return $fields;
    }

    /** @param list<array<string, mixed>> $items */
    private function syncItems(Quote $quote, array $items): void
    {
        $quote->items()->delete();
        foreach (array_values($items) as $i => $item) {
            $quote->items()->create([
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => round($item['quantity'] * $item['unit_price'], 2),
                'position' => $i,
            ]);
        }
        $quote->recalculate();
    }
}

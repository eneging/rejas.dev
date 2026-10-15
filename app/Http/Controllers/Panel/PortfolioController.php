<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\PortfolioProject;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function index(): View
    {
        return view('panel.portfolio.index', ['items' => PortfolioProject::orderBy('position')->get()]);
    }

    public function create(): View
    {
        return view('panel.portfolio.form', ['item' => new PortfolioProject(['is_published' => false])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $item = PortfolioProject::create($this->validated($request));
        Audit::record('portafolio.creado', $item);

        return redirect()->route('panel.portfolio.index')->with('status', 'Caso agregado al portafolio.');
    }

    public function edit(PortfolioProject $portfolioProject): View
    {
        return view('panel.portfolio.form', ['item' => $portfolioProject]);
    }

    public function update(Request $request, PortfolioProject $portfolioProject): RedirectResponse
    {
        $portfolioProject->update($this->validated($request, $portfolioProject));
        Audit::changes('portafolio.actualizado', $portfolioProject);

        return redirect()->route('panel.portfolio.index')->with('status', 'Caso actualizado.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?PortfolioProject $item = null): array
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('title'))]);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'slug' => ['required', 'string', 'max:160', Rule::unique('portfolio_projects', 'slug')->ignore($item?->id)],
            'sector' => ['required', 'string', 'max:80'],
            'summary' => ['required', 'string', 'max:255'],
            'challenge' => ['nullable', 'string', 'max:5000'],
            'solution' => ['nullable', 'string', 'max:5000'],
            'results' => ['nullable', 'string', 'max:5000'],
            'technologies' => ['nullable', 'string', 'max:500'],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $data['technologies'] = array_values(array_filter(array_map('trim', explode(',', $data['technologies'] ?? ''))));
        $data['is_published'] = $request->boolean('is_published');
        $data['position'] ??= 0;

        return $data;
    }
}

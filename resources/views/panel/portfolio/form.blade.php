<x-layouts.panel :title="$item->exists ? 'Editar caso' : 'Nuevo caso'">
    <form method="POST" action="{{ $item->exists ? route('panel.portfolio.update', $item) : route('panel.portfolio.store') }}" class="card grid max-w-4xl gap-5 sm:grid-cols-2">
        @csrf @if($item->exists) @method('PUT') @endif
        <x-field label="Título *" name="title" :value="$item->title" required />
        <x-field label="Slug (URL)" name="slug" :value="$item->slug" help="Se genera desde el título si lo dejas vacío." />
        <x-field label="Sector *" name="sector" :value="$item->sector" required />
        <x-field label="Orden" name="position" type="number" min="0" :value="$item->position" />
        <x-field label="Resumen *" name="summary" :value="$item->summary" required class="sm:col-span-2" />
        <x-field label="El reto" name="challenge" type="textarea" :value="$item->challenge" class="sm:col-span-2" />
        <x-field label="La solución" name="solution" type="textarea" :value="$item->solution" class="sm:col-span-2" />
        <x-field label="Resultados" name="results" type="textarea" :value="$item->results" class="sm:col-span-2" />
        <x-field label="Tecnologías (separadas por coma)" name="technologies" :value="implode(', ', $item->technologies ?? [])" class="sm:col-span-2" />
        <label class="flex items-center gap-2 text-sm sm:col-span-2"><input type="checkbox" name="is_published" value="1" class="accent-electric" @checked(old('is_published', $item->is_published))> Publicado en la web</label>
        <div class="sm:col-span-2"><button class="btn-primary">Guardar</button></div>
    </form>
</x-layouts.panel>

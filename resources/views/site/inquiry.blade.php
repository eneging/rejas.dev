<x-layouts.site title="Solicitar cotización">
    <section class="site-container grid gap-14 pt-36 pb-24 lg:grid-cols-[.8fr_1.2fr]">
        <div>
            <p class="eyebrow">Solicitar cotización</p>
            <h1 class="mt-4 text-4xl font-bold sm:text-5xl">Cuéntanos tu proyecto</h1>
            <p class="mt-6 text-mist">Respondemos en un máximo de 2 días hábiles. Solo te pediremos datos adicionales (como DNI o RUC) si avanzamos a la contratación.</p>
            <p class="mt-6 text-sm text-mist">También puedes escribirnos a <a class="text-white underline" href="mailto:{{ config('rejas.contact_email') }}">{{ config('rejas.contact_email') }}</a>.</p>
        </div>

        <div>
            @if (session('status'))
                <div role="status" class="mb-8 rounded-xl border border-emerald-400/30 bg-emerald-400/10 px-5 py-4 text-emerald-200">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('inquiry.store') }}" class="space-y-5" novalidate>
                @csrf
                <div class="hidden" aria-hidden="true"><label>No completar <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="name" class="mb-2 block text-sm text-mist">Nombre *</label>
                        <input id="name" name="name" value="{{ old('name') }}" required autocomplete="name" class="site-input">
                        @error('name')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="company" class="mb-2 block text-sm text-mist">Empresa (opcional)</label>
                        <input id="company" name="company" value="{{ old('company') }}" autocomplete="organization" class="site-input">
                    </div>
                    <div>
                        <label for="email" class="mb-2 block text-sm text-mist">Correo *</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="site-input">
                        @error('email')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="phone" class="mb-2 block text-sm text-mist">Teléfono / WhatsApp</label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" class="site-input">
                    </div>
                </div>

                <div>
                    <label for="service_type" class="mb-2 block text-sm text-mist">Tipo de servicio *</label>
                    <select id="service_type" name="service_type" required class="site-input">
                        <option value="" class="bg-ink">Selecciona una opción</option>
                        @foreach ($serviceTypes as $key => $label)
                            <option value="{{ $key }}" class="bg-ink" @selected(old('service_type', $selected) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('service_type')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="description" class="mb-2 block text-sm text-mist">Describe lo que necesitas *</label>
                    <textarea id="description" name="description" rows="6" required class="site-input" placeholder="¿Qué problema quieres resolver? ¿Tienes una fecha objetivo?">{{ old('description') }}</textarea>
                    @error('description')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-3 rounded-xl border border-white/10 p-5 text-sm">
                    <label class="flex gap-3">
                        <input type="checkbox" name="privacy" value="1" class="mt-0.5 h-4 w-4 accent-electric" @checked(old('privacy'))>
                        <span class="text-mist">He leído y acepto la <a href="{{ route('privacy') }}" target="_blank" class="text-white underline">política de privacidad</a> para que Rejas.dev trate mis datos con el fin de atender esta solicitud. *</span>
                    </label>
                    @error('privacy')<p class="text-red-400">{{ $message }}</p>@enderror
                    <label class="flex gap-3">
                        <input type="checkbox" name="marketing" value="1" class="mt-0.5 h-4 w-4 accent-electric" @checked(old('marketing'))>
                        <span class="text-mist">(Opcional) Acepto recibir novedades y comunicaciones comerciales de Rejas.dev.</span>
                    </label>
                </div>

                <button class="btn-site w-full justify-center bg-electric py-4 text-base text-white hover:bg-electric-700 sm:w-auto sm:px-10">Enviar solicitud</button>
            </form>
        </div>
    </section>
</x-layouts.site>

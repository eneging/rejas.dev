@php
    use App\Support\Pricing;
    $trial = $workshop['trial_days'];
    $testimonial = $workshop['testimonial'];
    $tax = Pricing::taxNote();
@endphp
<x-layouts.site :title="$workshop['name']" description="Sistema para talleres mecánicos en Ica: órdenes de trabajo, inventario de repuestos, cotizaciones y avisos por WhatsApp. {{ $trial }} días gratis.">
    {{-- Hero --}}
    <section class="relative overflow-hidden pt-36 pb-20">
        <div class="grid-bg absolute inset-0 -z-10 [mask-image:radial-gradient(ellipse_at_top,black,transparent_70%)]"></div>
        <div class="site-container max-w-4xl text-center">
            <p class="eyebrow" data-reveal>{{ $workshop['name'] }}</p>
            <h1 class="mt-6 text-4xl leading-[1.05] font-bold sm:text-6xl" data-reveal="1">Tu taller ordenado, <span class="text-electric">desde el celular</span></h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-mist" data-reveal="2">Sabe cuánto ganas por cada carro, qué repuestos te quedan y avisa al cliente por WhatsApp cuando su carro está listo.</p>
            <div class="mt-10 flex flex-wrap justify-center gap-4" data-reveal="3">
                <a href="#prueba" class="btn-site bg-electric text-white hover:bg-electric-700">Probar {{ $trial }} días gratis</a>
                <a href="#planes" class="btn-site border border-white/20 text-white hover:border-electric">Ver planes</a>
            </div>
            <p class="mt-4 text-sm text-mist" data-reveal="3">Sin contrato · Pasamos tus datos por ti</p>
        </div>
    </section>

    {{-- Funciones --}}
    <section class="border-t border-white/5 py-20">
        <div class="site-container grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                ['Órdenes de trabajo', 'Registra cada carro que entra, el trabajo, los repuestos y el responsable.'],
                ['Clientes y vehículos', 'Historial completo por placa: qué se le hizo, cuándo y cuánto se cobró.'],
                ['Inventario de repuestos', 'Sabe qué te queda y qué reponer antes de quedarte sin stock.'],
                ['Cotizaciones en PDF', 'Envía presupuestos claros y profesionales en segundos.'],
                ['Avisos por WhatsApp', 'Recuérdale al cliente su mantenimiento y avísale cuando su carro esté listo.'],
                ['Reportes de ganancias', 'Cuánto ganaste por carro, por día y por mes.'],
            ] as $i => [$title, $text])
                <article class="rounded-2xl border border-white/10 bg-slate-ink/60 p-6" data-reveal="{{ $i % 3 }}">
                    <h2 class="text-lg font-semibold">{{ $title }}</h2>
                    <p class="mt-2 text-sm text-mist">{{ $text }}</p>
                </article>
            @endforeach
        </div>
    </section>

    {{-- Testimonio: solo con autorización del cliente --}}
    @if ($testimonial['enabled'])
        <section class="py-20">
            <figure class="site-container max-w-3xl text-center" data-reveal>
                @if ($testimonial['video_url'])
                    <div class="aspect-video overflow-hidden rounded-2xl border border-white/10"><iframe src="{{ $testimonial['video_url'] }}" title="Testimonio de {{ $testimonial['business'] }}" class="h-full w-full" loading="lazy" allowfullscreen></iframe></div>
                @endif
                @if ($testimonial['quote'])<blockquote class="mt-8 text-2xl font-semibold">“{{ $testimonial['quote'] }}”</blockquote>@endif
                <figcaption class="mt-4 text-mist">{{ $testimonial['business'] }}</figcaption>
            </figure>
        </section>
    @endif

    {{-- Planes --}}
    <section id="planes" class="scroll-mt-16 bg-slate-ink py-24">
        <div class="site-container">
            <h2 class="text-center text-3xl font-bold sm:text-5xl" data-reveal>Planes</h2>
            <p class="mx-auto mt-4 max-w-xl text-center text-mist" data-reveal="1">Pago mensual, o anual adelantado: 12 meses por el precio de {{ $workshop['annual_months_charged'] }}.</p>
            <div class="mt-14"><x-workshop-plans :plans="$plans" /></div>
            <p class="mt-8 text-center text-sm text-mist">
                Instalación y migración de tus datos: <span class="line-through">{{ Pricing::amount($workshop['setup_fee']) }}</span> <strong class="text-white">gratis al contratar</strong>.
                @if ($tax) Precios: {{ $tax }}. @endif
            </p>
        </div>
    </section>

    {{-- Preguntas frecuentes --}}
    <section class="py-24">
        <div class="site-container max-w-3xl">
            <h2 class="text-3xl font-bold" data-reveal>Preguntas frecuentes</h2>
            <div class="mt-10 divide-y divide-white/10 border-y border-white/10">
                @foreach ([
                    ['¿Cómo funciona la prueba gratis?', "Usas el sistema completo durante {$trial} días, sin contrato. Si te sirve, eliges un plan; si no, no pagas nada."],
                    ['¿Tengo que pasar mis datos?', 'No. Nosotros cargamos tus clientes, vehículos y repuestos. Al contratar, la instalación y migración es gratis.'],
                    ['¿Cómo se paga?', 'Mensual, o anual por adelantado (12 meses por el precio de '.$workshop['annual_months_charged'].'). La web no cobra: acordamos el pago contigo directamente.'],
                    ['¿Prefieres un pago único?', 'Escríbenos y vemos la opción que mejor se adapte a tu taller.'],
                ] as [$q, $a])
                    <details class="group py-5">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-medium">{{ $q }}<span class="text-electric transition group-open:rotate-45">+</span></summary>
                        <p class="mt-3 text-mist">{{ $a }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Formulario de prueba --}}
    <section id="prueba" class="scroll-mt-20 border-t border-white/5 bg-slate-ink py-24">
        <div class="site-container grid max-w-5xl gap-12 lg:grid-cols-[.8fr_1.2fr]">
            <div>
                <h2 class="text-3xl font-bold sm:text-4xl">Empieza tu prueba gratis</h2>
                <p class="mt-4 text-mist">Déjanos tus datos y te contactamos para activar tu cuenta y pasar tu información.</p>
                @if (config('rejas.whatsapp'))
                    <a href="https://wa.me/{{ config('rejas.whatsapp') }}?text={{ rawurlencode('Hola, quiero probar gratis el sistema para talleres.') }}" target="_blank" rel="noopener" class="btn-site mt-8 border border-white/20 text-white hover:border-electric">Prefiero escribir por WhatsApp</a>
                @endif
            </div>
            <div>
                @if (session('status'))
                    <div role="status" class="mb-6 rounded-xl border border-emerald-400/30 bg-emerald-400/10 px-5 py-4 text-emerald-200">{{ session('status') }}</div>
                @endif
                <form method="POST" action="{{ route('inquiry.store') }}" class="space-y-5" novalidate>
                    @csrf
                    <input type="hidden" name="service_type" value="taller">
                    <div class="hidden" aria-hidden="true"><label>No completar <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="name" class="mb-2 block text-sm text-mist">Tu nombre *</label>
                            <input id="name" name="name" value="{{ old('name') }}" required autocomplete="name" class="site-input">
                            @error('name')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="company" class="mb-2 block text-sm text-mist">Nombre del taller</label>
                            <input id="company" name="company" value="{{ old('company') }}" class="site-input">
                        </div>
                        <div>
                            <label for="phone" class="mb-2 block text-sm text-mist">Celular / WhatsApp</label>
                            <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" class="site-input">
                        </div>
                        <div>
                            <label for="email" class="mb-2 block text-sm text-mist">Correo *</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="site-input">
                            @error('email')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label for="plan" class="mb-2 block text-sm text-mist">Plan que te interesa</label>
                        <select id="plan" name="plan" class="site-input">
                            <option value="" class="bg-ink">Aún no lo sé</option>
                            @foreach ($plans as $key => $plan)
                                <option value="{{ $key }}" class="bg-ink" @selected(old('plan', request('plan')) === $key)>{{ $plan['name'] }} · {{ Pricing::amount($plan['monthly']) }}/mes</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="description" class="mb-2 block text-sm text-mist">Cuéntanos de tu taller *</label>
                        <textarea id="description" name="description" rows="4" required class="site-input" placeholder="¿Dónde está? ¿Cuántos carros atienden al mes? ¿Cuántas personas trabajan?">{{ old('description') }}</textarea>
                        @error('description')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-3 rounded-xl border border-white/10 p-5 text-sm">
                        <label class="flex gap-3">
                            <input type="checkbox" name="privacy" value="1" class="mt-0.5 h-4 w-4 accent-electric" @checked(old('privacy'))>
                            <span class="text-mist">He leído y acepto la <a href="{{ route('privacy') }}" target="_blank" class="text-white underline">política de privacidad</a>. *</span>
                        </label>
                        @error('privacy')<p class="text-red-400">{{ $message }}</p>@enderror
                        <label class="flex gap-3">
                            <input type="checkbox" name="marketing" value="1" class="mt-0.5 h-4 w-4 accent-electric" @checked(old('marketing'))>
                            <span class="text-mist">(Opcional) Acepto recibir novedades de Rejas.dev.</span>
                        </label>
                    </div>
                    <button class="btn-site w-full justify-center bg-electric py-4 text-base text-white hover:bg-electric-700 sm:w-auto sm:px-10">Quiero mi prueba gratis</button>
                </form>
            </div>
        </div>
    </section>
</x-layouts.site>

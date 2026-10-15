<x-layouts.site>
    {{-- Hero --}}
    <section class="relative isolate flex min-h-[100svh] items-center overflow-hidden pt-16">
        <div class="grid-bg absolute inset-0 -z-10 [mask-image:radial-gradient(ellipse_at_center,black,transparent_75%)]"></div>
        <div class="site-container grid items-center gap-12 py-16 lg:grid-cols-[1.1fr_.9fr]">
            <div>
                <p class="eyebrow" data-reveal>Software a medida · Soluciones por rubro</p>
                <h1 class="mt-6 text-4xl leading-[1.05] font-bold sm:text-6xl lg:text-7xl" data-reveal="1">
                    Desarrollamos tecnología que <span class="text-electric">impulsa</span> tu negocio
                </h1>
                <p class="mt-6 max-w-xl text-lg text-mist" data-reveal="2">
                    Diseñamos y construimos sistemas web, plataformas de gestión y sitios que ordenan tu operación: talleres, colegios, inventarios, finanzas y lo que tu empresa necesite.
                </p>
                <div class="mt-10 flex flex-wrap gap-4" data-reveal="3">
                    <a href="#proyectos" class="btn-site bg-white text-ink hover:bg-mist">Explorar proyectos</a>
                    <a href="{{ route('inquiry.create') }}" class="btn-site border border-white/20 text-white hover:border-electric hover:bg-electric/10">Solicitar cotización →</a>
                </div>
            </div>

            {{-- Objeto conceptual: "R" metálica con acentos azules (CSS 3D, sin WebGL obligatorio) --}}
            <div class="r-stage relative mx-auto aspect-square w-full max-w-md" data-r-stage aria-hidden="true">
                <div class="r-glow absolute inset-10 rounded-full"></div>
                <div class="r-object relative h-full w-full" data-r-object style="transform: rotateY(-14deg) rotateX(6deg)">
                    @for ($i = 10; $i >= 1; $i--)
                        <div class="r-layer text-[18rem] sm:text-[22rem]" style="transform: translateZ({{ -$i * 3 }}px); color: rgb({{ 20 + $i * 2 }} {{ 30 + $i * 3 }} {{ 60 + $i * 6 }});">R</div>
                    @endfor
                    <div class="r-layer text-[18rem] sm:text-[22rem]" style="transform: translateZ(2px); background: linear-gradient(135deg,#fff 0%,#cbd5e1 35%,#64748b 55%,#e2e8f0 70%,#2563eb 100%); -webkit-background-clip: text; background-clip: text; color: transparent;">R</div>
                    <div class="absolute right-[18%] bottom-[16%] h-6 w-6 rounded-full bg-electric shadow-[0_0_40px_10px_rgb(37_99_235/.6)]" style="transform: translateZ(40px)"></div>
                </div>
            </div>
        </div>
        <a href="#servicios" class="absolute bottom-8 left-1/2 -translate-x-1/2 text-xs tracking-[0.3em] text-mist uppercase hover:text-white">Descubre ↓</a>
    </section>

    {{-- Servicios --}}
    <section id="servicios" class="scroll-mt-16 border-t border-white/5 py-24 sm:py-32">
        <div class="site-container">
            <p class="eyebrow" data-reveal>Servicios</p>
            <h2 class="mt-4 max-w-2xl text-3xl font-bold sm:text-5xl" data-reveal="1">Del problema al sistema funcionando</h2>
            <div class="mt-14 grid gap-px overflow-hidden rounded-2xl border border-white/10 bg-white/10 sm:grid-cols-2">
                @foreach ($services as $i => $service)
                    <article class="bg-ink p-8 transition hover:bg-slate-ink" data-reveal="{{ $i }}">
                        <span class="font-display text-sm text-electric">0{{ $i + 1 }}</span>
                        <h3 class="mt-4 text-xl font-semibold">{{ $service['title'] }}</h3>
                        <p class="mt-3 text-mist">{{ $service['text'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Soluciones por sector --}}
    <section id="soluciones" class="scroll-mt-16 bg-slate-ink py-24 sm:py-32">
        <div class="site-container grid gap-12 lg:grid-cols-[.8fr_1.2fr]">
            <div>
                <p class="eyebrow" data-reveal>Soluciones por sector</p>
                <h2 class="mt-4 text-3xl font-bold sm:text-5xl" data-reveal="1">Bases probadas, adaptadas a tu rubro</h2>
                <p class="mt-6 text-mist" data-reveal="2">Partimos de módulos reutilizables y los ajustamos a tus procesos: menos tiempo de desarrollo, más foco en lo que te diferencia.</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ($sectors as $i => $sector)
                    <article class="rounded-2xl border border-white/10 bg-ink/60 p-6" data-reveal="{{ $i }}">
                        <h3 class="text-lg font-semibold">{{ $sector['title'] }}</h3>
                        <p class="mt-2 text-sm text-mist">{{ $sector['text'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Proceso --}}
    <section class="py-24 sm:py-32">
        <div class="site-container">
            <p class="eyebrow" data-reveal>Cómo trabajamos</p>
            <ol class="mt-10 grid gap-8 md:grid-cols-4">
                @foreach ([['Conversamos', 'Entendemos tu operación y lo que quieres lograr.'], ['Cotizamos', 'Alcance, plazos y precio claros, por escrito.'], ['Construimos', 'Avances visibles y entregas por etapas.'], ['Acompañamos', 'Soporte, mejoras y evolución continua.']] as $i => [$step, $text])
                    <li class="border-l border-electric/60 pl-5" data-reveal="{{ $i }}">
                        <span class="font-display text-4xl font-bold text-white/15">{{ $i + 1 }}</span>
                        <h3 class="mt-2 font-semibold">{{ $step }}</h3>
                        <p class="mt-1 text-sm text-mist">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Portafolio --}}
    <section id="proyectos" class="scroll-mt-16 border-t border-white/5 py-24 sm:py-32">
        <div class="site-container">
            <p class="eyebrow" data-reveal>Trabajos realizados</p>
            <h2 class="mt-4 text-3xl font-bold sm:text-5xl" data-reveal="1">Proyectos</h2>
            @if ($portfolio->isEmpty())
                <p class="mt-10 text-mist" data-reveal>Muy pronto publicaremos nuestros primeros casos de estudio. ¿Quieres ser uno de ellos?</p>
            @else
                <div class="mt-14 grid gap-6 md:grid-cols-2">
                    @foreach ($portfolio as $i => $item)
                        <a href="{{ route('portfolio.show', $item) }}" class="group relative overflow-hidden rounded-2xl border border-white/10 bg-gradient-to-br from-slate-ink to-ink p-8 transition hover:border-electric/60" data-reveal="{{ $i % 2 }}">
                            <p class="text-xs tracking-[0.25em] text-electric-400 uppercase">{{ $item->sector }}</p>
                            <h3 class="mt-4 text-2xl font-semibold">{{ $item->title }}</h3>
                            <p class="mt-3 text-mist">{{ $item->summary }}</p>
                            @if ($item->technologies)
                                <p class="mt-6 flex flex-wrap gap-2">@foreach ($item->technologies as $tech)<span class="rounded-full border border-white/10 px-3 py-1 text-xs text-mist">{{ $tech }}</span>@endforeach</p>
                            @endif
                            <span class="mt-6 inline-block text-sm font-medium text-white group-hover:text-electric-400">Ver caso →</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Planes referenciales --}}
    <section id="planes" class="scroll-mt-16 bg-slate-ink py-24 sm:py-32">
        <div class="site-container">
            <p class="eyebrow" data-reveal>Planes referenciales</p>
            <h2 class="mt-4 text-3xl font-bold sm:text-5xl" data-reveal="1">Cada proyecto se cotiza a medida</h2>
            <div class="mt-14 grid gap-6 md:grid-cols-3">
                @foreach ($plans as $i => $plan)
                    <article class="flex flex-col rounded-2xl border border-white/10 bg-ink/60 p-8" data-reveal="{{ $i }}">
                        <h3 class="text-lg font-semibold">{{ $plan['name'] }}</h3>
                        <p class="mt-2 font-display text-2xl font-bold text-electric-400">{{ $plan['from'] }}</p>
                        <ul class="mt-6 flex-1 space-y-2 text-sm text-mist">
                            @foreach ($plan['items'] as $line)<li class="flex gap-2"><span class="text-electric">—</span>{{ $line }}</li>@endforeach
                        </ul>
                    </article>
                @endforeach
            </div>
            <p class="mt-6 text-xs text-mist/70">Precios referenciales en soles, no incluyen impuestos. La propuesta final depende del alcance. Los pagos se acuerdan directamente con cada cliente; esta web no procesa pagos.</p>
        </div>
    </section>

    {{-- CTA final --}}
    <section class="relative overflow-hidden py-28 sm:py-36">
        <div class="r-glow absolute inset-x-0 -bottom-40 mx-auto h-96 w-[40rem] rounded-full"></div>
        <div class="site-container relative text-center">
            <h2 class="mx-auto max-w-3xl text-4xl font-bold sm:text-6xl" data-reveal>¿Tienes un proyecto en mente?</h2>
            <p class="mx-auto mt-6 max-w-xl text-mist" data-reveal="1">Cuéntanos qué necesitas y te enviamos una propuesta clara con alcance, plazos y precio.</p>
            <a href="{{ route('inquiry.create') }}" class="btn-site mt-10 bg-electric px-8 py-4 text-base text-white hover:bg-electric-700" data-reveal="2">Solicitar cotización</a>
        </div>
    </section>
</x-layouts.site>

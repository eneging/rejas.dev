@props(['plans'])
@php
    use App\Support\Pricing;
    $tax = Pricing::taxNote();
    $whatsapp = config('rejas.whatsapp');
    $trialDays = config('rejas.workshop.trial_days');
@endphp
<div class="grid gap-6 md:grid-cols-3">
    @foreach ($plans as $key => $plan)
        @php
            $cta = $whatsapp
                ? 'https://wa.me/'.$whatsapp.'?text='.rawurlencode("Hola, quiero probar gratis el plan {$plan['name']} del sistema para talleres.")
                : route('workshop').'?plan='.$key.'#prueba';
        @endphp
        <article @class([
            'relative flex flex-col rounded-2xl border p-8',
            'border-electric bg-electric/10 shadow-[0_0_60px_-20px_rgb(37_99_235/.7)]' => $plan['highlight'] ?? false,
            'border-white/10 bg-ink/60' => ! ($plan['highlight'] ?? false),
        ]) data-reveal="{{ $loop->index }}">
            @if ($plan['highlight'] ?? false)
                <span class="absolute -top-3 left-8 rounded-full bg-electric px-3 py-1 text-xs font-semibold">Más elegido</span>
            @endif
            <h3 class="text-lg font-semibold">{{ $plan['name'] }}</h3>
            <p class="mt-4 flex items-baseline gap-1">
                <span class="font-display text-4xl font-bold">{{ Pricing::amount($plan['monthly']) }}</span>
                <span class="text-sm text-mist">/mes</span>
            </p>
            <p class="mt-1 text-sm text-mist">
                o {{ Pricing::amount($plan['annual']) }} al año <span class="text-electric-400">(ahorras {{ Pricing::amount(Pricing::annualSaving($plan['monthly'])) }})</span>
            </p>
            @if ($tax)<p class="mt-1 text-xs text-mist/70">{{ $tax }}</p>@endif
            <ul class="mt-6 flex-1 space-y-2 text-sm text-mist">
                @foreach ($plan['features'] as $feature)
                    <li class="flex gap-2"><span class="text-electric">✓</span>{{ $feature }}</li>
                @endforeach
                <li class="flex gap-2"><span class="text-electric">✓</span>{{ $plan['users'] }}</li>
            </ul>
            <a href="{{ $cta }}" @if($whatsapp) target="_blank" rel="noopener" @endif @class([
                'btn-site mt-8 justify-center',
                'bg-electric text-white hover:bg-electric-700' => $plan['highlight'] ?? false,
                'border border-white/20 text-white hover:border-electric' => ! ($plan['highlight'] ?? false),
            ])>Probar {{ $trialDays }} días gratis</a>
        </article>
    @endforeach
</div>

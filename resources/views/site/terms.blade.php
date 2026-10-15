<x-layouts.site title="Términos">
    <article class="site-container max-w-3xl pt-36 pb-24 leading-relaxed text-mist [&_h2]:mt-10 [&_h2]:mb-3 [&_h2]:text-xl [&_h2]:font-semibold [&_h2]:text-white">
        <h1 class="text-4xl font-bold text-white">Términos de uso</h1>
        <p class="mt-2 text-sm">Versión {{ config('rejas.legal.terms_version') }}</p>
        <div class="mt-6 rounded-lg border border-amber-400/30 bg-amber-400/10 p-4 text-sm text-amber-200">Borrador referencial pendiente de revisión legal.</div>

        <h2>Uso del sitio</h2>
        <p>Este sitio muestra información comercial de Rejas.dev y permite solicitar cotizaciones. No procesa pagos ni constituye una oferta vinculante.</p>
        <h2>Cotizaciones y contratos</h2>
        <p>Los precios publicados son referenciales. Cada servicio se rige por la cotización aceptada y el contrato firmado entre las partes, que detallan alcance, entregables, plazos y condiciones de pago.</p>
        <h2>Propiedad intelectual</h2>
        <p>Los contenidos, marcas y diseños de este sitio pertenecen a Rejas.dev o a sus respectivos titulares.</p>
        <h2>Contacto</h2>
        <p>{{ config('rejas.contact_email') }}</p>
    </article>
</x-layouts.site>

<x-layouts.site title="Política de privacidad">
    <article class="site-container max-w-3xl pt-36 pb-24 leading-relaxed text-mist [&_h2]:mt-10 [&_h2]:mb-3 [&_h2]:text-xl [&_h2]:font-semibold [&_h2]:text-white [&_li]:ml-5 [&_li]:list-disc">
        <h1 class="text-4xl font-bold text-white">Política de privacidad</h1>
        <p class="mt-2 text-sm">Versión {{ config('rejas.legal.privacy_version') }}</p>
        <div class="mt-6 rounded-lg border border-amber-400/30 bg-amber-400/10 p-4 text-sm text-amber-200">
            Borrador referencial. Debe revisarse con asesoría legal local y adecuarse a la Ley N.° 29733, Ley de Protección de Datos Personales, y su reglamento vigente antes de publicarse.
        </div>

        <h2>Responsable del tratamiento</h2>
        <p>Rejas.dev ({{ config('rejas.domain') }}). Contacto: <a class="text-white underline" href="mailto:{{ config('rejas.contact_email') }}">{{ config('rejas.contact_email') }}</a>. [Completar razón social, RUC y domicilio].</p>

        <h2>Datos que recopilamos</h2>
        <ul>
            <li>Formulario de cotización: nombre, correo, teléfono, empresa, tipo de servicio y descripción del proyecto.</li>
            <li>Evidencia de consentimiento: fecha, versión de esta política, dirección IP y navegador.</li>
            <li>Documento de identidad (DNI/RUC) únicamente si es necesario para la contratación o la emisión de comprobantes.</li>
        </ul>

        <h2>Finalidad</h2>
        <ul>
            <li>Atender tu solicitud, contactarte y preparar una cotización.</li>
            <li>Gestionar la relación contractual si decides contratar nuestros servicios.</li>
            <li>Solo si lo autorizas de forma separada y opcional: enviarte comunicaciones comerciales.</li>
        </ul>

        <h2>Destinatarios</h2>
        <p>No vendemos tus datos. Podrán acceder proveedores que nos prestan servicios de alojamiento o correo, bajo obligaciones de confidencialidad. [Detallar proveedores y si existe transferencia internacional].</p>

        <h2>Conservación</h2>
        <p>Conservamos los datos de prospectos que no se conviertan en clientes por un máximo de [12] meses; los de clientes, mientras dure la relación y los plazos legales aplicables.</p>

        <h2>Tus derechos</h2>
        <p>Puedes ejercer tus derechos de acceso, rectificación, cancelación y oposición escribiendo a {{ config('rejas.contact_email') }}. También puedes revocar el consentimiento comercial en cualquier momento.</p>

        <h2>Seguridad</h2>
        <p>Usamos conexiones cifradas (HTTPS), acceso restringido por roles, respaldos protegidos y registro de auditoría de acciones críticas.</p>
    </article>
</x-layouts.site>

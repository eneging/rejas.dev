<?php

return [
    'brand' => 'Rejas.dev',
    'domain' => 'eduarrejas.com',
    'contact_email' => env('REJAS_CONTACT_EMAIL', 'hola@eduarrejas.com'),
    'whatsapp' => env('REJAS_WHATSAPP'),
    'base_currency' => 'PEN',
    'timezone' => 'America/Lima',

    'legal' => [
        // Cambiar la versión cada vez que se actualice el texto legal: queda registrada en cada consentimiento.
        'privacy_version' => '2026-10-15',
        'terms_version' => '2026-10-15',
    ],

    'service_types' => [
        'web' => 'Página o sitio web',
        'sistema' => 'Sistema a medida',
        'inventario' => 'Sistema de inventario',
        'escolar' => 'Gestión escolar',
        'finanzas' => 'Finanzas y facturación',
        'taller' => 'Sistema para talleres',
        'soporte' => 'Soporte y mantenimiento',
        'otro' => 'Otro',
    ],

    'services' => [
        ['icon' => 'code', 'title' => 'Software a medida', 'text' => 'Sistemas web diseñados alrededor de tus procesos, no al revés.'],
        ['icon' => 'layers', 'title' => 'Soluciones por rubro', 'text' => 'Bases probadas para talleres, colegios, inventarios y finanzas que adaptamos a tu negocio.'],
        ['icon' => 'globe', 'title' => 'Sitios web y portafolios', 'text' => 'Presencia digital rápida, accesible y optimizada para buscadores.'],
        ['icon' => 'shield', 'title' => 'Soporte y evolución', 'text' => 'Mantenimiento mensual, mejoras continuas, copias de seguridad y monitoreo.'],
    ],

    'sectors' => [
        ['title' => 'Talleres', 'text' => 'Órdenes de trabajo, repuestos, historial por vehículo y avisos al cliente.'],
        ['title' => 'Colegios', 'text' => 'Matrículas, notas, asistencia, pensiones y comunicación con familias.'],
        ['title' => 'Inventarios', 'text' => 'Stock en tiempo real, kardex, almacenes, compras y alertas de reposición.'],
        ['title' => 'Finanzas', 'text' => 'Ingresos, gastos, cuentas por cobrar, flujo de caja y reportes.'],
    ],

    /*
    | Impuestos en precios publicados. Mientras no se defina el régimen tributario:
    | 'ninguno' → no se menciona el IGV; 'incluido' → "Incluye IGV"; 'adicional' → "+ IGV".
    */
    'tax_display' => env('REJAS_TAX_DISPLAY', 'ninguno'),

    /*
    | Producto: sistema para talleres (suscripción). La web NO procesa pagos.
    | El pago único (licencia + mantenimiento) no se publica: solo se ofrece en persona.
    */
    'workshop' => [
        'name' => 'Sistema para talleres',
        'trial_days' => 30,
        'setup_fee' => 200,          // instalación y migración de datos; gratis al contratar
        'annual_months_charged' => 10, // pago anual adelantado: 12 meses por el precio de 10
        'plans' => [
            'basico' => [
                'name' => 'Básico',
                'monthly' => 99,
                'users' => '2 usuarios',
                'features' => ['Órdenes de trabajo', 'Clientes y vehículos', 'Historial por vehículo'],
            ],
            'pro' => [
                'name' => 'Pro',
                'monthly' => 179,
                'users' => '5 usuarios',
                'highlight' => true,
                'features' => ['Todo lo del Básico', 'Inventario de repuestos', 'Cotizaciones en PDF', 'Recordatorios por WhatsApp', 'Reportes de ganancias'],
            ],
            'premium' => [
                'name' => 'Premium',
                'monthly' => 299,
                'users' => 'Usuarios ilimitados',
                'features' => ['Todo lo del Pro', 'Varias sedes', 'Bot de WhatsApp'],
            ],
        ],
        // Testimonio: activar solo con autorización escrita del cliente.
        'testimonial' => [
            'enabled' => env('REJAS_TESTIMONIAL_ENABLED', false),
            'business' => 'Calle Autocenter',
            'quote' => null,
            'video_url' => env('REJAS_TESTIMONIAL_VIDEO'),
        ],
    ],
];

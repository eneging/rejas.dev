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
        'taller' => 'Gestión de talleres',
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

    // Planes referenciales: no se cobran en la web; se acuerdan directamente con cada cliente.
    'plans' => [
        ['name' => 'Presencia', 'from' => 'Desde S/ 1,500', 'items' => ['Sitio web a medida', 'Formulario de contacto', 'SEO básico', 'Hosting el primer año']],
        ['name' => 'Sistema', 'from' => 'Desde S/ 6,000', 'items' => ['Módulos según tu proceso', 'Usuarios y permisos', 'Reportes y exportaciones', 'Capacitación']],
        ['name' => 'Soporte', 'from' => 'Desde S/ 250 / mes', 'items' => ['Mantenimiento y respaldos', 'Mejoras menores', 'Monitoreo', 'Atención prioritaria']],
    ],
];

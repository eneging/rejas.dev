# Rejas.dev — guía para agentes

- Especificación: `docs/documento-maestro.md`. Estado y pendientes por fase: `docs/estado-implementacion.md` (actualízalo al cerrar trabajo).
- Stack: Laravel 13, PHP 8.3, Blade, Tailwind CSS 4 (sin Alpine), SQLite en local / MySQL en producción.
- Idioma: interfaz, mensajes, rutas públicas y del panel en español; código (clases, métodos, columnas) en inglés.
- Autorización siempre por permiso (`App\Support\Permissions` + gates/policies), nunca comparando nombres de rol en controladores o vistas.
- Las reglas de negocio viven en modelos (`Project::progress()`, `trafficLight()`, `paymentStatus()`) y `app/Services`; los controladores solo validan y orquestan.
- Registra con `App\Support\Audit` todo cambio de fechas, estados, estimaciones, pagos y usuarios.
- Antes de entregar: `vendor/bin/pint`, `php artisan test` y `npm run build`.

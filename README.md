# Rejas.dev

Plataforma de **Rejas.dev** (dominio previsto: `eduarrejas.com`): portafolio público para captar clientes y sistema privado de gestión de la empresa (prospectos, clientes, cotizaciones, contratos, proyectos, tareas, calendario, cobros y suscripciones).

La especificación completa está en [`docs/documento-maestro.md`](docs/documento-maestro.md) (original en `docs/Rejas_dev_Documento_Maestro.docx`). El estado de implementación por fase está en [`docs/estado-implementacion.md`](docs/estado-implementacion.md).

## Stack

Monolito modular: **Laravel 13 + PHP 8.3**, MySQL/MariaDB (SQLite en desarrollo), Blade, **Tailwind CSS 4** y JavaScript sin framework. Tipografías Space Grotesk (títulos) e Inter (interfaz).

## Puesta en marcha

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite          # o configura DB_* para MySQL/MariaDB
php artisan migrate --seed              # roles, permisos, plantillas, admin y datos demo (solo en local)
npm run build                           # o: npm run dev
php artisan serve
```

- Sitio público: `http://localhost:8000`
- Panel: `http://localhost:8000/panel` (acceso en `/login`)

El seeder crea el administrador con `ADMIN_EMAIL` / `ADMIN_PASSWORD` del `.env`; si `ADMIN_PASSWORD` está vacío genera una contraseña aleatoria y la muestra en consola. En entorno `local` también crea datos demo (`SEED_DEMO=true`) con los usuarios `secretaria@eduarrejas.com` y `dev@eduarrejas.com` (contraseña `password`).

Tareas programadas: en producción configura el cron de Laravel (`* * * * * php artisan schedule:run`). Genera a diario los cargos "por cobrar" de las suscripciones (`php artisan rejas:generar-cargos`).

## Pruebas

```bash
php artisan test
vendor/bin/pint     # formato
```

Las pruebas cubren los criterios mínimos de aceptación del documento maestro (§15), la regla de avance, el semáforo y un recorrido de todas las pantallas del panel con cada rol.

## Estructura

| Ruta | Contenido |
| --- | --- |
| `app/Models` | Entidades del dominio (§12 del documento maestro) |
| `app/Support/Permissions.php` | Catálogo de permisos granulares y matriz por rol |
| `app/Services` | Reglas de negocio: `ProjectBuilder` (plantillas, plan 50/50), `QuoteWorkflow` (versiones, aceptación con evidencia), `SubscriptionBilling` (cargos y cobros manuales) |
| `app/Policies` | Acceso a proyectos, tareas y fecha contractual |
| `app/Http/Controllers/Site` | Sitio público y formulario de cotización |
| `app/Http/Controllers/Panel` | Panel privado |
| `resources/views/site`, `resources/views/panel` | Vistas Blade |
| `config/rejas.php` | Marca, servicios, sectores, planes referenciales y versiones de textos legales |

## Reglas de negocio clave

- **Avance (%)** = 100 × Σ pesos de tareas completadas / Σ pesos de tareas computables. El peso son las horas estimadas cuando todas las tareas las tienen; si falta alguna se usa peso 1 para todas. Las canceladas no cuentan. Sin tareas → "Sin planificación" (nunca 0 % engañoso).
- **Semáforo** (sobre la entrega interna, o la contractual si no hay interna): verde en plazo, amarillo con ≤ 5 días, rojo si venció sin finalizar; entregado/finalizado y cancelado tienen estado propio. No se mezcla con los cobros.
- **Margen de planificación**: si no se indica la entrega interna se calcula reservando el % configurado (25 % por defecto) del plazo contractual.
- **Permisos**: la autorización se evalúa por permiso, no por nombre de rol. Secretaría no puede cambiar la fecha contractual ni ver costos, gastos o márgenes. Los trabajadores solo ven sus proyectos/tareas.
- **Cotizaciones**: cada envío guarda una versión inmutable; la aceptación exige evidencia del canal (abrir el PDF no cuenta) y genera contrato en borrador y proyecto.
- **Suscripciones**: sin pasarela de pago. El vencimiento genera un cargo *por cobrar*; solo un pago registrado lo marca como *cobrado*.
- **Auditoría** de acciones críticas (fechas, estados, estimaciones, pagos, usuarios).

> Los textos legales (privacidad, términos) son borradores y deben revisarse con asesoría legal local conforme a la Ley N.° 29733 y su reglamento antes de publicar.

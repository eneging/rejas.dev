# Estado de implementación

Referencia: roadmap del [documento maestro](documento-maestro.md) (§14). Actualizado: 15/10/2026.

## Fase 1 · Base MVP — implementada

| Requisito | Estado | Dónde |
| --- | --- | --- |
| Login, cuentas activas/inactivas | ✅ | `Auth\LoginController`, middleware `active` |
| Roles y permisos granulares (Administrador, Secretaría, Trabajador) | ✅ | `Support\Permissions`, `RolesAndPermissionsSeeder`, pantalla Usuarios |
| Prospectos desde la web con evidencia de consentimiento | ✅ | `Site\InquiryController`, `lead_consents` |
| Clientes y contactos | ✅ | `Panel\ClientController` |
| Cotizaciones con partidas, impuestos, versiones, envío, aceptación con evidencia | ✅ | `Panel\QuoteController`, `Services\QuoteWorkflow` |
| PDF de cotización | 🟡 Vista imprimible ("Guardar como PDF" del navegador) | `panel/quotes/print.blade.php` |
| Proyectos: tabla con responsables, fechas, avance, estado, semáforo y cobro | ✅ | `Panel\ProjectController@index` |
| Plantillas por tipo de servicio (copia de tareas) | ✅ | `ProjectBuilder`, `CatalogSeeder` |
| Tareas: responsable, fecha, estimación, prioridad, comentarios, adjuntos, historial | ✅ | `Panel\TaskController`, auditoría |
| Avance automático y margen de planificación | ✅ | `Project::progress()`, `suggestInternalDueDate()` |
| Calendario mes/semana/día con filtros, sincronizado con la tabla | ✅ | `Panel\CalendarController` |
| Registro de pagos por hitos (50/50 por defecto), pagos parciales, comprobantes | ✅ | `Panel\PaymentController` |
| Sitio público: portada, servicios, sectores, portafolio, planes, cotización, legales | ✅ | `Site\*`, `resources/views/site` |

## Adelantado de fases posteriores

- Suscripciones manuales con cargos por cobrar y cobro (fase 2) — ✅
- Gastos, flujo de caja, cuentas por cobrar y rentabilidad restringida (fase 2) — ✅ básico
- Auditoría de acciones críticas (fase 2) — ✅
- Exportación de tabla y calendario filtrados (fase 2) — 🟡 CSV compatible con Excel (UTF-8, `;`, filtros y zona horaria en cabecera)
- Experiencia interactiva: "R" 3D en CSS que sigue al cursor, scroll narrativo, `prefers-reduced-motion`, versión sin JS (fase 3) — ✅ versión ligera

## Pendiente

- [ ] Exportación nativa `.xlsx` y PDF (p. ej. `maatwebsite/excel` y `barryvdh/laravel-dompdf`) y PDF de cotizaciones/contratos generados en cola.
- [ ] Contratos: plantillas editables, generación PDF y carga de archivos firmados (tablas `contracts`, `contract_files` ya existen).
- [ ] Recuperación de contraseña, verificación de correo y MFA opcional para administradores.
- [ ] Notificaciones internas y por correo (entregas próximas, tareas reasignadas, cambios de fecha) mediante colas.
- [ ] Descarga de adjuntos y comprobantes con enlaces temporales firmados.
- [ ] Edición/anulación de pagos con política definida (decisión pendiente §16).
- [ ] Multimoneda con tipo de cambio en reportes (el campo `exchange_rate` ya existe).
- [ ] Objeto 3D en Three.js/Spline y GSAP ScrollTrigger (integración progresiva sobre la versión CSS actual).
- [ ] SEO: sitemap, datos estructurados, imágenes de portada del portafolio.
- [ ] Textos legales definitivos revisados por asesoría legal.
- [ ] Despliegue: hosting, HTTPS, copias de seguridad probadas, monitoreo y entornos separados.

## Decisiones tomadas (revisables)

- El semáforo usa la **entrega interna** (meta de gestión) y, si no existe, la contractual. "Entregado" cuenta como finalizado para el semáforo.
- Si alguna tarea no tiene estimación, el avance usa peso 1 para todas (no se mezclan horas con unidades).
- Un usuario puede tener varios roles; los permisos se suman.

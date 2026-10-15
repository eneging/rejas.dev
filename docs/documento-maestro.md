# Rejas.dev — Documento maestro de producto, arquitectura funcional, identidad visual y plan de implementación

Dominio previsto: eduarrejas.com \| Versión 1.0 \| 15 de octubre de 2026

Estado: propuesta de diseño basada en los requisitos conversados; no es una implementación ni una especificación contractual definitiva.

# 1. Visión y objetivos

Rejas.dev será la marca y plataforma de servicios de desarrollo de software a medida y soluciones reutilizables por rubro (talleres, colegios, inventarios, finanzas y otros). El dominio eduarrejas.com alojará un portafolio público y un sistema privado de gestión de la empresa.

- Centralizar prospectos, clientes, cotizaciones, contratos, proyectos, tareas y cobros.

- Gestionar proyectos por fechas y porcentajes calculados automáticamente, sin tablero Kanban.

- Registrar pagos por hitos (habitualmente 50 % inicial y 50 % final) y suscripciones mensuales cobradas fuera de la web.

- Facilitar el crecimiento con usuarios, roles, permisos y auditoría.

- Mostrar trabajos realizados y captar solicitudes de cotización desde la web pública.

# 2. Alcance y experiencias

| Sitio público                                                                                                    | Panel privado                                                                                                                    |
|------------------------------------------------------------------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------|
| Portada, servicios, soluciones por sector, portafolio, planes referenciales, contacto y solicitud de cotización. | Dashboard, clientes, oportunidades, documentos, proyectos, calendario, tareas, finanzas, suscripciones, reportes, configuración. |

La página pública no procesará pagos de suscripción: solo captará contactos y mostrará información comercial. Los cobros se acuerdan directamente con cada cliente y se registran internamente.

# 3. Roles y permisos

| Rol           | Acceso previsto                                                                                                                                                                   |
|---------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Administrador | Acceso completo; configuración, usuarios, reportes financieros, márgenes y auditoría.                                                                                             |
| Secretaría    | Gestiona clientes, cotizaciones (crea y envía), contratos, proyectos, asignaciones, fechas internas y registro de pagos; no ve márgenes ni reportes de rentabilidad restringidos. |
| Trabajador    | Ve proyectos y tareas asignadas, actualiza estados, agrega comentarios y adjuntos; sin acceso financiero salvo permiso explícito.                                                 |

Implementar permisos granulares por acción y recurso, no solo por nombre de rol. Registrar autor, fecha y detalle de cambios importantes. La fecha contractual de entrega no puede cambiarla la secretaría; sí las fechas internas.

# 4. Flujo comercial

Solicitud web → prospecto → contacto y evaluación → cotización → envío → aceptación → contrato → proyecto → adelanto → ejecución → entrega → pago final → posible suscripción de soporte/servicio.

- La secretaría puede crear, editar y enviar cotizaciones sin aprobación previa.

- Versionar cotizaciones; registrar fecha de envío, vigencia, aceptación y cambios.

- Generar PDFs con numeración, partidas, alcance, precio, impuestos aplicables, plazos y condiciones.

- Contratos basados en plantillas revisables, con entregables, exclusiones, cronograma, cambios de alcance y condiciones de pago.

- No asumir que el cliente aceptó por abrir un PDF: registrar evidencia de aceptación por el canal utilizado.

# 5. Proyectos, tareas y avance

- Vista principal: tabla con proyecto, cliente, responsables, entrega interna, entrega contractual, avance, estado operativo y estado de cobro.

- Estados sugeridos: por iniciar, en ejecución, pausado, entregado, finalizado y cancelado.

- Semáforo: verde en plazo; amarillo cuando faltan 5 días o menos; rojo cuando venció y no finalizó; finalizado con estado independiente.

- Plantillas por tipo de servicio: página web, sistema de inventario, gestión escolar, finanzas, etc. Copiar tareas y estimaciones al crear un proyecto; cambios futuros a la plantilla no alteran proyectos existentes.

- Varios trabajadores por proyecto; cada tarea puede tener responsable, fecha, estimación, prioridad, comentarios, adjuntos e historial.

- Completar una tarea actualiza el avance inmediatamente, sin aprobación; reabrirla revierte el cálculo.

## Regla de cálculo

Avance (%) = 100 × suma de pesos de tareas completadas / suma de pesos de todas las tareas computables. Peso inicial sugerido: horas estimadas; si no existen, peso 1 por tarea. No usar tareas canceladas en el denominador. Si no hay tareas computables, mostrar “sin planificación”, no 0 % engañoso. Registrar cambios de estimación y evitar que tareas nuevas alteren históricos sin trazabilidad.

## Margen de planificación

Propuesta inicial: reservar internamente 20–30 % del plazo contractual, configurable por proyecto. Ejemplo: compromiso de 20 días hábiles y meta interna de 15 días (25 % de margen). El margen es una meta de gestión, no una garantía de cumplimiento. Alertar cuando el avance real sea bajo respecto al tiempo restante.

# 6. Calendario y exportaciones

- Vistas mensual, semanal y diaria; hitos, entregas, reuniones, tareas y recordatorios personales.

- Filtros por responsable, cliente, proyecto, rango de fechas y tipo de evento.

- Calendario y tabla sincronizados con las mismas fuentes de datos.

- Exportar calendario y tabla a Excel (.xlsx) y PDF con filtros, fechas y zona horaria explícitos.

- Recordatorios de entregas, tareas reasignadas y cambios de fecha; notificaciones internas primero y correo opcional posteriormente.

- Las tareas personales privadas no se exponen a otros usuarios sin autorización.

# 7. Finanzas y suscripciones

## Cobros por proyecto

Cada proyecto puede tener un plan de pagos con hitos editables: por defecto 50 % de adelanto y 50 % al finalizar. Registrar fecha, importe, moneda, método, referencia, responsable, comprobante y saldo pendiente. Admitir pagos parciales y ajustes autorizados.

## Suscripciones

Registro administrativo de planes, servicio asociado, cliente, monto, periodicidad mensual, inicio, siguiente vencimiento, estado (activa, suspendida, cancelada), pagos y renovaciones. No habrá pasarela de pago en la primera versión. Registrar el cobro manualmente y diferenciar “por cobrar” de “cobrado”.

## Control financiero

- Ingresos, gastos, cuentas por cobrar, vencimientos y flujo de caja por periodos.

- Separar importe cotizado, importe facturado (si corresponde), dinero recibido y utilidad estimada.

- La secretaría registra cobros, pero el acceso a márgenes y gastos internos puede restringirse.

- No registrar una suscripción como ingreso recibido solo porque venció la mensualidad.

- Moneda base PEN; contemplar otras monedas y tipo de cambio si el negocio crece.

# 8. Privacidad, formularios y documentos legales

En el formulario público solicitar nombre, correo, teléfono, tipo de servicio y descripción; DNI solo cuando sea realmente necesario para contratación o documentación. Publicar política de privacidad y términos aplicables, con casillas y evidencia de aceptación/consentimiento cuando corresponda. Evitar casillas premarcadas y separar el consentimiento comercial opcional.

- Informar finalidad, responsable del tratamiento, destinatarios, conservación y mecanismos para ejercer derechos sobre datos.

- Restringir acceso a datos personales, cifrar conexiones, proteger respaldos y definir retención/eliminación.

- Revisar obligaciones bajo la normativa peruana vigente de protección de datos personales y sus disposiciones reglamentarias antes de publicar.

- Los contratos y textos legales deben revisarse con asesoría legal local; este documento no sustituye esa revisión.

# 9. Diseño visual e identidad

| Elemento             | Definición                                                |
|----------------------|-----------------------------------------------------------|
| Marca                | Rejas.dev                                                 |
| Dominio              | eduarrejas.com                                            |
| Negro profundo       | \#080B12                                                  |
| Azul eléctrico       | \#2563EB                                                  |
| Azul grisáceo oscuro | \#111827                                                  |
| Blanco               | \#FFFFFF                                                  |
| Gris secundario      | \#94A3B8                                                  |
| Tipografías          | Space Grotesk para títulos; Inter para interfaz y lectura |

Portafolio: experiencia oscura, editorial, tecnológica y premium. Panel: menú lateral oscuro y área de trabajo clara, con opción de tema oscuro. Bordes finos, espacios generosos, jerarquía clara y tablas legibles.

# 10. Experiencia interactiva de la web pública

- Hero: logotipo REJAS.DEV, mensaje “Desarrollamos tecnología que impulsa tu negocio”, CTA “Explorar proyectos” y “Solicitar cotización”.

- Objeto 3D conceptual: letra R metálica/abstracta con acentos azules que rota sutilmente según el cursor; alternativa ligera en CSS/WebGL.

- Scroll narrativo: secciones que entran con transiciones suaves y presentan servicios, casos de uso, trabajos y CTA final.

- Tecnologías candidatas: Spline o Three.js para 3D, GSAP ScrollTrigger para scroll, con integración progresiva.

- Respetar prefers-reduced-motion, controles táctiles, contraste, carga diferida, versión estática y rendimiento en móviles.

- Evitar que la animación obstaculice formularios, lectura o navegación.

# 11. Arquitectura técnica propuesta

Monolito modular inicial: Laravel + PHP, MySQL/MariaDB, Blade, Tailwind CSS y Alpine.js; colas para correos, PDFs y notificaciones. Para la portada interactiva integrar componentes 3D aislados sin obligar a que todo el panel use WebGL.

- Autenticación segura, recuperación de contraseña, verificación de correo y MFA opcional para administrador.

- Autorización por políticas y permisos; auditoría de acciones críticas.

- Almacenamiento privado de contratos, comprobantes y adjuntos; enlaces temporales para descarga.

- Servicios separados por dominio: CRM, Cotizaciones, Contratos, Proyectos, Agenda, Facturación/Cobros, Suscripciones, Portafolio y Administración.

- Procesos en cola para PDF, Excel, avisos y correos; copias de seguridad y restauración probada.

- Entornos separados: desarrollo, pruebas y producción; HTTPS, logs, monitoreo y pruebas automatizadas.

# 12. Entidades de datos principales

| Área          | Entidades sugeridas                                                                                             |
|---------------|-----------------------------------------------------------------------------------------------------------------|
| Identidad     | users, roles, permissions, user_roles, audit_logs                                                               |
| CRM           | leads, clients, client_contacts, lead_consents                                                                  |
| Comercial     | quotes, quote_versions, quote_items, contracts, contract_files                                                  |
| Proyectos     | project_templates, template_tasks, projects, project_members, tasks, task_assignees, task_comments, attachments |
| Agenda        | calendar_events, reminders                                                                                      |
| Finanzas      | payment_schedules, payments, expenses, financial_categories                                                     |
| Recurrentes   | subscription_plans, subscriptions, subscription_charges                                                         |
| Sitio público | portfolio_projects, services, inquiries, site_settings                                                          |

Relaciones clave: cliente → múltiples cotizaciones/proyectos/suscripciones; cotización aceptada → contrato y proyecto; proyecto → tareas, integrantes, cronograma y plan de cobros; suscripción → cargos periódicos y pagos. Usar IDs internos, timestamps y soft delete cuando sea apropiado.

# 13. Pantallas propuestas

- Público: Inicio, Servicios, Soluciones por sector, Portafolio, Caso de estudio, Solicitar cotización, Contacto, Privacidad y Términos.

- Privado: Resumen, Prospectos, Clientes, Cotizaciones, Contratos, Proyectos (tabla), Detalle de proyecto, Calendario, Tareas, Finanzas, Suscripciones, Reportes, Portafolio y Usuarios/Configuración.

- En detalle de proyecto: cabecera, semáforo, barra de avance, tareas por responsable, fechas, documentos, pagos y actividad.

# 14. Roadmap sugerido

| Fase                  | Entregables                                                                                                 |
|-----------------------|-------------------------------------------------------------------------------------------------------------|
| 0 · Diseño            | Wireframes, flujos, reglas de negocio, esquema ER, prototipo visual, permisos y criterios de aceptación.    |
| 1 · Base MVP          | Login, roles, clientes, cotizaciones, proyectos, tareas, plantillas, tabla, calendario y registro de pagos. |
| 2 · Operación         | Contratos PDF, gastos, suscripciones manuales, exportación Excel/PDF, alertas y auditoría.                  |
| 3 · Presencia pública | Portafolio, formularios, política de privacidad, SEO y experiencia 3D optimizada.                           |
| 4 · Escalamiento      | Reportes avanzados, integraciones, automatizaciones, mejoras de rendimiento y permisos más finos.           |

# 15. Criterios mínimos de aceptación

- Una solicitud web crea un prospecto con datos y evidencia de aceptación de privacidad.

- La secretaría puede enviar cotizaciones y registrar pagos, pero no acceder a márgenes restringidos.

- Un proyecto puede tener varios trabajadores y tareas individuales.

- Completar/reabrir tareas recalcula el avance sin aprobación.

- El semáforo responde a las fechas y el estado finalizado, sin mezclar pagos.

- La secretaría puede modificar fechas internas, no la fecha contractual.

- Se pueden exportar tabla y calendario filtrados a PDF y Excel.

- Una suscripción vencida figura pendiente hasta registrar el cobro real.

- El portafolio funciona también en móvil y sin animaciones avanzadas.

# 16. Decisiones pendientes

- Definir nombre comercial exacto en cabecera: Rejas.dev y forma de mostrar eduarrejas.com.

- Precisar cómo se aprueban/firmarán contratos y si se emitirán comprobantes desde el sistema o fuera de él.

- Definir políticas de edición/anulación de pagos y control de caja.

- Acordar si el avance ponderado por horas estimadas es la regla definitiva.

- Definir canales de notificación (correo, WhatsApp u otros), hosting y presupuesto.

- Preparar textos legales, identidad gráfica final y primeros casos del portafolio.

FIN DEL DOCUMENTO · Documento vivo: actualizar al validar prototipos y reglas con usuarios reales.

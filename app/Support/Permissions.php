<?php

namespace App\Support;

use App\Models\Role;

/**
 * Catálogo de permisos granulares por acción y recurso.
 * Los roles solo agrupan permisos; las autorizaciones siempre se evalúan por permiso.
 */
final class Permissions
{
    public const LEADS_MANAGE = 'leads.manage';

    public const CLIENTS_MANAGE = 'clients.manage';

    public const QUOTES_MANAGE = 'quotes.manage';

    public const QUOTES_SEND = 'quotes.send';

    public const PROJECTS_VIEW_ALL = 'projects.view_all';

    public const PROJECTS_MANAGE = 'projects.manage';

    public const PROJECTS_EDIT_CONTRACT_DATE = 'projects.edit_contract_date';

    public const TASKS_MANAGE = 'tasks.manage';

    public const PAYMENTS_RECORD = 'payments.record';

    public const FINANCE_VIEW = 'finance.view';

    public const FINANCE_VIEW_MARGINS = 'finance.view_margins';

    public const SUBSCRIPTIONS_MANAGE = 'subscriptions.manage';

    public const PORTFOLIO_MANAGE = 'portfolio.manage';

    public const USERS_MANAGE = 'users.manage';

    public const AUDIT_VIEW = 'audit.view';

    /** @return array<string, string> */
    public static function all(): array
    {
        return [
            self::LEADS_MANAGE => 'Gestionar prospectos y solicitudes web',
            self::CLIENTS_MANAGE => 'Gestionar clientes y contactos',
            self::QUOTES_MANAGE => 'Crear y editar cotizaciones',
            self::QUOTES_SEND => 'Enviar cotizaciones y registrar aceptación',
            self::PROJECTS_VIEW_ALL => 'Ver todos los proyectos',
            self::PROJECTS_MANAGE => 'Crear proyectos, asignar equipo y fechas internas',
            self::PROJECTS_EDIT_CONTRACT_DATE => 'Modificar la fecha contractual de entrega',
            self::TASKS_MANAGE => 'Crear, asignar y editar cualquier tarea',
            self::PAYMENTS_RECORD => 'Registrar cobros y planes de pago',
            self::FINANCE_VIEW => 'Ver cuentas por cobrar e ingresos',
            self::FINANCE_VIEW_MARGINS => 'Ver costos, gastos internos, márgenes y rentabilidad',
            self::SUBSCRIPTIONS_MANAGE => 'Gestionar suscripciones y sus cobros',
            self::PORTFOLIO_MANAGE => 'Gestionar el portafolio público',
            self::USERS_MANAGE => 'Gestionar usuarios, roles y configuración',
            self::AUDIT_VIEW => 'Ver la auditoría de acciones',
        ];
    }

    /** @return array<string, list<string>> */
    public static function defaultRoleMatrix(): array
    {
        $all = array_keys(self::all());

        return [
            Role::ADMIN => $all,
            Role::SECRETARY => [
                self::LEADS_MANAGE,
                self::CLIENTS_MANAGE,
                self::QUOTES_MANAGE,
                self::QUOTES_SEND,
                self::PROJECTS_VIEW_ALL,
                self::PROJECTS_MANAGE,
                self::TASKS_MANAGE,
                self::PAYMENTS_RECORD,
                self::FINANCE_VIEW,
                self::SUBSCRIPTIONS_MANAGE,
                self::PORTFOLIO_MANAGE,
            ],
            Role::WORKER => [],
        ];
    }
}

<?php

namespace Database\Seeders;

use App\Models\FinancialCategory;
use App\Models\ProjectTemplate;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            ['Página web', 'web', 20, [
                ['Reunión de levantamiento y briefing', 2], ['Arquitectura de contenidos y wireframes', 6], ['Diseño visual (UI)', 12],
                ['Maquetación responsive', 16], ['Formularios y SEO básico', 6], ['Pruebas en dispositivos', 4], ['Publicación y capacitación', 3],
            ]],
            ['Sistema de inventario', 'inventario', 45, [
                ['Levantamiento de procesos de almacén', 6], ['Modelo de datos y prototipo', 10], ['Usuarios, roles y permisos', 8],
                ['Productos, categorías y almacenes', 16], ['Entradas, salidas y kardex', 24], ['Alertas de stock y reportes', 14],
                ['Importación de datos iniciales', 6], ['Pruebas con usuarios', 8], ['Despliegue y capacitación', 6],
            ]],
            ['Gestión escolar', 'escolar', 60, [
                ['Levantamiento con dirección y secretaría', 8], ['Modelo académico (niveles, grados, secciones)', 10], ['Matrícula y legajo del estudiante', 20],
                ['Registro de notas y libretas', 24], ['Asistencia', 12], ['Pensiones y cobranza', 20], ['Portal de familias', 16],
                ['Pruebas piloto', 10], ['Capacitación y puesta en marcha', 8],
            ]],
            ['Sistema de finanzas', 'finanzas', 40, [
                ['Levantamiento contable y de flujo', 6], ['Plan de cuentas y categorías', 6], ['Ingresos y egresos', 16], ['Cuentas por cobrar y pagar', 18],
                ['Flujo de caja y reportes', 14], ['Exportaciones Excel/PDF', 8], ['Pruebas y capacitación', 8],
            ]],
        ];

        foreach ($templates as [$name, $type, $days, $tasks]) {
            $template = ProjectTemplate::updateOrCreate(['name' => $name], ['service_type' => $type, 'default_duration_days' => $days]);
            $template->tasks()->delete();
            foreach ($tasks as $i => [$title, $hours]) {
                $template->tasks()->create(['title' => $title, 'estimated_hours' => $hours, 'position' => $i]);
            }
        }

        foreach ([['Soporte básico', 250], ['Soporte y hosting', 400], ['Soporte prioritario', 800]] as [$name, $amount]) {
            SubscriptionPlan::updateOrCreate(['name' => $name], ['monthly_amount' => $amount, 'currency' => 'PEN']);
        }

        foreach (['Hosting y dominios', 'Licencias y software', 'Servicios de terceros', 'Publicidad', 'Equipos', 'Otros'] as $name) {
            FinancialCategory::firstOrCreate(['name' => $name, 'type' => 'gasto']);
        }
    }
}

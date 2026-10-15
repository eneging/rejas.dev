<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Exportación CSV compatible con Excel (UTF-8 con BOM, separador ";").
 * Incluye en la cabecera la fecha de generación, la zona horaria y los filtros aplicados.
 * La exportación nativa .xlsx y PDF queda para la fase 2 del roadmap.
 */
final class CsvExport
{
    /**
     * @param  list<string>  $headers
     * @param  iterable<array<int, mixed>>  $rows
     * @param  array<string, string>  $filters
     */
    public static function download(string $name, array $headers, iterable $rows, array $filters = []): StreamedResponse
    {
        $timezone = config('app.timezone');
        $filename = sprintf('%s-%s.csv', $name, now()->format('Ymd-His'));

        return response()->streamDownload(function () use ($headers, $rows, $filters, $timezone) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Generado', now()->format('d/m/Y H:i'), 'Zona horaria', $timezone], ';');
            foreach ($filters as $label => $value) {
                fputcsv($out, ['Filtro', $label, $value], ';');
            }
            fputcsv($out, [], ';');
            fputcsv($out, $headers, ';');
            foreach ($rows as $row) {
                fputcsv($out, array_values($row), ';');
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}

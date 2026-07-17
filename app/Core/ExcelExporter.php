<?php
declare(strict_types=1);

namespace App\Core;

final class ExcelExporter
{
    public static function table(string $title, array $headers, array $rows, array $meta = []): string
    {
        $colspan = max(1, count($headers));
        $html = '<html><head><meta charset="UTF-8"></head><body>';
        $html .= '<table border="1">';
        $html .= '<tr><th colspan="' . $colspan . '" style="background:#0f172a;color:#ffffff;font-size:16px;">' . e($title) . '</th></tr>';
        $html .= '<tr><td colspan="' . $colspan . '">Generado: ' . e(date('Y-m-d H:i:s')) . '</td></tr>';

        if (!empty($meta['user'])) {
            $html .= '<tr><td colspan="' . $colspan . '">Usuario: ' . e(self::safeCell($meta['user'])) . '</td></tr>';
        }

        if (!empty($meta['filters'])) {
            $html .= '<tr><td colspan="' . $colspan . '">Filtros: ' . e(self::safeCell($meta['filters'])) . '</td></tr>';
        }

        if (!empty($meta['totals']) && is_array($meta['totals'])) {
            foreach ($meta['totals'] as $label => $value) {
                $html .= '<tr><td colspan="' . $colspan . '"><strong>'
                    . e((string) $label) . ':</strong> ' . e(self::safeCell($value)) . '</td></tr>';
            }
        }

        $html .= '<tr>';

        foreach ($headers as $header) {
            $html .= '<th style="background:#0284c7;color:#ffffff;">' . e($header) . '</th>';
        }

        $html .= '</tr>';

        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . e(self::safeCell($cell)) . '</td>';
            }
            $html .= '</tr>';
        }

        if (!empty($meta['footer'])) {
            $html .= '<tr><td colspan="' . $colspan . '">' . e(self::safeCell($meta['footer'])) . '</td></tr>';
        }

        return $html . '</table></body></html>';
    }

    private static function safeCell(mixed $value): string
    {
        $cell = (string) $value;
        $trimmed = ltrim($cell);

        if ($trimmed !== '' && in_array($trimmed[0], ['=', '+', '-', '@'], true)) {
            return "'" . $cell;
        }

        return $cell;
    }
}

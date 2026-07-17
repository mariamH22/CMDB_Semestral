<?php
declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function redirect(string $path): never
    {
        header('Location: ' . url($path));
        exit;
    }

    public static function downloadExcel(string $filename, string $html): never
    {
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF";
        echo $html;
        exit;
    }
}

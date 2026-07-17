<?php
declare(strict_types=1);

namespace App\Core\Exceptions;

final class CsrfException extends HttpException
{
    public function __construct()
    {
        parent::__construct(419, 'La solicitud expiró o el token de seguridad no es válido.');
    }
}

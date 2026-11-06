<?php

declare(strict_types=1);

namespace App\Exceptions;

use LogicException;

/**
 * Una regla del negocio impidió la operación (ej.: stock insuficiente, transición
 * de estado no permitida). bootstrap/app.php la convierte en un mensaje para el usuario,
 * así los controladores no necesitan try/catch.
 */
class ReglaNegocioException extends LogicException
{
}

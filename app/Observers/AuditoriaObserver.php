<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Auditoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Patrón Observer (RNF006): registra en "auditorias" cada creación, edición y
 * eliminación de los modelos registrados en AppServiceProvider.
 * Nunca guarda contraseñas ni otros atributos ocultos ($hidden).
 */
class AuditoriaObserver
{
    /** Columnas cuyo cambio, por sí solo, no merece un registro. */
    private const IGNORADAS = ['updated_at', 'created_at', 'remember_token', 'subtotal', 'total'];

    /** Columnas que nunca se escriben en la descripción. */
    private const SENSIBLES = ['password', 'remember_token'];

    public function created(Model $modelo): void
    {
        Auditoria::registrar('crear', $modelo, $this->describir($modelo->getAttributes(), $modelo));
    }

    public function updated(Model $modelo): void
    {
        $cambios = array_diff_key($modelo->getChanges(), array_flip(self::IGNORADAS));

        if ($cambios === []) {
            return;
        }

        Auditoria::registrar('editar', $modelo, $this->describir($cambios, $modelo));
    }

    public function deleted(Model $modelo): void
    {
        $accion = in_array(SoftDeletes::class, class_uses_recursive($modelo), true) && ! $modelo->isForceDeleting()
            ? 'desactivar'
            : 'eliminar';

        Auditoria::registrar($accion, $modelo);
    }

    public function restored(Model $modelo): void
    {
        Auditoria::registrar('restaurar', $modelo);
    }

    /** @param array<string, mixed> $atributos */
    private function describir(array $atributos, Model $modelo): ?string
    {
        $ocultos = array_merge(self::SENSIBLES, $modelo->getHidden(), ['id', 'created_at', 'updated_at']);
        $partes  = [];

        foreach ($atributos as $campo => $valor) {
            if (in_array($campo, $ocultos, true)) {
                continue;
            }

            $valor    = $valor instanceof \BackedEnum ? $valor->value : $valor;
            $texto    = is_scalar($valor) || $valor === null ? (string) ($valor ?? '—') : json_encode($valor);
            $partes[] = $campo . ': ' . mb_strimwidth($texto, 0, 80, '…');
        }

        if (array_key_exists('password', $atributos) && $modelo->wasChanged('password')) {
            $partes[] = 'contraseña actualizada';
        }

        return $partes === [] ? null : implode(' | ', array_unique($partes));
    }
}

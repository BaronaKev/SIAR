<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Diagrama de clases → Auditoria. RNF006.
 */
class Auditoria extends Model
{
    use HasFactory;

    protected $table = 'auditorias';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'accion',
        'entidad',
        'entidad_id',
        'descripcion',
        'ip_address',
        'fecha',
    ];

    protected function casts(): array
    {
        return [
            'fecha'      => 'datetime',
            'entidad_id' => 'integer',
        ];
    }

    /** Usuario 1 —Genera— * Auditoria (incluye usuarios eliminados con SoftDeletes) */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    /**
     * Registra una acción. Ej.: Auditoria::registrar('crear', $pedido, 'Pedido creado en mesa 4').
     */
    public static function registrar(string $accion, ?Model $entidad = null, ?string $descripcion = null): self
    {
        return self::create([
            'user_id'     => auth()->id(),
            'accion'      => $accion,
            'entidad'     => $entidad ? class_basename($entidad) : 'Sistema',
            'entidad_id'  => $entidad?->getKey(),
            'descripcion' => $descripcion,
            'ip_address'  => request()?->ip(),
            'fecha'       => now(),
        ]);
    }

    /** Diagrama: consultar() → filtra por entidad y, opcionalmente, por id. */
    public function scopeDeEntidad($query, string $entidad, ?int $entidadId = null)
    {
        return $query->where('entidad', $entidad)
            ->when($entidadId !== null, fn ($q) => $q->where('entidad_id', $entidadId));
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    public const DISPONIBLE = 'Disponible';
    public const AGOTADO    = 'Agotado';

    protected $table = 'productos';

    protected $fillable = [
        'categoria_id',
        'nombre',
        'descripcion',
        'precio',
        'stock',
        'estado',
        'imagen',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'stock'  => 'integer',
    ];

    protected static function booted(): void
    {
        // RF011: un producto sin stock siempre queda Agotado, venga de donde venga el cambio.
        static::saving(function (self $producto): void {
            if ((int) $producto->stock <= 0) {
                $producto->estado = self::AGOTADO;
            }
        });
    }

    /**
     * Relación: Un producto pertenece a una categoría.
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    /** Producto 1 —Aparece en— * DetallePedido */
    public function detallesPedido(): HasMany
    {
        return $this->hasMany(DetallePedido::class, 'producto_id');
    }

    public function estaDisponible(): bool
    {
        return $this->estado === self::DISPONIBLE && $this->stock > 0;
    }

    /**
     * Scope para consultar solo productos con disponibilidad para pedidos.
     */
    public function scopeDisponibles(Builder $query): Builder
    {
        return $query->where('estado', self::DISPONIBLE)->where('stock', '>', 0);
    }

    /** Búsqueda por nombre o descripción. */
    public function scopeBuscar(Builder $query, ?string $texto): Builder
    {
        return $query->when($texto, fn (Builder $q) => $q->where(function (Builder $sub) use ($texto) {
            $sub->where('nombre', 'like', "%{$texto}%")
                ->orWhere('descripcion', 'like', "%{$texto}%");
        }));
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    use HasFactory;

    protected $table = 'categorias';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /**
     * Relación: Una categoría contiene muchos productos del menú.
     */
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'categoria_id');
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

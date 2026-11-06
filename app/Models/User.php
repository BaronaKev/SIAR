<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

/**
 * Diagrama de clases → Usuario.
 * ROL 1 —Tiene— * Usuario se resuelve con Spatie Permission (trait HasRoles).
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'estado',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $attributes = [
        'estado' => true,
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'estado'            => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Cuando cambia users.rol_id desde la aplicación, Spatie se actualiza en el mismo momento.
        static::saved(function (self $usuario): void {
            if ($usuario->wasChanged('rol_id') || ($usuario->wasRecentlyCreated && $usuario->rol_id !== null)) {
                $usuario->sincronizarRolesSpatie();
            }
        });
    }

    /** ROL 1 —Tiene— * Usuario (columna users.rol_id → roles.id) */
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'rol_id');
    }

    /**
     * Asigna el rol por nombre. users.rol_id es la fuente de verdad; el evento "saved"
     * copia el rol a Spatie (model_has_roles), de donde salen los permisos.
     */
    public function asignarRol(string $nombreRol): void
    {
        $this->forceFill(['rol_id' => Role::findByName($nombreRol, 'web')->id])->save();
    }

    /** Rol que tiene asignado en Spatie (tabla model_has_roles), o null. */
    public function rolIdSpatie(): ?int
    {
        $id = $this->roles()->value('roles.id');

        return $id === null ? null : (int) $id;
    }

    /**
     * Deja users.rol_id y model_has_roles iguales.
     *  - Si hay rol_id (por ejemplo, editado en phpMyAdmin), Spatie toma ese rol.
     *  - Si no hay rol_id pero Spatie tiene un rol, se copia a rol_id.
     *
     * @return bool true si hubo que corregir algo
     */
    public function sincronizarRol(): bool
    {
        $rolSpatie = $this->rolIdSpatie();
        $rolId     = $this->rol_id === null ? null : (int) $this->rol_id;

        if ($rolId === $rolSpatie && $this->roles()->count() <= 1) {
            return false;
        }

        if ($rolId !== null) {
            $this->sincronizarRolesSpatie();
        } else {
            $this->forceFill(['rol_id' => $rolSpatie])->saveQuietly();
        }

        return true;
    }

    private function sincronizarRolesSpatie(): void
    {
        $this->syncRoles($this->rol_id === null ? [] : [(int) $this->rol_id]);
        $this->unsetRelation('roles')->unsetRelation('rol');
    }

    /** Usuario 1 —Realiza— * Pedido */
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'user_id');
    }

    /** Usuario 1 —Genera— * Auditoria */
    public function auditorias(): HasMany
    {
        return $this->hasMany(Auditoria::class, 'user_id');
    }

    /** Usuario 1 —Gestiona— * Reserva */
    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'user_id');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado', true);
    }

    /** Búsqueda por nombre o correo. */
    public function scopeBuscar(Builder $query, ?string $texto): Builder
    {
        return $query->when($texto, fn (Builder $q) => $q->where(function (Builder $sub) use ($texto) {
            $sub->where('name', 'like', "%{$texto}%")
                ->orWhere('email', 'like', "%{$texto}%");
        }));
    }
}

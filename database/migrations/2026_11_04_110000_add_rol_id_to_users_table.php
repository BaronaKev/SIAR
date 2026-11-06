<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Diagrama de clases → Usuario.rol_id (ROL 1 —Tiene— * Usuario).
 * Llave foránea a la tabla "roles" de Spatie. Spatie sigue siendo quien decide los
 * permisos (tabla model_has_roles); rol_id es la referencia directa al rol principal
 * y se mantiene sincronizada con User::asignarRol().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('rol_id')
                ->nullable()
                ->after('password')
                ->constrained('roles')
                ->nullOnDelete();
        });

        // Completa rol_id de los usuarios que ya tenían un rol asignado con Spatie.
        $asignaciones = DB::table('model_has_roles')
            ->where('model_type', 'App\\Models\\User')
            ->orderBy('role_id')
            ->get(['model_id', 'role_id']);

        foreach ($asignaciones->unique('model_id') as $asignacion) {
            DB::table('users')
                ->where('id', $asignacion->model_id)
                ->update(['rol_id' => $asignacion->role_id]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rol_id');
        });
    }
};

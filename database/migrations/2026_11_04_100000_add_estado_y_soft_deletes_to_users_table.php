<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Diagrama de clases → Usuario.estado: Boolean.
 * SoftDeletes: al "eliminar" un usuario se conserva su historial de pedidos y auditoría (HU003).
 * El rol (Usuario.rol_id) lo gestiona Spatie Permission en la tabla model_has_roles.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('estado')->default(true)->after('password');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('estado');
        });
    }
};

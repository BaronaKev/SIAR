<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Diagrama de clases → Auditoria (Usuario 1 —Genera— * Auditoria). RNF006.
 * Registro inmutable: solo tiene "fecha", sin updated_at.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('accion', 50);
            $table->string('entidad', 50);
            $table->unsignedBigInteger('entidad_id')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('fecha')->useCurrent();

            $table->index(['entidad', 'entidad_id']);
            $table->index('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditorias');
    }
};

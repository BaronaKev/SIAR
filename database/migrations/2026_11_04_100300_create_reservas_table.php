<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Diagrama de clases → Reserva.
 *  Mesa 1 —Tiene— * Reserva
 *  Usuario 1 —Gestiona— * Reserva (usuario que registra la reserva)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mesa_id')->constrained('mesas')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nombre_cliente', 150);
            $table->date('fecha');
            $table->time('hora');
            $table->unsignedTinyInteger('personas')->default(2);
            $table->enum('estado', ['Activa', 'Cumplida', 'Cancelada'])->default('Activa');
            $table->timestamps();

            $table->index(['mesa_id', 'fecha', 'hora']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};

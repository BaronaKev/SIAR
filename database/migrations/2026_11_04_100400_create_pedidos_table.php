<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Diagrama de clases → Pedido.
 *  Usuario 1 —Realiza— * Pedido
 *  Mesa    1 —Ocupa—   * Pedido
 * RESTRICT en ambas llaves: no se puede borrar una mesa o un usuario con pedidos
 * (los usuarios usan SoftDeletes, así que su historial se conserva).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mesa_id')->constrained('mesas')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->dateTime('fecha')->useCurrent();
            $table->enum('estado', ['Pendiente', 'En preparación', 'Listo', 'Entregado', 'Facturado', 'Cancelado'])
                  ->default('Pendiente');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('impuesto', 12, 2)->default(0);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['estado', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};

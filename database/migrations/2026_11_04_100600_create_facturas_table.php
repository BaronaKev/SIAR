<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Diagrama de clases → Factura (Pedido 1 —Genera— 1 Factura).
 * pedido_id es UNIQUE para garantizar la relación 1 a 1.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->unique()->constrained('pedidos')->restrictOnDelete();
            $table->string('numero', 20)->nullable()->unique();
            $table->dateTime('fecha')->useCurrent();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('porcentaje_impuesto', 5, 2)->default(0);
            $table->decimal('impuesto', 12, 2)->default(0);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->enum('estado', ['Pendiente de pago', 'Pagada', 'Anulada'])->default('Pendiente de pago');
            $table->timestamps();

            $table->index(['estado', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};

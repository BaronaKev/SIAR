<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Diagrama de clases → Pago (Factura 1 —Registra— * Pago).
 * Varios pagos por factura permiten pagos mixtos (ej. parte efectivo, parte tarjeta).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas')->restrictOnDelete();
            $table->decimal('monto', 12, 2);
            $table->decimal('monto_recibido', 12, 2)->nullable();
            $table->enum('metodo', ['Efectivo', 'Tarjeta débito', 'Tarjeta crédito', 'Transferencia']);
            $table->string('referencia', 100)->nullable();
            $table->dateTime('fecha')->useCurrent();
            $table->timestamps();

            $table->index(['metodo', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};

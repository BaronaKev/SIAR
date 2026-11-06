<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Diagrama de clases → DetallePedido.
 *  Pedido   1 ◆—Contiene—   * DetallePedido  (composición: si se borra el pedido, se borran sus detalles)
 *  Producto 1 —Aparece en— * DetallePedido   (RESTRICT: el producto usa SoftDeletes)
 * precio_unitario guarda el precio del momento de la venta.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->restrictOnDelete();
            $table->unsignedInteger('cantidad');
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_pedidos');
    }
};

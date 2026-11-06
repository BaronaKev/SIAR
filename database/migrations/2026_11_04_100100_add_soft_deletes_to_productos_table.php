<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * RF009: eliminar un producto del menú conservando el historial de pedidos.
 *  - SoftDeletes: el producto desaparece del menú pero sigue existiendo para detalle_pedidos.
 *  - categoria_id pasa de CASCADE a SET NULL: borrar una categoría ya no borra en cascada
 *    productos que puedan estar referenciados por pedidos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign(['categoria_id']);
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->unsignedBigInteger('categoria_id')->nullable()->change();
            $table->foreign('categoria_id')->references('id')->on('categorias')->nullOnDelete();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropForeign(['categoria_id']);
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->unsignedBigInteger('categoria_id')->nullable(false)->change();
            $table->foreign('categoria_id')->references('id')->on('categorias')->cascadeOnDelete();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operacion_inventario_lineas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operacion_inventario_id')
                ->constrained('operaciones_inventario')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('producto_id')
                ->constrained('productos')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->unsignedInteger('cantidad');
            $table->decimal('costo_unitario', 12, 2)->default(0);
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->timestamps();

            $table->unique(
                ['operacion_inventario_id', 'producto_id'],
                'operacion_lineas_operacion_producto_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operacion_inventario_lineas');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 20)->unique()->comment('Código consecutivo generado por el sistema');
            $table->string('nombre', 120);
            $table->text('descripcion')->nullable();
            $table->foreignId('categoria_producto_id')
                ->constrained('categorias_productos')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('unidad_medida_id')
                ->constrained('unidad_medidas')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->string('imagen', 255)
                ->nullable()
                ->comment('Ruta relativa de la imagen en el disco público');
            $table->decimal('precio_compra', 12, 2)->default(0);
            $table->decimal('precio_venta', 12, 2)->default(0);
            $table->unsignedInteger('stock_minimo')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['nombre', 'categoria_producto_id'], 'productos_nombre_categoria_unique');
            $table->index('activo');
            $table->index('nombre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};

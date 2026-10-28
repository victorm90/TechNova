<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oferta_servicio_productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('oferta_servicio_id')
                ->constrained('oferta_servicios')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('producto_id')
                ->constrained('productos')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->unsignedInteger('cantidad')->default(1);
            $table->decimal('precio_venta', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(
                ['oferta_servicio_id', 'producto_id'],
                'oferta_servicio_productos_linea_producto_unique'
            );
            $table->index('producto_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oferta_servicio_productos');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')
                ->constrained('productos')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('almacen_id')
                ->constrained('almacenes')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->decimal('cantidad', 12, 2)->default(0);
            $table->decimal('costo_promedio', 12, 4)->default(0);
            $table->timestamp('ultimo_movimiento_at')->nullable();
            $table->timestamps();

            $table->unique(['producto_id', 'almacen_id'], 'inventarios_producto_almacen_unique');
            $table->index('almacen_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventarios');
    }
};

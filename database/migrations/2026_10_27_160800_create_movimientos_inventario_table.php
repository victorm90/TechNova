<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operacion_inventario_id')
                ->nullable()
                ->constrained('operaciones_inventario')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('producto_id')
                ->constrained('productos')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('almacen_id')
                ->constrained('almacenes')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->string('tipo', 10)->comment('entrada o salida');
            $table->decimal('cantidad', 12, 2);
            $table->decimal('saldo_resultante', 12, 2);
            $table->decimal('costo_unitario', 12, 4)->default(0);
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->timestamps();

            $table->index(['producto_id', 'almacen_id', 'created_at'], 'movimientos_producto_almacen_fecha_index');
            $table->index(['tipo', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};

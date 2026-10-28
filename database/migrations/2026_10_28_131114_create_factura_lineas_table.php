<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factura_lineas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')
                ->constrained('facturas')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('tipo', 20)->comment('servicio, gasto o producto');
            $table->string('descripcion', 200);
            $table->decimal('cantidad', 12, 2);
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('subtotal', 14, 2);
            $table->foreignId('servicio_id')
                ->nullable()
                ->constrained('servicios')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('gasto_id')
                ->nullable()
                ->constrained('gastos')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('producto_id')
                ->nullable()
                ->constrained('productos')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();

            $table->index(['factura_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_lineas');
    }
};

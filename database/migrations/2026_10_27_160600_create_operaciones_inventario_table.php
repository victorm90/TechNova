<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operaciones_inventario', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique()->comment('Código consecutivo de la operación');
            $table->string('tipo', 10)->comment('entrada o salida');
            $table->foreignId('almacen_id')
                ->constrained('almacenes')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('proveedor_id')
                ->nullable()
                ->constrained('proveedores')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->string('referencia', 60)->nullable();
            $table->text('notas')->nullable();
            $table->decimal('total', 14, 2)->default(0);
            $table->timestamps();

            $table->index(['tipo', 'created_at']);
            $table->index('proveedor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operaciones_inventario');
    }
};

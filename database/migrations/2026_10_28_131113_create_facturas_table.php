<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->foreignId('oferta_id')
                ->unique()
                ->constrained('ofertas')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('customer_id')
                ->constrained('clientes')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->date('fecha');
            $table->decimal('subtotal_servicios', 14, 2)->default(0);
            $table->decimal('subtotal_gastos', 14, 2)->default(0);
            $table->decimal('subtotal_productos', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->text('nota')->nullable();
            $table->string('forma_pago', 120)->nullable();
            $table->text('observaciones')->nullable();
            $table->string('firma_pie', 150)->nullable();
            $table->foreignId('almacen_id')
                ->nullable()
                ->constrained('almacenes')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->timestamps();

            $table->index('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};

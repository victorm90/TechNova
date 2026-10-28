<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oferta_servicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('oferta_id')
                ->constrained('ofertas')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('servicio_id')
                ->constrained('servicios')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->decimal('cantidad', 12, 2);
            $table->decimal('precio_unitario', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(
                ['oferta_id', 'servicio_id'],
                'oferta_servicios_oferta_servicio_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oferta_servicios');
    }
};

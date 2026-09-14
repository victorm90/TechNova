<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('municipios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 80)->comment('Nombre del municipio');
            $table->string('codigo', 10)->unique()->comment('Código único del municipio');
            $table->foreignId('provincia_id')
                ->constrained('provincias')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
                ->comment('Provincia a la que pertenece el municipio');
            $table->timestamps();

            $table->unique(['provincia_id', 'nombre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('municipios');
    }
};

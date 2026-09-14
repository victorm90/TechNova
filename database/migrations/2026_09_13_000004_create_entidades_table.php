<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entidades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 40)->unique()->comment('Nombre único de la entidad');
            $table->string('direccion', 100)->comment('Dirección de la entidad');
            $table->foreignId('provincia_id')
                ->constrained('provincias')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
                ->comment('Provincia a la que pertenece la entidad');
            $table->string('numero_cuenta', 15)
                ->nullable()
                ->unique()
                ->comment('Número de cuenta de 15 dígitos');
            $table->string('telefono', 12)->comment('Teléfono de 8 a 12 dígitos');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entidades');
    }
};

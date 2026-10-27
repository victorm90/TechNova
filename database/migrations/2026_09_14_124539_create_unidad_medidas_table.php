<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidad_medidas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique()->comment('Nombre de la unidad de medida');
            $table->string('abreviatura', 10)->nullable()->unique()->comment('Abreviatura de la unidad de medida');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidad_medidas');
    }
};

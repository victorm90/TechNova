<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El alta rápida de ofertas sólo exige el nombre del cliente. Los datos de
     * contacto se completan después desde el directorio de clientes.
     */
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('address', 100)->nullable()->change();
            $table->string('phone', 12)->nullable()->change();
            $table->string('identity_card', 11)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('address', 100)->nullable(false)->change();
            $table->string('phone', 12)->nullable(false)->change();
            $table->string('identity_card', 11)->nullable(false)->change();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('customers');
        Schema::dropIfExists('customer');

        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('address', 100);
            $table->string('phone', 12)->unique();
            $table->string('identity_card', 11)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');       
    }
};

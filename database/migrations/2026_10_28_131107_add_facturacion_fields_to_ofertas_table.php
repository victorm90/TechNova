<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ofertas', function (Blueprint $table) {
            $table->text('nota')->nullable()->after('estado');
            $table->string('forma_pago', 120)->nullable()->after('nota');
            $table->text('observaciones')->nullable()->after('forma_pago');
            $table->string('firma_pie', 150)->nullable()->after('observaciones');
            $table->unsignedSmallInteger('vigencia_dias')->default(15)->after('firma_pie');
            $table->foreignId('almacen_id')
                ->nullable()
                ->after('vigencia_dias')
                ->constrained('almacenes')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->timestamp('facturada_at')->nullable()->after('almacen_id');
        });
    }

    public function down(): void
    {
        Schema::table('ofertas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('almacen_id');
            $table->dropColumn([
                'nota',
                'forma_pago',
                'observaciones',
                'firma_pie',
                'vigencia_dias',
                'facturada_at',
            ]);
        });
    }
};

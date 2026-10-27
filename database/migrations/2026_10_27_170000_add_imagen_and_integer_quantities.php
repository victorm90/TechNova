<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table): void {
            $table->string('imagen', 255)->nullable()->comment('Ruta relativa de la imagen en el disco público');
        });

        $this->normalizarCantidadesExistentes();

        Schema::table('productos', function (Blueprint $table): void {
            $table->unsignedInteger('stock_minimo')->default(0)->change();
        });

        Schema::table('inventarios', function (Blueprint $table): void {
            $table->unsignedInteger('cantidad')->default(0)->change();
        });

        Schema::table('operacion_inventario_lineas', function (Blueprint $table): void {
            $table->unsignedInteger('cantidad')->change();
        });

        Schema::table('movimientos_inventario', function (Blueprint $table): void {
            $table->integer('cantidad')->change();
            $table->unsignedInteger('saldo_resultante')->change();
        });
    }

    public function down(): void
    {
        Schema::table('movimientos_inventario', function (Blueprint $table): void {
            $table->decimal('cantidad', 12, 2)->change();
            $table->decimal('saldo_resultante', 12, 2)->change();
        });

        Schema::table('operacion_inventario_lineas', function (Blueprint $table): void {
            $table->decimal('cantidad', 12, 2)->change();
        });

        Schema::table('inventarios', function (Blueprint $table): void {
            $table->decimal('cantidad', 12, 2)->default(0)->change();
        });

        Schema::table('productos', function (Blueprint $table): void {
            $table->decimal('stock_minimo', 12, 2)->default(0)->change();
        });

        Schema::table('productos', function (Blueprint $table): void {
            $table->dropColumn('imagen');
        });
    }

    /**
     * Redondear las cantidades existentes antes de convertir las columnas a enteros.
     */
    private function normalizarCantidadesExistentes(): void
    {
        DB::table('productos')->update([
            'stock_minimo' => DB::raw('CASE WHEN stock_minimo < 0 THEN 0 ELSE ROUND(stock_minimo) END'),
        ]);

        DB::table('inventarios')->update([
            'cantidad' => DB::raw('CASE WHEN cantidad < 0 THEN 0 ELSE ROUND(cantidad) END'),
        ]);

        DB::table('operacion_inventario_lineas')->update([
            'cantidad' => DB::raw('CASE WHEN cantidad < 0 THEN 0 ELSE ROUND(cantidad) END'),
        ]);

        DB::table('movimientos_inventario')->update([
            'cantidad' => DB::raw('ROUND(cantidad)'),
            'saldo_resultante' => DB::raw('CASE WHEN saldo_resultante < 0 THEN 0 ELSE ROUND(saldo_resultante) END'),
        ]);
    }
};

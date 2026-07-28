<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->foreignId('id_proveedor')
                ->nullable()
                ->after('id')
                ->constrained('proveedores')
                ->nullOnDelete();

            $table->foreignId('id_producto')
                ->nullable()
                ->after('id_proveedor')
                ->constrained('productos')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->dropForeign(['id_proveedor']);
            $table->dropForeign(['id_producto']);
            $table->dropColumn(['id_proveedor', 'id_producto']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('numero')->unique();
            $table->string('cliente_nombre');
            $table->string('cliente_email');
            $table->string('cliente_telefono', 20)->nullable();
            $table->text('direccion')->nullable();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('total', 12, 2);
            $table->string('metodo_pago', 30);
            $table->string('estado', 30)->default('completado')->index();
            $table->timestamp('completado_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};

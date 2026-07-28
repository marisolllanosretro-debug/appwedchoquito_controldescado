<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')->updateOrInsert(['id' => 1], ['nombre' => 'admin', 'descripcion' => 'Administrador del sistema', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('roles')->updateOrInsert(['id' => 2], ['nombre' => 'cajera', 'descripcion' => 'Cajera / Atención al cliente', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('roles')->updateOrInsert(['id' => 3], ['nombre' => 'operador', 'descripcion' => 'Operador del sistema', 'created_at' => now(), 'updated_at' => now()]);

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('id_rol')->nullable()->after('remember_token');
        });

        DB::statement('UPDATE users SET id_rol = (SELECT id FROM roles WHERE roles.nombre = users.role) WHERE role IS NOT NULL');

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('id_rol')->references('id')->on('roles')->nullOnDelete();
        });

        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permissions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 50)->nullable()->after('remember_token');
        });

        DB::statement('UPDATE users SET role = (SELECT nombre FROM roles WHERE roles.id = users.id_rol) WHERE id_rol IS NOT NULL');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_rol']);
            $table->dropColumn('id_rol');
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 80)->unique();
            $table->string('descripcion')->nullable();
            $table->string('grupo')->nullable();
            $table->timestamps();
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->unique(['user_id', 'role_id']);
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->unique(['permission_id', 'role_id']);
        });
    }
};

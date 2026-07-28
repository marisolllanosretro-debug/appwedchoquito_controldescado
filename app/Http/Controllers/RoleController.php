<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarRolRequest;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    public function index(): Response
    {
        $roles = Role::orderBy('id')
            ->get()
            ->map(fn (Role $rol) => [
                'id' => $rol->id,
                'nombre' => $rol->nombre,
                'descripcion' => $rol->descripcion,
                'permisos' => $rol->permisos,
                'usuarios_count' => $rol->usuarios()->count(),
            ]);

        return Inertia::render('Roles/index', [
            'roles' => $roles,
            'modulos' => Role::MODULOS,
            'niveles' => Role::NIVELES,
        ]);
    }

    public function store(GuardarRolRequest $request): RedirectResponse
    {
        Role::create($request->validated());

        return redirect()->back()->with('success', 'Rol creado correctamente.');
    }

    public function update(GuardarRolRequest $request, Role $rol): RedirectResponse
    {
        $rol->update($request->validated());

        return redirect()->back()->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(Role $rol): RedirectResponse
    {
        if ($rol->usuarios()->exists()) {
            return redirect()->back()->with('error', 'No se puede eliminar un rol con usuarios asignados.');
        }

        $rol->delete();

        return redirect()->back()->with('success', 'Rol eliminado correctamente.');
    }
}

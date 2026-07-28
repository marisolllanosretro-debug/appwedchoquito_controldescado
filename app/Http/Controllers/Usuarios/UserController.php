<?php

namespace App\Http\Controllers\Usuarios;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $usuarios = User::with('rol')->latest()->paginate(10);

        return Inertia::render('Usuarios/index', [
            'usuarios' => $usuarios,
            'roles' => Role::all(['id', 'nombre']),
        ]);
    }

    public function store(StoreUsuarioRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        User::create([
            'name' => $validated['name'],
            'apellido_paterno' => $validated['apellido_paterno'] ?? null,
            'apellido_materno' => $validated['apellido_materno'] ?? null,
            'email' => $validated['email'],
            'telefono' => $validated['telefono'] ?? null,
            'fecha_ingreso' => $validated['fecha_ingreso'] ?? null,
            'password' => $validated['password'],
            'id_rol' => $validated['id_rol'],
        ]);

        return redirect()->back()->with('success', 'Usuario creado exitosamente.');
    }

    public function update(UpdateUsuarioRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        if (auth()->id() === $user->id && (int) $validated['id_rol'] !== $user->id_rol) {
            return redirect()->back()->with('error', 'No puedes cambiarte el rol a ti mismo.');
        }

        $data = [
            'name' => $validated['name'],
            'apellido_paterno' => $validated['apellido_paterno'] ?? null,
            'apellido_materno' => $validated['apellido_materno'] ?? null,
            'email' => $validated['email'],
            'telefono' => $validated['telefono'] ?? null,
            'fecha_ingreso' => $validated['fecha_ingreso'] ?? null,
            'id_rol' => $validated['id_rol'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }

        if (array_key_exists('activo', $validated)) {
            $activo = (bool) $validated['activo'];

            if (! $activo && auth()->id() === $user->id) {
                return redirect()->back()->with('error', 'No puedes desactivarte a ti mismo.');
            }

            if (! $activo && $user->esAdministrador()) {
                $adminCount = User::where('activo', true)
                    ->where('id_rol', User::ROL_ADMINISTRADOR)
                    ->count();

                if ($adminCount <= 1) {
                    return redirect()->back()->with('error', 'No puedes desactivar al último administrador activo.');
                }
            }

            $data['activo'] = $activo;
        }

        $user->update($data);

        return redirect()->back()->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->esAdministrador()) {
            $adminCount = User::where('id_rol', User::ROL_ADMINISTRADOR)->count();
            if ($adminCount <= 1) {
                return redirect()->back()->with('error', 'No puedes eliminar al último administrador del sistema.');
            }
        }

        $user->delete();

        return redirect()->back()->with('success', 'Usuario eliminado exitosamente.');
    }

    public function updateEstado(Request $request, User $user): RedirectResponse
    {
        if (auth()->id() === $user->id) {
            return redirect()->back()->with('error', 'No puedes desactivarte a ti mismo.');
        }

        $activo = $request->boolean('activo', false);

        if (! $activo && $user->esAdministrador()) {
            $adminCount = User::where('activo', true)
                ->where('id_rol', User::ROL_ADMINISTRADOR)
                ->count();

            if ($adminCount <= 1) {
                return redirect()->back()->with('error', 'No puedes desactivar al último administrador activo.');
            }
        }

        $user->update(['activo' => $activo]);

        $mensaje = $activo ? 'Usuario reactivado exitosamente.' : 'Usuario desactivado exitosamente.';

        return redirect()->back()->with('success', $mensaje);
    }
}

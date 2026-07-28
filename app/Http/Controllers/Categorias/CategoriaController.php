<?php

namespace App\Http\Controllers\Categorias;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoriaController extends Controller
{
    public function index(): Response
    {
        $categorias = Categoria::withCount('productos')->latest()->paginate(10);

        return Inertia::render('Categorias/index', [
            'categorias' => $categorias,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:50|unique:categorias,nombre',
            'descripcion' => 'nullable|string|max:255',
            'icono' => 'nullable|string|max:10',
            'activo' => 'boolean',
        ]);

        Categoria::create($validated);

        return redirect()->back()->with('success', 'Categoría creada exitosamente.');
    }

    public function update(Request $request, Categoria $categoria): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:50|unique:categorias,nombre,'.$categoria->id,
            'descripcion' => 'nullable|string|max:255',
            'icono' => 'nullable|string|max:10',
            'activo' => 'boolean',
        ]);

        $categoria->update($validated);

        return redirect()->back()->with('success', 'Categoría actualizada exitosamente.');
    }

    public function destroy(Categoria $categoria): RedirectResponse
    {
        Producto::where('id_categoria', $categoria->id)->update([
            'id_categoria' => null,
            'categoria' => $categoria->nombre,
        ]);

        $categoria->delete();

        return redirect()->back()->with('success', 'Categoría eliminada exitosamente.');
    }
}

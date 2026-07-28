<?php

namespace App\Http\Controllers\Productos;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductoController extends Controller
{
    public function index(): Response
    {
        $productos = Producto::with('categoriaRel')->latest()->paginate(10);
        $categorias = Categoria::where('activo', true)->orderBy('nombre')->get(['id', 'nombre', 'icono']);

        return Inertia::render('Productos/index', [
            'productos' => $productos,
            'categorias' => $categorias,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'id_categoria' => 'nullable|exists:categorias,id',
            'categoria' => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:500',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'unidad_medida' => 'required|string|max:50',
        ]);

        if (empty($validated['id_categoria'])) {
            $validated['id_categoria'] = null;
        }

        $producto = Producto::create($validated);

        if ($producto->id_categoria) {
            $cat = Categoria::find($producto->id_categoria);
            if ($cat) {
                $producto->categoria = $cat->nombre;
                $producto->save();
            }
        }

        return redirect()->back()->with('success', 'Producto creado exitosamente.');
    }

    public function update(Request $request, Producto $producto): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'id_categoria' => 'nullable|exists:categorias,id',
            'categoria' => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:500',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'unidad_medida' => 'required|string|max:50',
        ]);

        if (empty($validated['id_categoria'])) {
            $validated['id_categoria'] = null;
        }

        $producto->update($validated);

        if ($producto->id_categoria) {
            $cat = Categoria::find($producto->id_categoria);
            if ($cat) {
                $producto->categoria = $cat->nombre;
                $producto->save();
            }
        }

        return redirect()->back()->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        $producto->delete();

        return redirect()->back()->with('success', 'Producto eliminado exitosamente.');
    }

    public function catalogo(): Response
    {
        $productos = Producto::with('categoriaRel')
            ->whereHas('categoriaRel', fn ($q) => $q->where('activo', true))
            ->orWhereNull('id_categoria')
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get();

        $categorias = Categoria::where('activo', true)->orderBy('nombre')->get(['id', 'nombre', 'icono']);

        return Inertia::render('Catalogo/index', [
            'productos' => $productos,
            'categorias' => $categorias,
        ]);
    }
}

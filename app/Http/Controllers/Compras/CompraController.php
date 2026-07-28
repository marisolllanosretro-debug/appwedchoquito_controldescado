<?php

namespace App\Http\Controllers\Compras;

use App\Http\Controllers\Controller;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompraController extends Controller
{
    public function index(): Response
    {
        $compras = Compra::with(['proveedorRel', 'productoRel'])->latest()->paginate(10);

        $proveedores = Proveedor::orderBy('nombre')->get(['id', 'nombre']);
        $productos = Producto::orderBy('nombre')->get(['id', 'nombre', 'precio_compra', 'stock', 'unidad_medida']);

        return Inertia::render('Compras/index', [
            'compras' => $compras,
            'proveedores' => $proveedores,
            'productos' => $productos,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_proveedor' => 'nullable|exists:proveedores,id',
            'id_producto' => 'nullable|exists:productos,id',
            'proveedor' => 'required|string|max:255',
            'producto' => 'required|string|max:255',
            'cantidad' => 'required|integer|min:1',
            'precio_unitario' => 'required|numeric|min:0',
            'fecha_compra' => 'required|date',
            'estado' => 'required|in:completado,pendiente,cancelado',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $validated['total'] = $validated['cantidad'] * $validated['precio_unitario'];

        $compra = Compra::create($validated);

        if ($compra->estado === 'completado' && $compra->id_producto) {
            $producto = Producto::find($compra->id_producto);
            if ($producto) {
                $producto->increment('stock', $compra->cantidad);
            }
        }

        return redirect()->back()->with('success', 'Compra registrada exitosamente.');
    }

    public function update(Request $request, Compra $compra): RedirectResponse
    {
        $validated = $request->validate([
            'id_proveedor' => 'nullable|exists:proveedores,id',
            'id_producto' => 'nullable|exists:productos,id',
            'proveedor' => 'required|string|max:255',
            'producto' => 'required|string|max:255',
            'cantidad' => 'required|integer|min:1',
            'precio_unitario' => 'required|numeric|min:0',
            'fecha_compra' => 'required|date',
            'estado' => 'required|in:completado,pendiente,cancelado',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $validated['total'] = $validated['cantidad'] * $validated['precio_unitario'];

        $estadoAnterior = $compra->estado;
        $compra->update($validated);

        if ($compra->id_producto) {
            $producto = Producto::find($compra->id_producto);
            if ($producto) {
                if ($estadoAnterior === 'completado' && $compra->estado !== 'completado') {
                    $producto->decrement('stock', $compra->cantidad);
                } elseif ($estadoAnterior !== 'completado' && $compra->estado === 'completado') {
                    $producto->increment('stock', $compra->cantidad);
                }
            }
        }

        return redirect()->back()->with('success', 'Compra actualizada exitosamente.');
    }

    public function destroy(Compra $compra): RedirectResponse
    {
        if ($compra->estado === 'completado' && $compra->id_producto) {
            $producto = Producto::find($compra->id_producto);
            if ($producto) {
                $producto->decrement('stock', $compra->cantidad);
            }
        }

        $compra->delete();

        return redirect()->back()->with('success', 'Compra eliminada exitosamente.');
    }
}

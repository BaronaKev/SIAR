<?php

namespace App\Http\Controllers;

use App\Http\Requests\Menu\ProductoRequest;
use App\Models\Categoria;
use App\Models\Producto;
use App\Services\ProductoImagenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Módulo 2 – Menú (CU007–CU011). Autorización: ProductoPolicy.
 * Las imágenes las maneja ProductoImagenService; el estado por stock, el modelo Producto.
 */
class ProductoController extends Controller
{
    public function __construct(private readonly ProductoImagenService $imagenes)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Producto::class);

        $categoriaId = $request->integer('categoria_id') ?: null;
        $estado      = $request->string('estado')->toString() ?: null;
        $busqueda    = $request->string('buscar')->toString() ?: null;

        $productos = Producto::with('categoria')
            ->when($categoriaId, fn ($query) => $query->where('categoria_id', $categoriaId))
            ->when($estado, fn ($query) => $query->where('estado', $estado))
            ->buscar($busqueda)
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        $categorias = Categoria::orderBy('nombre')->get();

        return view('productos.index', compact('productos', 'categorias', 'categoriaId', 'estado', 'busqueda'));
    }

    public function create(): View
    {
        $this->authorize('create', Producto::class);

        return view('productos.create', ['categorias' => Categoria::orderBy('nombre')->get()]);
    }

    public function store(ProductoRequest $request): RedirectResponse
    {
        $this->authorize('create', Producto::class);

        $datos = $request->datosProducto();

        if ($request->hasFile('imagen')) {
            $datos['imagen'] = $this->imagenes->guardar($request->file('imagen'));
        }

        Producto::create($datos);

        return redirect()->route('productos.index')
            ->with('success', 'Producto agregado exitosamente al menú.');
    }

    public function show(Producto $producto): View
    {
        $this->authorize('view', $producto);

        $producto->load('categoria');

        return view('productos.show', compact('producto'));
    }

    public function edit(Producto $producto): View
    {
        $this->authorize('update', $producto);

        return view('productos.edit', [
            'producto'   => $producto,
            'categorias' => Categoria::orderBy('nombre')->get(),
        ]);
    }

    public function update(ProductoRequest $request, Producto $producto): RedirectResponse
    {
        $this->authorize('update', $producto);

        $datos = $request->datosProducto();

        if ($request->hasFile('imagen')) {
            $datos['imagen'] = $this->imagenes->reemplazar($producto->imagen, $request->file('imagen'));
        }

        $producto->update($datos);

        return redirect()->route('productos.index')
            ->with('success', "Producto {$producto->nombre} actualizado exitosamente.");
    }

    /**
     * RF009: retira el producto del menú (SoftDeletes). Se conserva la imagen porque
     * el producto sigue apareciendo en el historial de pedidos.
     */
    public function destroy(Producto $producto): RedirectResponse
    {
        $this->authorize('delete', $producto);

        $producto->delete();

        return redirect()->route('productos.index')
            ->with('success', "Producto {$producto->nombre} retirado del menú.");
    }

    /** RF011: el cocinero o el administrador cambia la disponibilidad. */
    public function cambiarDisponibilidad(Producto $producto): RedirectResponse
    {
        $this->authorize('cambiarDisponibilidad', $producto);

        $nuevoEstado = $producto->estado === Producto::DISPONIBLE ? Producto::AGOTADO : Producto::DISPONIBLE;

        if ($nuevoEstado === Producto::DISPONIBLE && $producto->stock <= 0) {
            return back()->with('error', "No se puede marcar 'Disponible' porque el stock del producto es 0.");
        }

        $producto->update(['estado' => $nuevoEstado]);

        return back()->with('success', "Disponibilidad de {$producto->nombre} actualizada a '{$nuevoEstado}'.");
    }
}

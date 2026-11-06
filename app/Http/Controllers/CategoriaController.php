<?php

namespace App\Http\Controllers;

use App\Http\Requests\Menu\CategoriaRequest;
use App\Models\Categoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Módulo 2 – Categorías del menú (CU010). Autorización: CategoriaPolicy.
 */
class CategoriaController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Categoria::class);

        $busqueda = $request->string('buscar')->toString() ?: null;

        $categorias = Categoria::withCount('productos')
            ->buscar($busqueda)
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('categorias.index', compact('categorias', 'busqueda'));
    }

    public function create(): View
    {
        $this->authorize('create', Categoria::class);

        return view('categorias.create');
    }

    public function store(CategoriaRequest $request): RedirectResponse
    {
        $this->authorize('create', Categoria::class);

        Categoria::create($request->validated());

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría registrada exitosamente.');
    }

    public function show(Categoria $categoria): View
    {
        $this->authorize('view', $categoria);

        $categoria->load('productos');

        return view('categorias.show', compact('categoria'));
    }

    public function edit(Categoria $categoria): View
    {
        $this->authorize('update', $categoria);

        return view('categorias.edit', compact('categoria'));
    }

    public function update(CategoriaRequest $request, Categoria $categoria): RedirectResponse
    {
        $this->authorize('update', $categoria);

        $categoria->update($request->validated());

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría actualizada exitosamente.');
    }

    /** CU010: no se elimina una categoría con productos asignados. */
    public function destroy(Categoria $categoria): RedirectResponse
    {
        $this->authorize('delete', $categoria);

        if ($categoria->productos()->exists()) {
            return back()->with('error', 'No se puede eliminar la categoría porque tiene productos asignados. Reasigna los productos primero.');
        }

        $categoria->delete();

        return redirect()->route('categorias.index')
            ->with('success', "Categoría {$categoria->nombre} eliminada correctamente.");
    }
}

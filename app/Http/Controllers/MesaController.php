<?php

namespace App\Http\Controllers;

use App\Enums\EstadoMesa;
use App\Http\Requests\Mesas\CambiarEstadoMesaRequest;
use App\Http\Requests\Mesas\MesaRequest;
use App\Models\Mesa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Módulo 3 – Gestión de mesas (CU012–CU016). Autorización: MesaPolicy.
 */
class MesaController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Mesa::class);

        $estado   = $request->string('estado')->toString() ?: null;
        $busqueda = $request->string('buscar')->toString() ?: null;

        $mesas = Mesa::estado($estado)
            ->buscar($busqueda)
            ->orderBy('numero')
            ->paginate(10)
            ->withQueryString();

        return view('mesas.index', compact('mesas', 'estado', 'busqueda'));
    }

    public function create(): View
    {
        $this->authorize('create', Mesa::class);

        return view('mesas.create');
    }

    public function store(MesaRequest $request): RedirectResponse
    {
        $this->authorize('create', Mesa::class);

        Mesa::create($request->validated());

        return redirect()->route('mesas.index')
            ->with('success', 'Mesa creada exitosamente.');
    }

    public function show(Mesa $mesa): View
    {
        $this->authorize('view', $mesa);

        $mesa->load(['pedidosActivos', 'reservasActivas']);

        return view('mesas.show', compact('mesa'));
    }

    public function edit(Mesa $mesa): View
    {
        $this->authorize('update', $mesa);

        return view('mesas.edit', compact('mesa'));
    }

    public function update(MesaRequest $request, Mesa $mesa): RedirectResponse
    {
        $this->authorize('update', $mesa);

        $mesa->update($request->validated());

        return redirect()->route('mesas.index')
            ->with('success', 'Mesa actualizada correctamente.');
    }

    public function destroy(Mesa $mesa): RedirectResponse
    {
        $this->authorize('delete', $mesa);

        // Los pedidos conservan su mesa (historial y reportes de ocupación).
        if ($mesa->pedidos()->exists() || $mesa->reservasActivas()->exists()) {
            return back()->with('error', "No se puede eliminar la mesa {$mesa->numero} porque tiene pedidos o reservas registradas. Puedes ponerla en Mantenimiento.");
        }

        $mesa->delete();

        return redirect()->route('mesas.index')
            ->with('success', 'Mesa eliminada del sistema.');
    }

    /** Acción rápida para meseros y cajeros. */
    public function cambiarEstado(CambiarEstadoMesaRequest $request, Mesa $mesa): RedirectResponse
    {
        $this->authorize('cambiarEstado', $mesa);

        $estado = EstadoMesa::from($request->validated('estado'));

        if ($estado === EstadoMesa::Disponible && $mesa->pedidosActivos()->exists()) {
            return back()->with('error', "La mesa {$mesa->numero} tiene pedidos sin facturar; no puede quedar Disponible.");
        }

        $mesa->cambiarEstado($estado);

        return back()->with('success', "Estado de la mesa {$mesa->numero} actualizado a {$estado->value}.");
    }
}

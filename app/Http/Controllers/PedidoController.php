<?php

namespace App\Http\Controllers;

use App\Enums\EstadoMesa;
use App\Enums\EstadoPedido;
use App\Http\Requests\Pedidos\StorePedidoRequest;
use App\Models\Categoria;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\Producto;
use App\Services\PedidoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Módulo 4 – Pedidos (CU017, CU022). Autorización: PedidoPolicy. Lógica: PedidoService.
 */
class PedidoController extends Controller
{
    public function __construct(private readonly PedidoService $pedidos)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Pedido::class);

        $estado = $request->string('estado')->toString() ?: null;
        $mesaId = $request->integer('mesa_id') ?: null;

        $pedidos = Pedido::with(['mesa', 'usuario'])
            ->withCount('detalles')
            ->estado($estado)
            ->when($mesaId, fn ($query) => $query->where('mesa_id', $mesaId))
            ->latest('fecha')
            ->paginate(15)
            ->withQueryString();

        return view('pedidos.index', [
            'pedidos' => $pedidos,
            'estados' => EstadoPedido::cases(),
            'mesas'   => Mesa::orderBy('numero')->get(),
            'estado'  => $estado,
            'mesaId'  => $mesaId,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Pedido::class);

        return view('pedidos.create', [
            'mesas'        => Mesa::where('estado', '!=', EstadoMesa::Mantenimiento->value)->orderBy('numero')->get(),
            'categorias'   => Categoria::with(['productos' => fn ($q) => $q->disponibles()->orderBy('nombre')])
                ->orderBy('nombre')
                ->get()
                ->filter(fn (Categoria $categoria) => $categoria->productos->isNotEmpty()),
            'mesaSeleccionada' => $request->integer('mesa_id') ?: null,
        ]);
    }

    public function store(StorePedidoRequest $request): RedirectResponse
    {
        $this->authorize('create', Pedido::class);

        $pedido = $this->pedidos->crear(
            Mesa::findOrFail($request->validated('mesa_id')),
            $request->user(),
            $request->productos(),
            $request->validated('observaciones'),
        );

        return redirect()->route('pedidos.show', $pedido)
            ->with('success', "Pedido #{$pedido->id} creado en estado Pendiente.");
    }

    public function show(Pedido $pedido): View
    {
        $this->authorize('view', $pedido);

        $pedido->load(['mesa', 'usuario', 'detalles.producto', 'factura']);

        return view('pedidos.show', [
            'pedido'      => $pedido,
            'productos'   => Producto::disponibles()->orderBy('nombre')->get(),
            'transiciones' => $pedido->estado->transicionesPermitidas(),
        ]);
    }
}

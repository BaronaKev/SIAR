<?php

namespace App\Http\Controllers;

use App\Enums\EstadoMesa;
use App\Enums\EstadoReserva;
use App\Http\Requests\Reservas\ReservaRequest;
use App\Models\Mesa;
use App\Models\Reserva;
use App\Services\ReservaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Reservas (CU015 / CU016). Autorización: ReservaPolicy. Lógica: ReservaService.
 */
class ReservaController extends Controller
{
    public function __construct(private readonly ReservaService $reservas)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Reserva::class);

        $estado = $request->string('estado')->toString() ?: EstadoReserva::Activa->value;
        $fecha  = $request->string('fecha')->toString() ?: null;

        $reservas = Reserva::with(['mesa', 'usuario'])
            ->when($estado !== 'todas', fn ($query) => $query->where('estado', $estado))
            ->when($fecha, fn ($query) => $query->whereDate('fecha', $fecha))
            ->orderBy('fecha')
            ->orderBy('hora')
            ->paginate(15)
            ->withQueryString();

        return view('reservas.index', [
            'reservas' => $reservas,
            'estados'  => EstadoReserva::cases(),
            'estado'   => $estado,
            'fecha'    => $fecha,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Reserva::class);

        return view('reservas.create', [
            'mesas' => Mesa::where('estado', EstadoMesa::Disponible->value)->orderBy('numero')->get(),
        ]);
    }

    public function store(ReservaRequest $request): RedirectResponse
    {
        $this->authorize('create', Reserva::class);

        $reserva = $this->reservas->crear($request->validated(), $request->user());

        return redirect()->route('reservas.index')
            ->with('success', "Mesa {$reserva->mesa->numero} reservada para {$reserva->nombre_cliente}.");
    }

    public function edit(Reserva $reserva): View
    {
        $this->authorize('update', $reserva);

        return view('reservas.edit', compact('reserva'));
    }

    public function update(ReservaRequest $request, Reserva $reserva): RedirectResponse
    {
        $this->authorize('update', $reserva);

        $this->reservas->actualizar($reserva, $request->validated());

        return redirect()->route('reservas.index')->with('success', 'Reserva actualizada.');
    }

    public function cancelar(Reserva $reserva): RedirectResponse
    {
        $this->authorize('cancelar', $reserva);

        $this->reservas->cancelar($reserva);

        return back()->with('success', "Reserva de {$reserva->nombre_cliente} cancelada.");
    }

    public function cumplir(Reserva $reserva): RedirectResponse
    {
        $this->authorize('update', $reserva);

        $this->reservas->cumplir($reserva);

        return redirect()->route('pedidos.create', ['mesa_id' => $reserva->mesa_id])
            ->with('success', "Bienvenido {$reserva->nombre_cliente}. Ya puedes tomar el pedido de la mesa {$reserva->mesa->numero}.");
    }
}

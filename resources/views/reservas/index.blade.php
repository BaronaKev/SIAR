@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0"><i class="bi bi-calendar-check text-primary"></i> Reservas</h2>
            <p class="text-muted small mb-0">Reservas de mesas por fecha y hora (CU015 / CU016).</p>
        </div>
        @can('create', App\Models\Reserva::class)
        <a href="{{ route('reservas.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Nueva reserva</a>
        @endcan
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reservas.index') }}" class="row g-3">
                <div class="col-md-4">
                    <select name="estado" class="form-select" onchange="this.form.submit()">
                        @foreach($estados as $opcion)
                            <option value="{{ $opcion->value }}" @selected($estado === $opcion->value)>{{ $opcion->value }}</option>
                        @endforeach
                        <option value="todas" @selected($estado === 'todas')>Todas</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="date" name="fecha" class="form-control" value="{{ $fecha }}" onchange="this.form.submit()">
                </div>
                <div class="col-md-4">
                    <a href="{{ route('reservas.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i> Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">Fecha</th>
                        <th>Hora</th>
                        <th>Mesa</th>
                        <th>Cliente</th>
                        <th class="text-center">Personas</th>
                        <th>Estado</th>
                        <th class="text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reservas as $reserva)
                    <tr>
                        <td class="ps-3">{{ $reserva->fecha->format('d/m/Y') }}</td>
                        <td>{{ substr($reserva->hora, 0, 5) }}</td>
                        <td>Mesa {{ $reserva->mesa->numero }}</td>
                        <td>{{ $reserva->nombre_cliente }}</td>
                        <td class="text-center">{{ $reserva->personas }}</td>
                        <td>
                            <span class="badge {{ match($reserva->estado) {
                                App\Enums\EstadoReserva::Activa    => 'bg-warning text-dark',
                                App\Enums\EstadoReserva::Cumplida  => 'bg-success',
                                App\Enums\EstadoReserva::Cancelada => 'bg-secondary',
                            } }}">{{ $reserva->estado->value }}</span>
                        </td>
                        <td class="text-end pe-3">
                            <div class="btn-group btn-group-sm">
                                @can('update', $reserva)
                                <form action="{{ route('reservas.cumplir', $reserva) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-outline-success" title="El cliente llegó"><i class="bi bi-person-check"></i></button>
                                </form>
                                <a href="{{ route('reservas.edit', $reserva) }}" class="btn btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                @endcan
                                @can('cancelar', $reserva)
                                <form action="{{ route('reservas.cancelar', $reserva) }}" method="POST"
                                      onsubmit="return confirm('¿Cancelar la reserva de {{ $reserva->nombre_cliente }}?');">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-outline-danger" title="Cancelar"><i class="bi bi-x-circle"></i></button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No hay reservas con esos filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reservas->hasPages())
        <div class="card-footer bg-white py-3">{{ $reservas->links() }}</div>
        @endif
    </div>
</div>
@endsection

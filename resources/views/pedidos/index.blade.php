@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0"><i class="bi bi-receipt text-primary"></i> Pedidos</h2>
            <p class="text-muted small mb-0">Seguimiento de las órdenes desde el salón hasta la caja.</p>
        </div>
        @can('create', App\Models\Pedido::class)
        <a href="{{ route('pedidos.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nuevo pedido
        </a>
        @endcan
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('pedidos.index') }}" class="row g-3">
                <div class="col-md-4">
                    <select name="estado" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Todos los estados --</option>
                        @foreach($estados as $opcion)
                            <option value="{{ $opcion->value }}" @selected($estado === $opcion->value)>{{ $opcion->value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="mesa_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Todas las mesas --</option>
                        @foreach($mesas as $mesa)
                            <option value="{{ $mesa->id }}" @selected($mesaId === $mesa->id)>Mesa {{ $mesa->numero }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-funnel"></i> Filtrar</button>
                    @if($estado || $mesaId)
                        <a href="{{ route('pedidos.index') }}" class="btn btn-outline-secondary" title="Limpiar filtros"><i class="bi bi-x-circle"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Fecha</th>
                            <th>Mesa</th>
                            <th>Atendido por</th>
                            <th class="text-center">Productos</th>
                            <th class="text-end">Total</th>
                            <th>Estado</th>
                            <th class="text-end pe-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pedidos as $pedido)
                        <tr>
                            <td class="ps-3 fw-semibold">{{ $pedido->id }}</td>
                            <td>{{ $pedido->fecha->format('d/m/Y H:i') }}</td>
                            <td>Mesa {{ $pedido->mesa->numero }}</td>
                            <td>{{ $pedido->usuario->name }}</td>
                            <td class="text-center">{{ $pedido->detalles_count }}</td>
                            <td class="text-end">${{ number_format((float) $pedido->total, 0, ',', '.') }}</td>
                            <td><span class="badge {{ $pedido->estado->colorBadge() }}">{{ $pedido->estado->value }}</span></td>
                            <td class="text-end pe-3">
                                <a href="{{ route('pedidos.show', $pedido) }}" class="btn btn-sm btn-outline-info" title="Ver detalle">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i> No hay pedidos con esos filtros.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($pedidos->hasPages())
        <div class="card-footer bg-white py-3">{{ $pedidos->links() }}</div>
        @endif
    </div>
</div>
@endsection

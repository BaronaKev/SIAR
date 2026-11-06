@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h2 class="h3 mb-0"><i class="bi bi-file-earmark-text text-primary"></i> Facturación</h2>
        <p class="text-muted small mb-0">Pedidos entregados por facturar y facturas emitidas.</p>
    </div>

    @if($porFacturar->isNotEmpty())
    <div class="card shadow-sm mb-4 border-primary">
        <div class="card-header bg-primary text-white"><i class="bi bi-hourglass-split"></i> Pedidos entregados sin factura</div>
        <ul class="list-group list-group-flush">
            @foreach($porFacturar as $pedido)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>
                    <a href="{{ route('pedidos.show', $pedido) }}">Pedido #{{ $pedido->id }}</a> · Mesa {{ $pedido->mesa->numero }}
                    · ${{ number_format((float) $pedido->total, 0, ',', '.') }}
                </span>
                @can('create', [App\Models\Factura::class, $pedido])
                <form action="{{ route('facturas.store', $pedido) }}" method="POST">
                    @csrf
                    <button class="btn btn-sm btn-primary"><i class="bi bi-file-earmark-plus"></i> Generar factura</button>
                </form>
                @endcan
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('facturas.index') }}" class="row g-3">
                <div class="col-md-4">
                    <select name="estado" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Todos los estados --</option>
                        @foreach($estados as $opcion)
                            <option value="{{ $opcion->value }}" @selected($estado === $opcion->value)>{{ $opcion->value }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">Número</th>
                        <th>Fecha</th>
                        <th>Pedido</th>
                        <th class="text-end">Total</th>
                        <th>Estado</th>
                        <th class="text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($facturas as $factura)
                    <tr>
                        <td class="ps-3 fw-semibold">{{ $factura->numero }}</td>
                        <td>{{ $factura->fecha->format('d/m/Y H:i') }}</td>
                        <td>#{{ $factura->pedido_id }} · Mesa {{ $factura->pedido->mesa->numero }}</td>
                        <td class="text-end">${{ number_format((float) $factura->total, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge {{ $factura->estado === App\Enums\EstadoFactura::Pagada ? 'bg-success' : ($factura->estado === App\Enums\EstadoFactura::Anulada ? 'bg-danger' : 'bg-warning text-dark') }}">
                                {{ $factura->estado->value }}
                            </span>
                        </td>
                        <td class="text-end pe-3">
                            <a href="{{ route('facturas.show', $factura) }}" class="btn btn-sm btn-outline-info" title="Ver"><i class="bi bi-eye"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No hay facturas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($facturas->hasPages())
        <div class="card-footer bg-white py-3">{{ $facturas->links() }}</div>
        @endif
    </div>
</div>
@endsection

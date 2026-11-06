@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0">
                <i class="bi bi-receipt text-primary"></i> Pedido #{{ $pedido->id }}
                <span class="badge {{ $pedido->estado->colorBadge() }} fs-6 align-middle">{{ $pedido->estado->value }}</span>
            </h2>
            <p class="text-muted small mb-0">
                Mesa {{ $pedido->mesa->numero }} · {{ $pedido->fecha->format('d/m/Y H:i') }} · atendido por {{ $pedido->usuario->name }}
            </p>
        </div>
        <a href="{{ route('pedidos.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- Acciones de estado: solo aparecen las que la Policy permite al rol actual --}}
    <div class="d-flex flex-wrap gap-2 mb-4">
        @foreach($transiciones as $destino)
            @can('cambiarEstado', [$pedido, $destino])
            <form action="{{ route('pedidos.estado', $pedido) }}" method="POST"
                  @if($destino === App\Enums\EstadoPedido::Cancelado) onsubmit="return confirm('¿Cancelar el pedido #{{ $pedido->id }}?');" @endif>
                @csrf @method('PATCH')
                <input type="hidden" name="estado" value="{{ $destino->value }}">
                <button type="submit" class="btn {{ $destino === App\Enums\EstadoPedido::Cancelado ? 'btn-outline-danger' : 'btn-success' }}">
                    {{ $destino->accion() }}
                </button>
            </form>
            @endcan
        @endforeach

        @can('create', [App\Models\Factura::class, $pedido])
        <form action="{{ route('facturas.store', $pedido) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary"><i class="bi bi-file-earmark-plus"></i> Generar factura</button>
        </form>
        @endcan

        @if($pedido->factura)
            @can('view', $pedido->factura)
            <a href="{{ route('facturas.show', $pedido->factura) }}" class="btn btn-outline-primary">
                <i class="bi bi-file-earmark-text"></i> Ver factura {{ $pedido->factura->numero }}
            </a>
            @endcan
        @endif
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white"><i class="bi bi-list-ul"></i> Productos</div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Producto</th>
                                <th class="text-center" style="width: 180px">Cantidad</th>
                                <th class="text-end">Precio</th>
                                <th class="text-end pe-3">Subtotal</th>
                                @can('update', $pedido)<th></th>@endcan
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pedido->detalles as $detalle)
                            <tr>
                                <td class="ps-3">
                                    {{ $detalle->producto->nombre }}
                                    @if($detalle->producto->trashed())<span class="badge bg-light text-muted">retirado del menú</span>@endif
                                </td>
                                <td class="text-center">
                                    @can('update', $pedido)
                                    <form action="{{ route('pedidos.detalles.update', [$pedido, $detalle]) }}" method="POST" class="d-flex gap-1 justify-content-center">
                                        @csrf @method('PATCH')
                                        <input type="number" name="cantidad" value="{{ $detalle->cantidad }}" min="1" max="100" class="form-control form-control-sm" style="width: 70px">
                                        <button class="btn btn-sm btn-outline-primary" title="Actualizar cantidad"><i class="bi bi-arrow-repeat"></i></button>
                                    </form>
                                    @else
                                        {{ $detalle->cantidad }}
                                    @endcan
                                </td>
                                <td class="text-end">${{ number_format((float) $detalle->precio_unitario, 0, ',', '.') }}</td>
                                <td class="text-end pe-3">${{ number_format((float) $detalle->subtotal, 0, ',', '.') }}</td>
                                @can('update', $pedido)
                                <td class="text-end pe-3">
                                    <form action="{{ route('pedidos.detalles.destroy', [$pedido, $detalle]) }}" method="POST"
                                          onsubmit="return confirm('¿Quitar {{ $detalle->producto->nombre }} del pedido?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Quitar"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                                @endcan
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">El pedido no tiene productos.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="3" class="text-end">Total</td>
                                <td class="text-end pe-3">${{ number_format((float) $pedido->total, 0, ',', '.') }}</td>
                                @can('update', $pedido)<td></td>@endcan
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if($pedido->observaciones)
            <div class="alert alert-light border mt-3 mb-0"><i class="bi bi-chat-left-text"></i> {{ $pedido->observaciones }}</div>
            @endif
        </div>

        <div class="col-lg-4">
            @can('agregarProductos', $pedido)
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white"><i class="bi bi-plus-square"></i> Agregar producto</div>
                <div class="card-body">
                    <form action="{{ route('pedidos.detalles.store', $pedido) }}" method="POST">
                        @csrf
                        <label for="producto_id" class="form-label fw-semibold">Producto</label>
                        <select name="producto_id" id="producto_id" class="form-select mb-3" required>
                            <option value="">-- Seleccione --</option>
                            @foreach($productos as $producto)
                                <option value="{{ $producto->id }}">{{ $producto->nombre }} · ${{ number_format((float) $producto->precio, 0, ',', '.') }} (stock {{ $producto->stock }})</option>
                            @endforeach
                        </select>
                        <label for="cantidad" class="form-label fw-semibold">Cantidad</label>
                        <input type="number" name="cantidad" id="cantidad" value="1" min="1" max="100" class="form-control mb-3" required>
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-circle"></i> Agregar</button>
                    </form>
                </div>
            </div>
            @endcan
        </div>
    </div>
</div>
@endsection

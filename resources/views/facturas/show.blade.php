@extends('layouts.app')

@php($pesos = fn ($valor) => '$' . number_format((float) $valor, 0, ',', '.'))

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0">
                <i class="bi bi-file-earmark-text text-primary"></i> Factura {{ $factura->numero }}
                <span class="badge {{ $factura->estado === App\Enums\EstadoFactura::Pagada ? 'bg-success' : 'bg-warning text-dark' }} fs-6 align-middle">{{ $factura->estado->value }}</span>
            </h2>
            <p class="text-muted small mb-0">
                <a href="{{ route('pedidos.show', $factura->pedido) }}">Pedido #{{ $factura->pedido_id }}</a>
                · Mesa {{ $factura->pedido->mesa->numero }} · {{ $factura->fecha->format('d/m/Y H:i') }}
            </p>
        </div>
        <div class="d-flex gap-2">
            @if($factura->estado === App\Enums\EstadoFactura::Pagada)
            <a href="{{ route('facturas.comprobante', $factura) }}" target="_blank" class="btn btn-success">
                <i class="bi bi-printer"></i> Comprobante
            </a>
            @endif
            <a href="{{ route('facturas.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white"><i class="bi bi-list-ul"></i> Detalle</div>
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr><th class="ps-3">Producto</th><th class="text-center">Cant.</th><th class="text-end">Precio</th><th class="text-end pe-3">Subtotal</th></tr>
                    </thead>
                    <tbody>
                        @foreach($factura->pedido->detalles as $detalle)
                        <tr>
                            <td class="ps-3">{{ $detalle->producto->nombre }}</td>
                            <td class="text-center">{{ $detalle->cantidad }}</td>
                            <td class="text-end">{{ $pesos($detalle->precio_unitario) }}</td>
                            <td class="text-end pe-3">{{ $pesos($detalle->subtotal) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr><td colspan="3" class="text-end">Subtotal</td><td class="text-end pe-3">{{ $pesos($factura->subtotal) }}</td></tr>
                        <tr><td colspan="3" class="text-end">Descuento</td><td class="text-end pe-3 text-danger">− {{ $pesos($factura->descuento) }}</td></tr>
                        <tr><td colspan="3" class="text-end">Impuesto ({{ rtrim(rtrim($factura->porcentaje_impuesto, '0'), '.') }} %)</td><td class="text-end pe-3">{{ $pesos($factura->impuesto) }}</td></tr>
                        <tr class="fw-bold fs-5"><td colspan="3" class="text-end">Total</td><td class="text-end pe-3">{{ $pesos($factura->total) }}</td></tr>
                    </tfoot>
                </table>
            </div>

            <div class="card shadow-sm mt-4">
                <div class="card-header"><i class="bi bi-cash-coin"></i> Pagos registrados</div>
                <ul class="list-group list-group-flush">
                    @forelse($factura->pagos as $pago)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $pago->fecha->format('d/m/Y H:i') }} · {{ $pago->metodo->value }} @if($pago->referencia)<small class="text-muted">({{ $pago->referencia }})</small>@endif</span>
                            <strong>{{ $pesos($pago->monto) }}</strong>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">Sin pagos.</li>
                    @endforelse
                    <li class="list-group-item d-flex justify-content-between fw-semibold">
                        <span>Saldo pendiente</span><span>{{ $pesos($factura->saldoPendiente()) }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="col-lg-5">
            @can('aplicarDescuento', $factura)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white"><i class="bi bi-percent"></i> Aplicar descuento</div>
                <div class="card-body">
                    <form action="{{ route('facturas.descuento', $factura) }}" method="POST" class="row g-2">
                        @csrf @method('PATCH')
                        <div class="col-6">
                            <select name="tipo" class="form-select" required>
                                <option value="porcentaje" @selected(old('tipo') === 'porcentaje')>Porcentaje (%)</option>
                                <option value="valor_fijo" @selected(old('tipo') === 'valor_fijo')>Valor fijo ($)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <input type="number" name="valor" step="0.01" min="0" class="form-control" placeholder="Valor" value="{{ old('valor') }}" required>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-outline-primary w-100">Aplicar</button>
                        </div>
                    </form>
                </div>
            </div>
            @endcan

            @can('registrarPago', $factura)
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white"><i class="bi bi-credit-card"></i> Registrar pago</div>
                <div class="card-body">
                    <form action="{{ route('facturas.pagos.store', $factura) }}" method="POST">
                        @csrf
                        <label class="form-label fw-semibold" for="metodo">Método</label>
                        <select name="metodo" id="metodo" class="form-select mb-3" required>
                            @foreach($metodos as $metodo)
                                <option value="{{ $metodo->value }}" @selected(old('metodo') === $metodo->value)>{{ $metodo->value }}</option>
                            @endforeach
                        </select>

                        <label class="form-label fw-semibold" for="monto">Monto a pagar</label>
                        <input type="number" name="monto" id="monto" step="0.01" min="0" class="form-control mb-3"
                               value="{{ old('monto', $factura->saldoPendiente()) }}" required>

                        <label class="form-label fw-semibold" for="monto_recibido">Dinero recibido <small class="text-muted">(efectivo)</small></label>
                        <input type="number" name="monto_recibido" id="monto_recibido" step="0.01" min="0" class="form-control mb-3"
                               value="{{ old('monto_recibido') }}">

                        <label class="form-label fw-semibold" for="referencia">Referencia <small class="text-muted">(tarjeta o transferencia)</small></label>
                        <input type="text" name="referencia" id="referencia" maxlength="100" class="form-control mb-3" value="{{ old('referencia') }}">

                        <button class="btn btn-success w-100"><i class="bi bi-check-circle"></i> Registrar pago</button>
                    </form>
                </div>
            </div>
            @endcan
        </div>
    </div>
</div>
@endsection

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante {{ $factura->numero }} - SIAR</title>
    <style>
        body { font-family: "Courier New", monospace; max-width: 380px; margin: 20px auto; color: #000; }
        h1 { font-size: 18px; text-align: center; margin: 0; }
        .centro { text-align: center; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        td { padding: 2px 0; }
        .der { text-align: right; }
        .linea { border-top: 1px dashed #000; margin: 8px 0; }
        .total td { font-weight: bold; font-size: 15px; }
        @media print { .no-imprimir { display: none; } }
    </style>
</head>
<body>
    @php($pesos = fn ($valor) => '$' . number_format((float) $valor, 0, ',', '.'))

    <h1>{{ config('app.name', 'SIAR') }}</h1>
    <p class="centro">Comprobante de pago<br>{{ $factura->numero }}</p>
    <p>Fecha: {{ $factura->fecha->format('d/m/Y H:i') }}<br>
       Mesa: {{ $factura->pedido->mesa->numero }} · Pedido #{{ $factura->pedido_id }}</p>

    <div class="linea"></div>
    <table>
        @foreach($factura->pedido->detalles as $detalle)
        <tr>
            <td>{{ $detalle->cantidad }} × {{ $detalle->producto->nombre }}</td>
            <td class="der">{{ $pesos($detalle->subtotal) }}</td>
        </tr>
        @endforeach
    </table>
    <div class="linea"></div>
    <table>
        <tr><td>Subtotal</td><td class="der">{{ $pesos($factura->subtotal) }}</td></tr>
        <tr><td>Descuento</td><td class="der">− {{ $pesos($factura->descuento) }}</td></tr>
        <tr><td>Impuesto ({{ rtrim(rtrim($factura->porcentaje_impuesto, '0'), '.') }} %)</td><td class="der">{{ $pesos($factura->impuesto) }}</td></tr>
        <tr class="total"><td>TOTAL</td><td class="der">{{ $pesos($factura->total) }}</td></tr>
    </table>
    <div class="linea"></div>
    <table>
        @foreach($factura->pagos as $pago)
        <tr><td>{{ $pago->metodo->value }}</td><td class="der">{{ $pesos($pago->monto) }}</td></tr>
        @if($pago->cambio() > 0)
        <tr><td>Vueltas</td><td class="der">{{ $pesos($pago->cambio()) }}</td></tr>
        @endif
        @endforeach
    </table>
    <div class="linea"></div>
    <p class="centro">¡Gracias por su visita!</p>

    <p class="centro no-imprimir">
        <button onclick="window.print()">Imprimir / Guardar PDF</button>
    </p>
</body>
</html>

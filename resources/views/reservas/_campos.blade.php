{{-- Campos compartidos por crear y editar reserva. Recibe opcionalmente $reserva. --}}
<div class="col-md-6">
    <label for="nombre_cliente" class="form-label fw-semibold">Nombre del cliente <span class="text-danger">*</span></label>
    <input type="text" name="nombre_cliente" id="nombre_cliente" maxlength="150" required
           class="form-control @error('nombre_cliente') is-invalid @enderror"
           value="{{ old('nombre_cliente', $reserva->nombre_cliente ?? '') }}">
    @error('nombre_cliente')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="col-md-6">
    <label for="personas" class="form-label fw-semibold">Personas <span class="text-danger">*</span></label>
    <input type="number" name="personas" id="personas" min="1" max="50" required
           class="form-control @error('personas') is-invalid @enderror"
           value="{{ old('personas', $reserva->personas ?? 2) }}">
    @error('personas')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="col-md-6">
    <label for="fecha" class="form-label fw-semibold">Fecha <span class="text-danger">*</span></label>
    <input type="date" name="fecha" id="fecha" min="{{ now()->toDateString() }}" required
           class="form-control @error('fecha') is-invalid @enderror"
           value="{{ old('fecha', isset($reserva) ? $reserva->fecha->toDateString() : now()->toDateString()) }}">
    @error('fecha')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="col-md-6">
    <label for="hora" class="form-label fw-semibold">Hora <span class="text-danger">*</span></label>
    <input type="time" name="hora" id="hora" required
           class="form-control @error('hora') is-invalid @enderror"
           value="{{ old('hora', isset($reserva) ? substr($reserva->hora, 0, 5) : '') }}">
    @error('hora')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

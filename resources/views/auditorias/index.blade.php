@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h2 class="h3 mb-0"><i class="bi bi-shield-check text-primary"></i> Auditoría</h2>
        <p class="text-muted small mb-0">Acciones registradas por usuario, fecha y hora (RNF006).</p>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('auditorias.index') }}" class="row g-3">
                <div class="col-md-3">
                    <select name="user_id" class="form-select">
                        <option value="">-- Todos los usuarios --</option>
                        @foreach($usuarios as $usuario)
                            <option value="{{ $usuario->id }}" @selected($filtros['usuarioId'] === $usuario->id)>{{ $usuario->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="entidad" class="form-select">
                        <option value="">-- Entidad --</option>
                        @foreach($entidades as $entidad)
                            <option value="{{ $entidad }}" @selected($filtros['entidad'] === $entidad)>{{ $entidad }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="accion" class="form-select">
                        <option value="">-- Acción --</option>
                        @foreach($acciones as $accion)
                            <option value="{{ $accion }}" @selected($filtros['accion'] === $accion)>{{ $accion }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><input type="date" name="desde" class="form-control" value="{{ $filtros['desde'] }}" title="Desde"></div>
                <div class="col-md-2"><input type="date" name="hasta" class="form-control" value="{{ $filtros['hasta'] }}" title="Hasta"></div>
                <div class="col-md-1 d-flex gap-1">
                    <button class="btn btn-secondary w-100" title="Filtrar"><i class="bi bi-funnel"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">Fecha y hora</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Entidad</th>
                        <th>Detalle</th>
                        <th class="pe-3">IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registros as $registro)
                    <tr>
                        <td class="ps-3 text-nowrap">{{ $registro->fecha->format('d/m/Y H:i:s') }}</td>
                        <td>{{ $registro->usuario?->name ?? 'Sistema' }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $registro->accion }}</span></td>
                        <td>{{ $registro->entidad }}@if($registro->entidad_id) #{{ $registro->entidad_id }}@endif</td>
                        <td class="small text-muted">{{ $registro->descripcion }}</td>
                        <td class="pe-3 small">{{ $registro->ip_address }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Sin registros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($registros->hasPages())
        <div class="card-footer bg-white py-3">{{ $registros->links() }}</div>
        @endif
    </div>
</div>
@endsection

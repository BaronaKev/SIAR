<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * RNF006: consulta del registro de auditoría (solo administrador).
 */
class AuditoriaController extends Controller
{
    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', Auditoria::class);

        $usuarioId = $request->integer('user_id') ?: null;
        $entidad   = $request->string('entidad')->toString() ?: null;
        $accion    = $request->string('accion')->toString() ?: null;
        $desde     = $request->string('desde')->toString() ?: null;
        $hasta     = $request->string('hasta')->toString() ?: null;

        $registros = Auditoria::with('usuario')
            ->when($usuarioId, fn ($query) => $query->where('user_id', $usuarioId))
            ->when($entidad, fn ($query) => $query->where('entidad', $entidad))
            ->when($accion, fn ($query) => $query->where('accion', $accion))
            ->when($desde, fn ($query) => $query->whereDate('fecha', '>=', $desde))
            ->when($hasta, fn ($query) => $query->whereDate('fecha', '<=', $hasta))
            ->latest('fecha')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('auditorias.index', [
            'registros' => $registros,
            'usuarios'  => User::withTrashed()->orderBy('name')->get(['id', 'name']),
            'entidades' => Auditoria::query()->distinct()->orderBy('entidad')->pluck('entidad'),
            'acciones'  => Auditoria::query()->distinct()->orderBy('accion')->pluck('accion'),
            'filtros'   => compact('usuarioId', 'entidad', 'accion', 'desde', 'hasta'),
        ]);
    }
}

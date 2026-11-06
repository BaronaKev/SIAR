<?php

namespace App\Http\Controllers;

use App\Http\Requests\Usuarios\StoreUsuarioRequest;
use App\Http\Requests\Usuarios\UpdateUsuarioRequest;
use App\Models\User;
use App\Services\UsuarioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

/**
 * Módulo 1 – Gestión de usuarios (CU001–CU004).
 * Autorización: UserPolicy. Validación: Form Requests. Lógica: UsuarioService.
 */
class UsuarioController extends Controller
{
    public function __construct(private readonly UsuarioService $usuarios)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $rol      = $request->string('rol')->toString() ?: null;
        $busqueda = $request->string('buscar')->toString() ?: null;

        $usuarios = User::with('roles')
            ->when($rol, fn ($query) => $query->role($rol))
            ->buscar($busqueda)
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $roles = Role::orderBy('name')->get();

        return view('usuarios.index', compact('usuarios', 'roles', 'rol', 'busqueda'));
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('usuarios.create', ['roles' => Role::orderBy('name')->get()]);
    }

    public function store(StoreUsuarioRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $usuario = $this->usuarios->crear($request->validated());

        return redirect()->route('usuarios.index')
            ->with('success', "Usuario {$usuario->name} creado exitosamente con el rol {$request->validated('role')}.");
    }

    public function show(User $usuario): View
    {
        $this->authorize('view', $usuario);

        $usuario->load('roles.permissions');

        return view('usuarios.show', compact('usuario'));
    }

    public function edit(User $usuario): View
    {
        $this->authorize('update', $usuario);

        $roles    = Role::orderBy('name')->get();
        $userRole = $usuario->roles->first()?->name;

        return view('usuarios.edit', compact('usuario', 'roles', 'userRole'));
    }

    public function update(UpdateUsuarioRequest $request, User $usuario): RedirectResponse
    {
        $this->authorize('update', $usuario);

        $this->usuarios->actualizar($usuario, $request->validated(), $request->user());

        return redirect()->route('usuarios.index')
            ->with('success', "Usuario {$usuario->name} actualizado correctamente.");
    }

    public function destroy(Request $request, User $usuario): RedirectResponse
    {
        if ($request->user()->is($usuario)) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta de usuario en sesión.');
        }

        $this->authorize('delete', $usuario);

        $this->usuarios->eliminar($usuario);

        return redirect()->route('usuarios.index')
            ->with('success', "Usuario {$usuario->name} eliminado del sistema.");
    }
}

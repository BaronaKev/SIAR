@extends('layouts.autenticacion')

@section('titulo', 'Registrarse')
@section('subtitulo', 'Crea tu cuenta. El administrador te asignará un rol para acceder a los módulos.')

@section('content')
<form method="POST" action="{{ route('register') }}">
    @csrf
    <div class="mb-3">
        <label for="name" class="form-label">Nombre completo</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" class="form-control @error('name') is-invalid @enderror"
                   id="name" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
        </div>
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">Correo electrónico</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control @error('email') is-invalid @enderror"
                   id="email" name="email" value="{{ old('email') }}" required autocomplete="username">
        </div>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Contraseña</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control @error('password') is-invalid @enderror"
                   id="password" name="password" required autocomplete="new-password">
        </div>
        <div class="form-text">Mínimo 8 caracteres.</div>
    </div>
    <div class="mb-4">
        <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
            <input type="password" class="form-control" id="password_confirmation"
                   name="password_confirmation" required autocomplete="new-password">
        </div>
    </div>
    <button type="submit" class="btn btn-dark w-100">
        <i class="bi bi-person-plus"></i> Crear cuenta
    </button>
</form>

<hr class="my-4">
<p class="text-center text-muted mb-2">¿Ya tienes una cuenta?</p>
<a href="{{ route('login') }}" class="btn btn-outline-dark w-100">
    <i class="bi bi-box-arrow-in-right"></i> Iniciar sesión
</a>
@endsection

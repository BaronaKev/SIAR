@extends('layouts.autenticacion')

@section('titulo', 'Iniciar Sesión')

@section('content')
<form method="POST" action="{{ route('login') }}">
    @csrf
    <div class="mb-3">
        <label for="email" class="form-label">Correo electrónico</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control @error('email') is-invalid @enderror"
                   id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        </div>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Contraseña</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control @error('password') is-invalid @enderror"
                   id="password" name="password" required autocomplete="current-password">
        </div>
    </div>
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label" for="remember">Recordarme</label>
        </div>
        @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="small text-decoration-none">¿Olvidaste tu contraseña?</a>
        @endif
    </div>
    <button type="submit" class="btn btn-dark w-100">
        <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
    </button>
</form>

@if (Route::has('register'))
    <hr class="my-4">
    <p class="text-center text-muted mb-2">¿No tienes una cuenta?</p>
    <a href="{{ route('register') }}" class="btn btn-outline-dark w-100">
        <i class="bi bi-person-plus"></i> Registrarse
    </a>
@endif
@endsection

@extends('auth.layout')

@section('title', 'Log in to your account')
@section('description', 'Enter your email and password below to log in')

@section('content')
<form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
               placeholder="email@example.com" value="{{ old('email') }}" required autofocus>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <label for="password" class="form-label mb-0">Password</label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-decoration-none" style="font-size: 0.875rem;">
                    Forgot password?
                </a>
            @endif
        </div>
        <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror"
               placeholder="Password" required>
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3 form-check">
        <input type="checkbox" name="remember" id="remember" class="form-check-input">
        <label class="form-check-label" for="remember">Remember me</label>
    </div>

    <button type="submit" class="btn btn-primary w-100 mb-3">Log in</button>

    <div class="text-center text-muted" style="font-size: 0.875rem;">
        Don't have an account? <a href="{{ route('register') }}" class="text-decoration-none">Sign up</a>
    </div>
</form>
@endsection

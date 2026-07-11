@extends('auth.layout')

@section('title', 'Create an account')
@section('description', 'Enter your details below to create your account')

@section('content')
<form method="POST" action="{{ route('register') }}">
    @csrf

    <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               placeholder="Full name" value="{{ old('name') }}" required autofocus>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               placeholder="email@example.com" value="{{ old('email') }}" required>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror"
               placeholder="Password" required>
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password_confirmation" class="form-label">Confirm Password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" 
               class="form-control @error('password_confirmation') is-invalid @enderror"
               placeholder="Confirm password" required>
        @error('password_confirmation')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary w-100 mb-3">Create account</button>

    <div class="text-center text-muted" style="font-size: 0.875rem;">
        Already have an account? <a href="{{ route('login') }}" class="text-decoration-none">Log in</a>
    </div>
</form>
@endsection

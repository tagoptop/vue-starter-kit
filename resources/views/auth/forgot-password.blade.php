@extends('auth.layout')

@section('title', 'Forgot password')
@section('description', 'Enter your email to receive a password reset link')

@section('content')
<form method="POST" action="{{ route('password.email') }}">
    @csrf

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               placeholder="email@example.com" value="{{ old('email') }}" required autofocus>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary w-100 mb-3">Send Password Reset Link</button>

    <div class="text-center text-muted" style="font-size: 0.875rem;">
        Remember your password? <a href="{{ route('login') }}" class="text-decoration-none">Log in</a>
    </div>
</form>
@endsection

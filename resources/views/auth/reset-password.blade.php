@extends('auth.layout')

@section('title', 'Reset password')
@section('description', 'Please enter your new password below')

@section('content')
<form method="POST" action="{{ route('password.store') }}">
    @csrf

    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ $request->email }}" readonly>
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

    <button type="submit" class="btn btn-primary w-100 mb-3">Reset Password</button>
</form>
@endsection

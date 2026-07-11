@extends('auth.layout')

@section('title', 'Confirm your password')
@section('description', 'This is a secure area of the application. Please confirm your password before continuing.')

@section('content')
<form method="POST" action="{{ route('password.confirm') }}">
    @csrf

    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror"
               placeholder="Password" required autofocus>
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary w-100">Confirm</button>
</form>
@endsection

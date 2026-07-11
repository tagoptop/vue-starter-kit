@extends('auth.layout')

@section('title', 'Verify email')
@section('description', 'Please verify your email address by clicking on the link we just emailed to you.')

@section('content')
@if (session('status') === 'verification-link-sent')
    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
        A new verification link has been sent to the email address you provided during registration.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form method="POST" action="{{ route('verification.send') }}" class="text-center">
    @csrf
    <button type="submit" class="btn btn-primary w-100 mb-3">Resend verification email</button>
</form>

<form method="POST" action="{{ route('logout') }}" class="text-center mt-3">
    @csrf
    <button type="submit" class="btn btn-link text-decoration-none">Log out</button>
</form>
@endsection

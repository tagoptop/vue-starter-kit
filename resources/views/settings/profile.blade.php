@extends('settings.layout')

@section('settings-content')
<div class="card">
    <div class="card-header">
        <h4 class="mb-0">Profile Information</h4>
    </div>
    <div class="card-body">
        @if ($status = session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ $status }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form method="post" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')

            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input id="name" class="form-control @error('name') is-invalid @enderror" type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required autofocus autocomplete="name" />
                @error('name')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email" class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required autocomplete="username" />
                @error('email')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                    <div class="mt-2">
                        <p class="text-muted">
                            Your email address is unverified.
                        </p>

                        <button form="send-verification" class="btn btn-sm btn-primary">
                            Click here to re-send the verification email.
                        </button>

                        @if ($status === 'verification-link-sent')
                            <p class="mt-2 fw-medium text-sm text-success">
                                A new verification link has been sent to your email address.
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <div class="d-flex align-items-center gap-4">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h4 class="mb-0">Delete Account</h4>
    </div>
    <div class="card-body">
        <p class="text-muted">Once your account is deleted, there is no going back. Please be certain.</p>
        
        <form method="post" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Are you sure you want to delete your account? This cannot be undone.');">
            @csrf
            @method('DELETE')
            
            <button type="submit" class="btn btn-danger">Delete Account</button>
        </form>
    </div>
</div>
@endsection

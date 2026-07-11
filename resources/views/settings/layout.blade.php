@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Settings</h2>
    <p class="text-muted mb-4">Manage your profile and account settings</p>

    <div class="row">
        <!-- Sidebar Navigation -->
        <div class="col-md-3 mb-4">
            <div class="list-group">
                <a href="{{ route('settings.profile') }}" class="list-group-item list-group-item-action {{ request()->routeIs('settings.profile') ? 'active' : '' }}">
                    Profile
                </a>
                <a href="{{ route('settings.password') }}" class="list-group-item list-group-item-action {{ request()->routeIs('settings.password') ? 'active' : '' }}">
                    Password
                </a>
                <a href="{{ route('settings.appearance') }}" class="list-group-item list-group-item-action {{ request()->routeIs('settings.appearance') ? 'active' : '' }}">
                    Appearance
                </a>
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('settings.branding') }}" class="list-group-item list-group-item-action {{ request()->routeIs('settings.branding') ? 'active' : '' }}">
                        Company Branding
                    </a>
                @endif
            </div>
        </div>

        <!-- Content -->
        <div class="col-md-9">
            @yield('settings-content')
        </div>
    </div>
</div>
@endsection

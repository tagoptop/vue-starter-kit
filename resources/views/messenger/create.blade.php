@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Start New Conversation</h4>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Validation Error:</strong>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="post" action="{{ route('conversations.store') }}">
                        @csrf

                        @if($isCustomer)
                            @if($defaultSupportContact)
                                <input type="hidden" name="participant_id" value="{{ $defaultSupportContact->id }}">
                                <div class="mb-3 border rounded p-3 bg-light-subtle">
                                    <div class="fw-semibold">Default Support Contact</div>
                                    <div>{{ $defaultSupportContact->name }}</div>
                                    <div class="small text-muted">{{ $defaultSupportContact->email }} · {{ ucfirst($defaultSupportContact->role) }}</div>
                                </div>
                            @else
                                <div class="alert alert-warning mb-3">No support contact is available right now.</div>
                            @endif
                        @else
                            <div class="mb-3">
                                <label for="participant_id" class="form-label">Select Customer</label>
                                <select id="participant_id" name="participant_id" class="form-select @error('participant_id') is-invalid @enderror" required>
                                    <option value="">-- Choose a customer --</option>
                                    @foreach($participants as $participant)
                                        <option value="{{ $participant->id }}" {{ old('participant_id') == $participant->id ? 'selected' : '' }}>
                                            {{ $participant->name }} ({{ $participant->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('participant_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject (Optional)</label>
                            <input id="subject" type="text" name="subject" class="form-control @error('subject') is-invalid @enderror" placeholder="e.g., Project Update" value="{{ old('subject') }}" />
                            @error('subject')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" @disabled($isCustomer && ! $defaultSupportContact)>{{ $isCustomer ? 'Contact Support' : 'Start Conversation' }}</button>
                            <a href="{{ route('conversations.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

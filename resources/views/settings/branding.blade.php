@extends('settings.layout')

@section('settings-content')
<div class="card">
    <div class="card-header">
        <h4 class="mb-0">Company Branding</h4>
    </div>
    <div class="card-body">
        @if ($status = session('status'))
            @php
                $statusType = session('status_type', 'saved');
                $isReset = $statusType === 'reset';
            @endphp
            <div class="alert {{ $isReset ? 'alert-warning' : 'alert-success' }} alert-dismissible fade show" role="alert">
                <strong>{{ $isReset ? 'Reset:' : 'Saved:' }}</strong> {{ $status }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

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

        <form id="branding-update-form" method="post" action="{{ route('branding.update') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <label for="company_name" class="form-label">Company Name</label>
                <input id="company_name" type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name', $companyName) }}" required />
                @error('company_name')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label for="logo" class="form-label">Company Logo</label>
                
                @if($logoUrl && $logoUrl !== '/logo.svg')
                    <div class="mb-3">
                        <p class="text-muted mb-2">Current Logo:</p>
                        <img src="{{ $logoUrl }}" alt="Company Logo" style="max-width: 200px; max-height: 100px; border: 1px solid #dee2e6; padding: 5px;">
                    </div>
                @endif

                <input id="logo" type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/svg+xml,image/png,image/jpeg,image/gif,image/webp" />
                <small class="text-muted d-block mt-2">Supported formats: SVG, PNG, JPG, GIF, WebP (max 2MB)</small>
                @error('logo')
                    <span class="invalid-feedback d-block">{{ $message }}</span>
                @enderror

                <div id="preview-container" style="display: none; margin-top: 1rem;">
                    <p class="text-muted mb-2">Preview:</p>
                    <img id="logo-preview" src="" alt="Preview" style="max-width: 200px; max-height: 100px; border: 1px solid #dee2e6; padding: 5px;">
                </div>
            </div>

        </form>

        <div class="d-flex gap-2 mt-3">
            <button type="submit" form="branding-update-form" class="btn btn-primary">Save Changes</button>

            <form method="post" action="{{ route('branding.reset') }}" style="display: inline;" onsubmit="return confirm('Reset company branding to defaults?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-secondary">Reset to Defaults</button>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('logo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('logo-preview').src = event.target.result;
            document.getElementById('preview-container').style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        document.getElementById('preview-container').style.display = 'none';
    }
});
</script>
@endsection

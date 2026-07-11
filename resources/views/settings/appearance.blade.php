@extends('settings.layout')

@section('settings-content')
<div class="card">
    <div class="card-header">
        <h4 class="mb-0">Appearance Settings</h4>
    </div>
    <div class="card-body">
        <p class="text-muted">Customize your interface preferences</p>
        
        <form>
            <div class="mb-3">
                <label class="form-label">Theme</label>
                <div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="theme" id="theme-light" value="light" checked>
                        <label class="form-check-label" for="theme-light">
                            Light Theme
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="theme" id="theme-dark" value="dark">
                        <label class="form-check-label" for="theme-dark">
                            Dark Theme
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="theme" id="theme-auto" value="auto">
                        <label class="form-check-label" for="theme-auto">
                            Auto (System Preference)
                        </label>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Language</label>
                <select class="form-select">
                    <option selected>English</option>
                    <option>Spanish</option>
                    <option>French</option>
                    <option>German</option>
                </select>
            </div>

            <div class="d-flex align-items-center gap-4">
                <button type="submit" class="btn btn-primary">Save Preferences</button>
            </div>
        </form>
    </div>
</div>
@endsection

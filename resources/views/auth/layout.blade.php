<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Auth') - Construction Supply Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        .auth-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
        }
        .auth-header {
            padding: 2rem;
            text-align: center;
            border-bottom: 1px solid #e9ecef;
        }
        .auth-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #212529;
            margin-bottom: 0.5rem;
        }
        .auth-header p {
            color: #6c757d;
            margin: 0;
        }
        .auth-body {
            padding: 2rem;
        }
        .form-label {
            font-weight: 500;
            color: #212529;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn {
            font-weight: 500;
            padding: 0.6rem 1.2rem;
        }
        /* Mobile responsiveness */
        @media (max-width: 576px) {
            body {
                padding: 1rem;
            }
            .auth-container {
                max-width: 100%;
                border-radius: 8px;
            }
            .auth-header {
                padding: 1.5rem;
            }
            .auth-header h1 {
                font-size: 1.25rem;
            }
            .auth-header p {
                font-size: 0.875rem;
            }
            .auth-body {
                padding: 1.5rem;
            }
            .form-label {
                font-size: 0.9rem;
            }
            .form-control {
                font-size: 1rem;
                padding: 0.5rem 0.75rem;
            }
            .btn {
                font-size: 0.9rem;
                padding: 0.5rem 1rem;
            }
            .text-center {
                font-size: 0.8rem;
            }
        }
        @media (max-width: 375px) {
            .auth-header {
                padding: 1rem;
            }
            .auth-body {
                padding: 1rem;
            }
            .auth-header h1 {
                font-size: 1.1rem;
            }
            .mb-3 {
                margin-bottom: 0.75rem !important;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h1>@yield('title')</h1>
            <p>@yield('description')</p>
        </div>
        <div class="auth-body">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Validation Error:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($status = session('status'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    {{ $status }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

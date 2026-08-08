<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <title>{{ config('app.name', 'Digital Inspection') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <script src="{{ asset('assets/js/layout.js') }}"></script>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/custom.min.css') }}" rel="stylesheet" type="text/css" />
</head>
<body>
    <div class="auth-page-wrapper pt-5">
        <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
            <div class="bg-overlay"></div>
            <div class="shape">
                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1440 120">
                    <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
                </svg>
            </div>
        </div>

        <div class="auth-page-content">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="text-center mt-sm-5 mb-4 text-white-50">
                            <img src="{{ asset('assets/images/logo-light.png') }}" alt="" height="24" class="mb-3">
                            <h4 class="text-white">Digital Inspection System</h4>
                            <p class="text-white-50">Streamline your inspection workflows</p>
                        </div>
                        <div class="card mt-4">
                            <div class="card-body p-4 text-center">
                                <div class="d-grid gap-3">
                                    @if (Route::has('login'))
                                        @auth
                                            <a href="{{ url('/dashboard') }}" class="btn btn-primary btn-lg">
                                                <i class="ri-dashboard-2-line me-2"></i> Go to Dashboard
                                            </a>
                                        @else
                                            <a href="{{ route('login') }}" class="btn btn-success btn-lg">
                                                <i class="ri-login-box-line me-2"></i> Sign In
                                            </a>
                                            @if (Route::has('register'))
                                                <a href="{{ route('register') }}" class="btn btn-outline-primary btn-lg">
                                                    <i class="ri-user-add-line me-2"></i> Create Account
                                                </a>
                                            @endif
                                        @endauth
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 text-center">
                        <p class="mb-0 text-muted">&copy; {{ date('Y') }} {{ config('app.name', 'Digital Inspection') }}.</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>

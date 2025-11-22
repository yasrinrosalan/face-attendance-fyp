@extends('layouts.app')

@section('content')
    <div class="container-fluid p-0" style="min-height: calc(100vh - 80px);">
        <div class="row g-0">

            <div class="col-lg-7 d-none d-lg-block position-relative"
                style="min-height: calc(100vh - 80px); background-color: var(--bs-primary);">
                <div class="position-absolute top-0 start-0 w-100 h-100"
                    style="background: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-secondary) 100%); opacity: 0.9;">
                </div>

                <div class="position-absolute top-50 start-50 translate-middle text-center text-white w-75">
                    <div class="mb-4">
                        <i class="fas fa-user-shield fa-4x mb-3"></i>
                    </div>
                    <h1 class="display-5 fw-bold mb-3">UMPSA Smart Attendance</h1>
                    <p class="lead fs-5 mb-4 text-white-50">
                        Secure, fast, and automated face recognition.
                    </p>

                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <span class="badge bg-white text-primary rounded-pill px-3 py-2">
                            <i class="fas fa-shield-alt me-2"></i>Secure
                        </span>
                        <span class="badge bg-white text-primary rounded-pill px-3 py-2">
                            <i class="fas fa-bolt me-2"></i>Fast
                        </span>
                        <span class="badge bg-white text-primary rounded-pill px-3 py-2">
                            <i class="fas fa-magic me-2"></i>AI-Powered
                        </span>
                    </div>
                </div>

                <div class="position-absolute bottom-0 start-0 w-100 p-4 text-white-50 small text-center">
                    &copy; {{ date('Y') }} Universiti Malaysia Pahang Al-Sultan Abdullah
                </div>
            </div>

            <div class="col-12 col-lg-5 d-flex flex-column bg-white">

                <div class="d-lg-none bg-primary text-white p-4 text-center mb-4"
                    style="background: linear-gradient(135deg, var(--bs-primary), var(--bs-secondary));">
                    <i class="fas fa-user-shield fa-2x mb-2"></i>
                    <h3 class="fw-bold h5 mb-0">Smart Attendance</h3>
                </div>

                <div class="flex-grow-1 d-flex align-items-center justify-content-center">
                    <div class="w-100 px-4 px-md-5 py-4" style="max-width: 500px;">

                        <div class="mb-4">
                            <h2 class="fw-bold text-dark">Login</h2>
                            <p class="text-muted">Please enter your details to sign in.</p>
                        </div>

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label small fw-bold text-secondary">EMAIL ADDRESS</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i
                                            class="fas fa-envelope"></i></span>
                                    <input id="email" type="email"
                                        class="form-control bg-light border-start-0 ps-0 py-2 @error('email') is-invalid @enderror"
                                        name="email" value="{{ old('email') }}" placeholder="name@example.com" required
                                        autofocus>
                                </div>
                                @error('email')
                                    <span class="invalid-feedback d-block"
                                        role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="password"
                                        class="form-label small fw-bold text-secondary mb-0">PASSWORD</label>
                                    @if (Route::has('password.request'))
                                        <a class="text-decoration-none small" href="{{ route('password.request') }}">Forgot
                                            password?</a>
                                    @endif
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i
                                            class="fas fa-lock"></i></span>
                                    <input id="password" type="password"
                                        class="form-control bg-light border-start-0 ps-0 py-2 @error('password') is-invalid @enderror"
                                        name="password" placeholder="Enter your password" required
                                        autocomplete="current-password">
                                </div>
                                @error('password')
                                    <span class="invalid-feedback d-block"
                                        role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm mb-3">
                                Sign In
                            </button>

                            <div class="text-center text-muted small">
                                Don't have an account? <a href="{{ route('register') }}"
                                    class="fw-bold text-decoration-none">Register Student</a>
                            </div>
                        </form>

                        <div class="mt-5 pt-4 border-top">
                            <button class="btn btn-sm btn-outline-light text-secondary w-100" type="button"
                                data-bs-toggle="collapse" data-bs-target="#demoCreds">
                                <i class="fas fa-key me-2"></i> Show Demo Credentials
                            </button>

                            <div class="collapse mt-2" id="demoCreds">
                                <div class="card card-body bg-light border-0 small">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="fw-bold text-primary">Admin:</span>
                                        <span class="font-monospace text-muted">admin@demo.com / password</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="fw-bold text-primary">Lecturer:</span>
                                        <span class="font-monospace text-muted">lecturer@demo.com / password</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold text-primary">Student:</span>
                                        <span class="font-monospace text-muted">student@demo.com / password</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Fix for container padding override */
        main.container {
            padding: 0 !important;
            max-width: 100% !important;
        }

        /* Custom Input Styling */
        .input-group-text {
            border-color: #dee2e6;
        }

        .form-control,
        .input-group-text {
            background-color: #f8f9fa;
        }

        .form-control:focus {
            background-color: #fff;
            border-color: var(--bs-primary);
            box-shadow: none;
        }

        .form-control:focus+.input-group-text,
        .input-group-text:has(+ .form-control:focus) {
            border-color: var(--bs-primary);
            background-color: #fff;
            color: var(--bs-primary) !important;
        }
    </style>
@endsection

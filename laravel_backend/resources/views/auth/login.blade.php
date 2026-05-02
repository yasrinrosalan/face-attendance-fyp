@extends('layouts.app')

@section('content')
    <div class="container-fluid p-0" style="min-height: calc(100vh - 80px);">
        <div class="row g-0 h-100">

            <div class="col-lg-7 d-none d-lg-block position-relative"
                style="min-height: calc(100vh - 80px); background-color: var(--bs-primary);">
                <div class="position-absolute top-0 start-0 w-100 h-100"
                    style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #2980b9 100%); opacity: 0.95;">
                </div>

                <div class="position-absolute top-50 start-50 translate-middle text-center text-white w-75">
                    <div class="mb-4">
                        <i class="fas fa-user-shield fa-4x mb-3 shadow-sm rounded-circle p-4 bg-white bg-opacity-10"></i>
                    </div>
                    <h1 class="display-4 fw-bolder mb-3 tracking-tight">e-Hadir</h1>
                    <p class="lead fs-5 mb-5 text-white-50 fw-normal">
                        Secure, fast, and automated face recognition.
                    </p>

                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <span
                            class="badge bg-white bg-opacity-25 text-white rounded-pill px-4 py-2 fw-medium border border-light border-opacity-25 shadow-sm">
                            <i class="fas fa-shield-alt me-2 text-warning"></i>Secure
                        </span>
                        <span
                            class="badge bg-white bg-opacity-25 text-white rounded-pill px-4 py-2 fw-medium border border-light border-opacity-25 shadow-sm">
                            <i class="fas fa-bolt me-2 text-warning"></i>Fast
                        </span>
                        <span
                            class="badge bg-white bg-opacity-25 text-white rounded-pill px-4 py-2 fw-medium border border-light border-opacity-25 shadow-sm">
                            <i class="fas fa-magic me-2 text-warning"></i>AI-Powered
                        </span>
                    </div>
                </div>

                <div class="position-absolute bottom-0 start-0 w-100 p-4 text-white-50 small text-center fw-medium">
                    &copy; {{ date('Y') }} e-Hadir University System
                </div>
            </div>

            <div class="col-12 col-lg-5 d-flex flex-column bg-white" style="min-height: calc(100vh - 80px);">

                <div class="d-lg-none bg-primary text-white p-4 text-center mb-4 shadow-sm"
                    style="background: linear-gradient(135deg, var(--bs-primary), var(--bs-secondary));">
                    <i class="fas fa-user-shield fa-2x mb-2"></i>
                    <h3 class="fw-bold h5 mb-0">e-Hadir Smart Attendance</h3>
                </div>

                <div class="flex-grow-1 d-flex align-items-center justify-content-center">
                    <div class="w-100 px-4 px-md-5 py-4" style="max-width: 480px;">

                        <div class="mb-5">
                            <h2 class="fw-bold text-dark mb-2">Welcome Back</h2>
                            <p class="text-muted">Please enter your details to sign in.</p>
                        </div>

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <div class="mb-4">
                                <label for="email" class="form-label small fw-bold text-secondary mb-1 ls-1">EMAIL
                                    ADDRESS</label>
                                <div class="input-group input-group-lg shadow-sm-hover rounded-3 overflow-hidden border">
                                    <span class="input-group-text bg-light border-0 text-muted px-3">
                                        <i class="fas fa-envelope fs-6"></i>
                                    </span>
                                    <input id="email" type="email"
                                        class="form-control bg-light border-0 fs-6 py-3 @error('email') is-invalid @enderror"
                                        name="email" value="{{ old('email') }}" placeholder="name@example.com" required
                                        autofocus>
                                </div>
                                @error('email')
                                    <span class="text-danger small mt-1 d-block fw-medium" role="alert"><i
                                            class="fas fa-exclamation-circle me-1"></i>{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="password"
                                        class="form-label small fw-bold text-secondary mb-0 ls-1">PASSWORD</label>
                                    @if (Route::has('password.request'))
                                        <a class="text-decoration-none small fw-medium"
                                            href="{{ route('password.request') }}">Forgot password?</a>
                                    @endif
                                </div>
                                <div class="input-group input-group-lg shadow-sm-hover rounded-3 overflow-hidden border">
                                    <span class="input-group-text bg-light border-0 text-muted px-3">
                                        <i class="fas fa-lock fs-6"></i>
                                    </span>
                                    <input id="password" type="password"
                                        class="form-control bg-light border-0 fs-6 py-3 @error('password') is-invalid @enderror"
                                        name="password" placeholder="Enter your password" required
                                        autocomplete="current-password">
                                </div>
                                @error('password')
                                    <span class="text-danger small mt-1 d-block fw-medium" role="alert"><i
                                            class="fas fa-exclamation-circle me-1"></i>{{ $message }}</span>
                                @enderror
                            </div>

                            <button type="submit"
                                class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow-sm btn-hover-lift mt-2 mb-4">
                                Sign In <i class="fas fa-arrow-right ms-2"></i>
                            </button>

                            <div class="text-center text-muted small">
                                Don't have an account? <a href="{{ route('register') }}"
                                    class="fw-bold text-decoration-none ms-1">Register Student</a>
                            </div>
                        </form>

                        <div class="mt-5 pt-4 border-top">
                            <button class="btn btn-sm btn-light text-secondary w-100 fw-medium rounded-3 py-2"
                                type="button" data-bs-toggle="collapse" data-bs-target="#demoCreds">
                                <i class="fas fa-key me-2"></i> Show Demo Credentials
                            </button>

                            <div class="collapse mt-3" id="demoCreds">
                                <div class="card card-body bg-light border-0 rounded-3 small shadow-sm">
                                    <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                                        <span class="fw-bold text-primary"><i class="fas fa-user-shield me-1"></i>
                                            Admin:</span>
                                        <span class="font-monospace text-muted">admin@demo.com <span
                                                class="text-light-muted">/</span> password</span>
                                    </div>
                                    <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                                        <span class="fw-bold text-primary"><i class="fas fa-chalkboard-teacher me-1"></i>
                                            Lecturer:</span>
                                        <span class="font-monospace text-muted">lecturer@demo.com <span
                                                class="text-light-muted">/</span> password</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold text-primary"><i class="fas fa-user-graduate me-1"></i>
                                            Student:</span>
                                        <span class="font-monospace text-muted">student@demo.com <span
                                                class="text-light-muted">/</span> password</span>
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

        /* Spacing utilities */
        .ls-1 {
            letter-spacing: 0.5px;
        }

        .text-light-muted {
            color: #ced4da;
        }

        /* Custom Input Styling */
        .form-control:focus {
            background-color: #fff !important;
            box-shadow: none;
        }

        /* Focus glow effect on the container instead of the input */
        .input-group:focus-within {
            background-color: #fff;
            border-color: var(--bs-primary) !important;
            box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.15);
        }

        .input-group:focus-within .input-group-text,
        .input-group:focus-within .form-control {
            background-color: #fff !important;
            color: var(--bs-primary);
        }

        /* Button Hover Lift Effect */
        .btn-hover-lift {
            transition: all 0.2s ease-in-out;
        }

        .btn-hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }
    </style>
@endsection

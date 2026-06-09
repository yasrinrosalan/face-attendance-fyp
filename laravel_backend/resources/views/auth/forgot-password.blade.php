@extends('layouts.app') @section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow border-0">
                    <div class="card-body p-5">
                        <h3 class="fw-bold text-primary mb-3">Forgot Password?</h3>
                        <p class="text-muted small mb-4">Enter your registered email address and we will send you a secure
                            link to reset your password.</p>

                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf
                            <div class="mb-4">
                                <label for="email" class="form-label fw-bold small text-uppercase text-secondary">Email
                                    Address</label>
                                <input id="email" type="email"
                                    class="form-control form-control-lg bg-light border-0 @error('email') is-invalid @enderror"
                                    name="email" value="{{ old('email') }}" required autofocus>
                                @error('email')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg fw-bold">
                                    Send Password Reset Link
                                </button>
                                <a href="{{ route('login') }}" class="btn btn-link text-muted mt-2">Back to Login</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow border-0">
                    <div class="card-body p-5">
                        <h3 class="fw-bold text-primary mb-4">Create New Password</h3>

                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="mb-3">
                                <label for="email" class="form-label fw-bold small text-uppercase text-secondary">Email
                                    Address</label>
                                <input id="email" type="email"
                                    class="form-control bg-light border-0 @error('email') is-invalid @enderror"
                                    name="email" value="{{ $email ?? old('email') }}" required readonly>
                                @error('email')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-bold small text-uppercase text-secondary">New
                                    Password</label>
                                <input id="password" type="password"
                                    class="form-control bg-light border-0 @error('password') is-invalid @enderror"
                                    name="password" required autocomplete="new-password">
                                @error('password')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="password-confirm"
                                    class="form-label fw-bold small text-uppercase text-secondary">Confirm Password</label>
                                <input id="password-confirm" type="password" class="form-control bg-light border-0"
                                    name="password_confirmation" required autocomplete="new-password">
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg fw-bold">
                                    Reset Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

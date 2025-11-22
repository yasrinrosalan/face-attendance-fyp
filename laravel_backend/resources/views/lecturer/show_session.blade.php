@extends('layouts.app')

@section('content')
    <div class="container py-4">

        <div class="mb-4">
            <a href="{{ route('lecturer.dashboard') }}" class="text-decoration-none text-muted fw-medium">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom-0 text-center pt-5 pb-0">
                        <h6 class="text-uppercase text-secondary fw-bold letter-spacing-1 mb-2">
                            {{ $session->course->course_name }}</h6>
                        <h1 class="display-6 fw-bold text-dark mb-2">{{ $session->session_title }}</h1>

                        <div class="mt-3">
                            @if ($session->isActive())
                                <span
                                    class="badge bg-success-subtle text-success px-3 py-2 rounded-pill border border-success-subtle">
                                    <i class="fas fa-circle fa-xs me-2"></i>Active Session
                                </span>
                            @elseif(now()->gt($session->ends_at))
                                <span
                                    class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill border border-secondary-subtle">
                                    <i class="fas fa-stopwatch me-2"></i>Session Expired
                                </span>
                            @else
                                <span
                                    class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill border border-warning-subtle">
                                    <i class="fas fa-clock me-2"></i>Starting Soon
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="card-body text-center p-5">

                        <p class="text-muted mb-4">
                            Ask students to scan this QR code or enter the code manually.
                        </p>

                        <div class="d-inline-block p-3 bg-white rounded-4 shadow-sm border mb-4">
                            {!! QrCode::size(280)->generate($attendance_url) !!}
                        </div>

                        <div class="mb-4">
                            <div class="d-inline-block bg-light px-4 py-2 rounded-3 border">
                                <span class="text-muted small d-block text-uppercase fw-bold mb-1"
                                    style="font-size: 0.7rem; letter-spacing: 1px;">Attendance Code</span>
                                <span
                                    class="h1 font-monospace fw-bold text-primary mb-0 tracking-wide">{{ $session->referral_code }}</span>
                            </div>
                        </div>

                        <div class="text-muted small mb-4">
                            @if ($session->isActive())
                                Closes at <strong>{{ $session->ends_at->format('h:i A') }}</strong>
                            @else
                                Ended at <strong>{{ $session->ends_at->format('h:i A') }}</strong>
                            @endif
                        </div>

                        <hr class="my-4 opacity-10">

                        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                            <a href="{{ route('lecturer.attendance.export', $session->id) }}"
                                class="btn btn-primary px-4 py-2 fw-medium">
                                <i class="fas fa-download me-2"></i>Export CSV ({{ $session->attendance_records->count() }})
                            </a>

                            <form action="{{ route('lecturer.session.delete', $session->id) }}" method="POST"
                                onsubmit="return confirm('Are you sure you want to delete this session? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger px-4 py-2 fw-medium w-100">
                                    <i class="fas fa-trash-alt me-2"></i>Delete Session
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <style>
        .letter-spacing-1 {
            letter-spacing: 1px;
        }

        .tracking-wide {
            letter-spacing: 3px;
        }

        .bg-success-subtle {
            background-color: #d1e7dd;
        }

        .border-success-subtle {
            border-color: #a3cfbb;
        }

        .bg-secondary-subtle {
            background-color: #e2e3e5;
        }

        .border-secondary-subtle {
            border-color: #d3d6d8;
        }

        .bg-warning-subtle {
            background-color: #fff3cd;
        }

        .border-warning-subtle {
            border-color: #ffecb5;
        }
    </style>
@endsection

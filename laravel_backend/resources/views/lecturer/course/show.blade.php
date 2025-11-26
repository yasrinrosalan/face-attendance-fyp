@extends('layouts.app')

@section('content')
    <div class="container py-4 font-sans-serif">
        <div class="mb-4">
            <a href="{{ route('lecturer.dashboard') }}"
                class="text-decoration-none text-muted fw-medium align-items-center d-inline-flex">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>

        <div class="d-flex justify-content-between align-items-start mb-4 border-bottom pb-3">
            <div>
                <span class="badge bg-primary bg-opacity-10 text-primary mb-2">{{ $course->course_code }}</span>
                <h2 class="fw-bold text-dark mb-0">{{ $course->course_name }}</h2>
            </div>
            <div>
                <button type="button" class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal"
                    data-bs-target="#createSessionModal">
                    <i class="fas fa-calendar-plus me-2"></i> Create Session
                </button>
            </div>
        </div>

        <h5 class="fw-bold text-secondary mb-3">Course Session History</h5>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="list-group list-group-flush rounded-3">
                    @forelse($course->attendance_sessions as $session)
                        <div
                            class="list-group-item border-0 px-4 py-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between hover-bg-light">
                            <div class="mb-2 mb-md-0">
                                <div class="d-flex align-items-center mb-1">
                                    <strong class="text-dark fs-5">{{ $session->session_title }}</strong>
                                    <span
                                        class="badge bg-light text-secondary border ms-3 font-monospace">{{ $session->referral_code }}</span>
                                </div>
                                <div class="small text-muted">
                                    <i class="far fa-calendar-alt me-1"></i> {{ $session->starts_at->format('M d, Y') }}
                                    <span class="mx-2">•</span>
                                    <i class="far fa-clock me-1"></i> {{ $session->starts_at->format('h:i A') }} -
                                    {{ $session->ends_at->format('h:i A') }}
                                    <span class="mx-2">•</span>
                                    @if ($session->isActive())
                                        <span class="text-success fw-bold"><i class="fas fa-circle fa-xs me-1"></i> Active
                                            Now</span>
                                    @else
                                        <span class="text-secondary"><i class="fas fa-check-circle me-1"></i>
                                            Completed</span>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('lecturer.session.show', $session->id) }}"
                                    class="btn btn-sm btn-outline-primary fw-medium">
                                    Manage & Reports
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 opacity-50">
                            <i class="fas fa-calendar-times fa-3x mb-3"></i>
                            <p class="fw-medium mb-0">No sessions have been created for this course yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @include('lecturer.partials.create_session_modal')

    <style>
        .font-sans-serif {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .hover-bg-light:hover {
            background-color: #f8f9fa !important;
        }
    </style>
@endsection

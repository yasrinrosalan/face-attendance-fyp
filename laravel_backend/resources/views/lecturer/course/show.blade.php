@extends('layouts.app')

@section('content')
    <div class="container py-4 font-sans-serif">

        <div class="mb-4">
            <a href="{{ route('lecturer.dashboard') }}"
                class="text-decoration-none fw-bold text-primary align-items-center d-inline-flex link-hover">
                <i class="fas fa-arrow-left me-2 transition-all"></i> Back to Dashboard
            </a>
        </div>

        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-5 gap-4">
            <div>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span
                        class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 px-3 py-2 rounded-pill fw-bold"
                        style="letter-spacing: 0.5px;">
                        <i class="fas fa-tag me-1"></i> {{ $course->course_code }}
                    </span>
                    <span
                        class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-10 px-3 py-2 rounded-pill fw-bold"
                        style="letter-spacing: 0.5px;">
                        <i class="fas fa-clock me-1"></i> Semester {{ $course->semester }} ({{ $course->academic_year }})
                    </span>
                </div>
                <h1 class="fw-bolder text-dark mb-0 tracking-tight display-6">{{ $course->course_name }}</h1>
            </div>

            <div class="d-flex gap-2 flex-wrap flex-md-nowrap">
                <a href="{{ route('lecturer.course.export_csv', $course->id) }}"
                    class="btn btn-success bg-gradient px-4 py-2 rounded-pill fw-bold shadow-sm btn-hover-lift d-flex align-items-center justify-content-center">
                    <i class="fas fa-file-csv me-2 fs-5"></i> Export 14-Week Report
                </a>

                <button type="button"
                    class="btn btn-primary px-4 py-2 rounded-pill fw-bold shadow-sm btn-hover-lift d-flex align-items-center justify-content-center"
                    data-bs-toggle="modal" data-bs-target="#createSessionModal"
                    style="background: linear-gradient(135deg, var(--bs-primary) 0%, #2a5298 100%); border: none;">
                    <i class="fas fa-calendar-plus me-2 fs-5"></i> Create Session
                </button>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3 mt-5">
            <h5 class="fw-bold text-dark m-0"><i class="fas fa-history me-2 text-primary opacity-75"></i>Session History
            </h5>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($course->attendance_sessions as $session)
                        <div class="list-group-item border-bottom border-light p-4 transition-all hover-bg-light">
                            <div class="row align-items-center g-3">

                                <div class="col-md-8 col-lg-9">
                                    <h5 class="fw-bold text-dark mb-2">{{ $session->session_title }}</h5>

                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        <span
                                            class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2 py-1">
                                            Week {{ $session->week_number }}
                                        </span>

                                        @if ($session->mode === 'online')
                                            <span
                                                class="badge bg-dark bg-opacity-10 text-dark border border-dark border-opacity-10 rounded-pill px-2 py-1"
                                                title="Online Class">
                                                <i class="fas fa-laptop-house"></i> Online
                                            </span>
                                        @else
                                            <span
                                                class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 rounded-pill px-2 py-1"
                                                title="Physical Class">
                                                <i class="fas fa-building"></i> Physical
                                            </span>
                                        @endif

                                        <span class="badge bg-light text-muted border px-2 py-1 font-monospace"
                                            title="Class Code">
                                            <i class="fas fa-key me-1 opacity-50"></i>{{ $session->referral_code }}
                                        </span>
                                    </div>

                                    <div class="d-flex flex-wrap align-items-center text-muted small fw-medium gap-3">
                                        <div class="d-flex align-items-center">
                                            <i class="far fa-calendar-alt me-2 text-primary opacity-75"></i>
                                            {{ $session->starts_at->format('M d, Y') }}
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <i class="far fa-clock me-2 text-primary opacity-75"></i>
                                            {{ $session->starts_at->format('h:i A') }} -
                                            {{ $session->ends_at->format('h:i A') }}
                                        </div>
                                        <div class="d-flex align-items-center">
                                            @if ($session->isActive())
                                                <span
                                                    class="text-success fw-bold bg-success bg-opacity-10 px-2 py-1 rounded-1 d-inline-flex align-items-center">
                                                    <span class="spinner-grow spinner-grow-sm text-success me-2"
                                                        role="status" style="width: 0.5rem; height: 0.5rem;"></span> Active
                                                    Now
                                                </span>
                                            @else
                                                <span class="text-secondary d-inline-flex align-items-center">
                                                    <i class="fas fa-check-circle me-1 opacity-50"></i> Completed
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 col-lg-3 text-md-end">
                                    <a href="{{ route('lecturer.session.show', $session->id) }}"
                                        class="btn btn-outline-primary fw-bold px-4 py-2 rounded-pill btn-hover-lift d-block d-md-inline-block w-100 w-md-auto">
                                        Manage & Reports <i class="fas fa-chevron-right ms-1 small"></i>
                                    </a>
                                </div>

                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 bg-white">
                            <div class="bg-light rounded-circle d-inline-flex p-4 mb-3">
                                <i class="fas fa-calendar-times fa-3x text-primary opacity-50"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">No Sessions Yet</h5>
                            <p class="text-muted small mb-4">You haven't created any attendance sessions for this course.
                            </p>
                            <button type="button"
                                class="btn btn-primary px-4 py-2 rounded-pill fw-bold shadow-sm btn-hover-lift"
                                data-bs-toggle="modal" data-bs-target="#createSessionModal">
                                <i class="fas fa-plus me-2"></i> Create First Session
                            </button>
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

        .tracking-tight {
            letter-spacing: -0.5px;
        }

        .transition-all {
            transition: all 0.2s ease-in-out;
        }

        .hover-bg-light:hover {
            background-color: #f8fafc !important;
        }

        /* Button Hover Lift */
        .btn-hover-lift {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        /* Link Hover Animation */
        .link-hover:hover i {
            transform: translateX(-4px);
        }

        /* Custom Responsive Width Fix */
        @media (min-width: 768px) {
            .w-md-auto {
                width: auto !important;
            }
        }
    </style>
@endsection

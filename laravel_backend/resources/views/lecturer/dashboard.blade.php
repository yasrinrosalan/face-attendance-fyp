@extends('layouts.app')

@section('content')
    <div class="container py-4 font-sans-serif">

        <div class="row mb-5 align-items-center">
            <div class="col-md-8 mb-3 mb-md-0">
                <h6 class="text-uppercase text-muted small fw-bold ls-1 mb-1">Lecturer Dashboard</h6>
                <h2 class="fw-bold text-dark mb-1 tracking-tight">{{ Auth::user()->name }}</h2>
                <p class="text-muted mb-0 fw-medium">Manage your courses and attendance sessions.</p>
            </div>
            <div class="col-md-4 text-md-end">
                <button type="button"
                    class="btn btn-primary btn-lg rounded-pill px-4 py-3 fw-bold shadow-sm w-100 w-md-auto btn-hover-lift"
                    data-bs-toggle="modal" data-bs-target="#createSessionModal"
                    style="background: linear-gradient(135deg, var(--bs-primary) 0%, #2a5298 100%); border: none;">
                    <i class="fas fa-plus-circle me-2"></i> Create New Session
                </button>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center p-3 mb-4"
                role="alert">
                <i class="fas fa-check-circle fs-5 me-3"></i>
                <div class="fw-medium">{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center p-3 mb-4"
                role="alert">
                <i class="fas fa-exclamation-circle fs-5 me-3"></i>
                <div class="fw-medium">{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-dark m-0"><i class="fas fa-layer-group me-2 text-primary opacity-75"></i>Your
                        Courses</h5>
                    <button
                        class="btn btn-sm btn-light text-primary border shadow-sm fw-bold rounded-pill px-3 py-2 btn-hover-lift"
                        data-bs-toggle="modal" data-bs-target="#addCourseModal">
                        <i class="fas fa-folder-plus me-1"></i> Add Course
                    </button>
                </div>

                @forelse($courses as $courseItem)
                    <div class="card border-0 shadow-sm mb-3 rounded-4 card-hover transition-all">
                        <div class="card-body p-4">
                            <div class="row align-items-center g-3">
                                <div class="col-md-9">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 border border-primary border-opacity-10 rounded-3 p-3 me-3 text-center shadow-sm"
                                            style="min-width: 80px;">
                                            <span class="d-block fw-bold text-primary small"
                                                style="letter-spacing: 0.5px;">{{ $courseItem->course_code }}</span>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold text-dark mb-1">{{ $courseItem->course_name }}</h5>
                                            <span class="text-muted small fw-medium">
                                                <i class="far fa-calendar-alt me-1"></i>
                                                {{ $courseItem->attendance_sessions_count }} Sessions Created
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 text-md-end">
                                    <a href="{{ route('lecturer.course.show', $courseItem->id) }}"
                                        class="btn btn-outline-primary fw-bold px-4 py-2 rounded-pill w-100 w-md-auto btn-hover-lift">
                                        Manage <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 bg-white rounded-4 shadow-sm border border-light">
                        <div class="bg-light rounded-circle d-inline-flex p-4 mb-3">
                            <i class="fas fa-folder-open fa-3x text-primary opacity-50"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-2">No Courses Found</h4>
                        <p class="text-muted mb-4 fw-medium">Get started by adding your first course to the system.</p>
                        <button type="button"
                            class="btn btn-primary px-4 py-2 rounded-pill fw-bold shadow-sm btn-hover-lift"
                            data-bs-toggle="modal" data-bs-target="#addCourseModal">
                            <i class="fas fa-plus me-2"></i> Add New Course
                        </button>
                    </div>
                @endforelse
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4 text-white overflow-hidden position-relative rounded-4 card-hover"
                    style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                    <div class="position-absolute top-0 end-0 bg-white opacity-10 rounded-circle"
                        style="width: 150px; height: 150px; margin-right: -40px; margin-top: -40px;"></div>
                    <div class="card-body p-4 p-md-5 position-relative">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="fw-bold opacity-75 mb-0 text-uppercase ls-1" style="font-size: 0.75rem;">Total
                                Sessions</h6>
                            <i class="fas fa-chart-line opacity-50 fs-4"></i>
                        </div>
                        <div class="d-flex align-items-baseline">
                            <h1 class="display-2 fw-bolder mb-0 lh-1 tracking-tight">
                                {{ $courses->sum('attendance_sessions_count') }}</h1>
                            <span
                                class="ms-3 text-white opacity-75 fw-medium bg-white bg-opacity-25 px-2 py-1 rounded small">Overall</span>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-bottom border-light py-3 px-4 rounded-top-4">
                        <h6 class="fw-bold text-dark mb-0">Quick Tools</h6>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="{{ route('lecturer.analytics') }}"
                            class="list-group-item list-group-item-action p-4 border-bottom border-light d-flex align-items-center transition-all">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3 shadow-sm">
                                <i class="fas fa-chart-pie fa-lg"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">Analytics</div>
                                <div class="small text-muted fw-medium">View detailed class reports</div>
                            </div>
                            <i class="fas fa-chevron-right ms-auto text-muted small opacity-50"></i>
                        </a>

                        <a href="{{ route('report.create') }}"
                            class="list-group-item list-group-item-action p-4 border-0 d-flex align-items-center transition-all rounded-bottom-4">
                            <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-3 me-3 shadow-sm">
                                <i class="fas fa-headset fa-lg"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">Support</div>
                                <div class="small text-muted fw-medium">Report a system issue</div>
                            </div>
                            <i class="fas fa-chevron-right ms-auto text-muted small opacity-50"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('lecturer.partials.add_course_modal')
    @include('lecturer.partials.create_session_modal')

    <style>
        .font-sans-serif {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .tracking-tight {
            letter-spacing: -0.5px;
        }

        .ls-1 {
            letter-spacing: 0.5px;
        }

        .transition-all {
            transition: all 0.2s ease-in-out;
        }

        /* Card Hover Effects */
        .card-hover {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-hover:hover {
            transform: translateY(-3px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .08) !important;
        }

        /* Button Hover Lift */
        .btn-hover-lift {
            transition: all 0.2s ease-in-out;
        }

        .btn-hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }
    </style>
@endsection

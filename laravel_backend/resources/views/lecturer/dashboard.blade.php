@extends('layouts.app')

@section('content')
    <div class="container py-4 font-sans-serif">

        <div class="row mb-5 align-items-center">
            <div class="col-md-8">
                <h2 class="fw-bold text-dark mb-1">Hello, {{ Auth::user()->name }}</h2>
                <p class="text-muted mb-0">Here is the performance overview for your courses.</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <button type="button" class="btn btn-primary px-4 py-2 fw-bold shadow-sm" data-bs-toggle="modal"
                    data-bs-target="#createSessionModal">
                    <i class="fas fa-plus me-2"></i> Create New Session
                </button>
            </div>
        </div>

        <div class="row g-4">

            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-secondary m-0"><i class="fas fa-layer-group me-2"></i>Your Courses</h5>
                    <button class="btn btn-sm btn-light text-primary fw-bold" data-bs-toggle="modal"
                        data-bs-target="#addCourseModal">
                        <i class="fas fa-folder-plus me-1"></i> Add Course
                    </button>
                </div>

                @forelse($courses as $courseItem)
                    <div class="card border-0 shadow-sm mb-4 overflow-hidden hover-lift">
                        <div
                            class="card-header bg-white border-bottom-0 pt-4 px-4 d-flex justify-content-between align-items-start">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span
                                        class="badge bg-primary bg-opacity-10 text-primary">{{ $courseItem->course_code }}</span>
                                    <span
                                        class="badge bg-light text-secondary border">{{ $courseItem->attendance_sessions->count() }}
                                        Sessions</span>
                                </div>
                                <h4 class="fw-bold text-dark mb-0">{{ $courseItem->course_name }}</h4>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm rounded-circle" type="button"
                                    data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v text-muted"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                                    <li><a class="dropdown-item"
                                            href="{{ route('lecturer.course.show', $courseItem->id) }}">View Details</a>
                                    </li>
                                    <li><a class="dropdown-item text-danger" href="#">Archive Course</a></li>
                                </ul>
                            </div>
                        </div>

                        <div class="card-body px-4 pb-4">
                            <div class="row g-3 mt-2">
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 border h-100">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="bg-white p-2 rounded shadow-sm me-3 text-success">
                                                <i class="fas fa-chart-line"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted text-uppercase fw-bold"
                                                    style="font-size: 0.7rem;">Avg. Attendance</small>
                                                <div class="h5 fw-bold mb-0">
                                                    {{-- Mock Calculation (Replace with real logic in controller later) --}}
                                                    {{ $courseItem->latest_session_stats ? $courseItem->latest_session_stats->attendance_rate : 0 }}%
                                                </div>
                                            </div>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-success"
                                                style="width: {{ $courseItem->latest_session_stats ? $courseItem->latest_session_stats->attendance_rate : 0 }}%">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 border h-100">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <small class="text-muted text-uppercase fw-bold"
                                                style="font-size: 0.7rem;">Recent Activity</small>
                                            @if ($courseItem->latest_session_stats)
                                                <span
                                                    class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                            @endif
                                        </div>

                                        @if ($courseItem->latest_session_stats)
                                            <div class="d-flex align-items-center mt-2">
                                                <div class="avatar-group me-2">
                                                    <div class="avatar avatar-xs bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center"
                                                        style="width:25px; height:25px; font-size:10px;">A</div>
                                                    <div class="avatar avatar-xs bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center"
                                                        style="width:25px; height:25px; font-size:10px;">B</div>
                                                    <div class="avatar avatar-xs bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center"
                                                        style="width:25px; height:25px; font-size:10px;">C</div>
                                                </div>
                                                <span class="small text-dark fw-medium">
                                                    +{{ $courseItem->latest_session_stats->present_count }} attended
                                                    <span class="text-muted fw-normal">last session</span>
                                                </span>
                                            </div>
                                        @else
                                            <div class="text-muted small fst-italic">No recent sessions recorded.</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-white border-top p-0">
                            <a href="{{ route('lecturer.course.show', $courseItem->id) }}"
                                class="btn btn-link text-decoration-none fw-bold w-100 py-3 text-primary d-flex justify-content-between align-items-center px-4">
                                <span>Manage Course & Sessions</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 bg-white rounded-3 shadow-sm border border-dashed">
                        <div class="mb-3">
                            <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle"
                                style="width: 80px; height: 80px;">
                                <i class="fas fa-folder-open fa-2x text-muted opacity-50"></i>
                            </div>
                        </div>
                        <h4 class="fw-bold text-dark">No Courses Found</h4>
                        <p class="text-muted mb-4">Get started by adding your first course to manage.</p>
                        <button type="button" class="btn btn-primary px-4 py-2 fw-bold shadow-sm" data-bs-toggle="modal"
                            data-bs-target="#addCourseModal">
                            <i class="fas fa-plus me-2"></i> Add New Course
                        </button>
                    </div>
                @endforelse
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4 bg-dark text-white overflow-hidden position-relative">
                    <div class="position-absolute top-0 end-0 bg-white opacity-10 rounded-circle"
                        style="width: 150px; height: 150px; margin-right: -40px; margin-top: -40px;"></div>

                    <div class="card-body p-4 position-relative">
                        <h6 class="fw-bold opacity-75 mb-4 text-uppercase letter-spacing-1" style="font-size: 0.75rem;">
                            Total Sessions Conducted</h6>
                        <div class="d-flex align-items-baseline">
                            <h1 class="display-3 fw-bold mb-0 lh-1">
                                {{ $courses->sum(fn($c) => $c->attendance_sessions->count()) }}</h1>
                            <span class="ms-2 text-success fw-bold"><i class="fas fa-arrow-up small me-1"></i>Total</span>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-dark mb-0">Quick Tools</h6>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="{{ route('lecturer.analytics') }}"
                            class="list-group-item list-group-item-action py-3 border-0 d-flex align-items-center">
                            <div class="bg-light text-primary rounded p-2 me-3">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">Analytics Dashboard</div>
                                <div class="small text-muted">Deep dive into student data</div>
                            </div>
                            <i class="fas fa-chevron-right ms-auto text-muted small"></i>
                        </a>
                        <button class="list-group-item list-group-item-action py-3 border-0 d-flex align-items-center"
                            data-bs-toggle="modal" data-bs-target="#addCourseModal">
                            <div class="bg-light text-success rounded p-2 me-3">
                                <i class="fas fa-book"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">Manage Courses</div>
                                <div class="small text-muted">Add or edit your subjects</div>
                            </div>
                            <i class="fas fa-chevron-right ms-auto text-muted small"></i>
                        </button>
                        <a href="{{ route('report.create') }}"
                            class="list-group-item list-group-item-action py-3 border-0 d-flex align-items-center">
                            <div class="bg-light text-warning rounded p-2 me-3">
                                <i class="fas fa-headset"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">Support Ticket</div>
                                <div class="small text-muted">Report an issue to admin</div>
                            </div>
                            <i class="fas fa-chevron-right ms-auto text-muted small"></i>
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

        .letter-spacing-1 {
            letter-spacing: 1px;
        }

        .btn-white {
            background: white;
            border: 1px solid #e2e8f0;
        }

        .btn-white:hover {
            background: #f8f9fa;
        }

        .hover-lift {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .08) !important;
        }

        .bg-success-subtle {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .bg-secondary-subtle {
            background-color: #e2e3e5;
            color: #41464b;
        }

        .avatar-group {
            display: flex;
        }

        .avatar-group .avatar {
            margin-left: -8px;
            border: 2px solid white;
        }

        .avatar-group .avatar:first-child {
            margin-left: 0;
        }
    </style>
@endsection

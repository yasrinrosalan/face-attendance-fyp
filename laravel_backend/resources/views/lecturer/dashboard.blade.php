@extends('layouts.app')

@section('content')
    <div class="container py-4 font-sans-serif">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 g-3">
            <div>
                <h2 class="fw-bold text-dark mb-1">Lecturer Dashboard</h2>
                <p class="text-muted mb-0">Overview of your courses and recent sessions.</p>
            </div>
            <div class="d-flex gap-2 mt-3 mt-md-0">
                <button type="button" class="btn btn-outline-primary fw-medium px-3 shadow-sm" data-bs-toggle="modal"
                    data-bs-target="#addCourseModal">
                    <i class="fas fa-plus me-2"></i> Add Course
                </button>
                <button type="button" class="btn btn-primary fw-bold px-4 shadow-sm" data-bs-toggle="modal"
                    data-bs-target="#createSessionModal">
                    <i class="fas fa-calendar-plus me-2"></i> Create Session
                </button>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-lg-2 g-4">
            {{-- FIX: Renamed '$course' to '$courseItem' to prevent variable leaking --}}
            @forelse($courses as $courseItem)
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm overflow-hidden">
                        <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span
                                        class="badge bg-primary bg-opacity-10 text-primary mb-2">{{ $courseItem->course_code }}</span>
                                    <h5 class="card-title fw-bold text-dark">{{ $courseItem->course_name }}</h5>
                                </div>
                                <a href="{{ route('lecturer.course.show', $courseItem->id) }}"
                                    class="btn btn-sm btn-light text-muted">
                                    <i class="fas fa-ellipsis-h"></i>
                                </a>
                            </div>
                        </div>
                        <div class="card-body px-4 pb-4 pt-2 font-sans-serif">
                            @if ($courseItem->latest_session_stats)
                                <div class="p-3 bg-light rounded-3 border border-light-subtle">
                                    <h6 class="text-uppercase fw-bold text-secondary small letter-spacing-1 mb-3">
                                        <i class="fas fa-history me-2"></i>Latest Session Summary
                                    </h6>
                                    <div class="mb-3">
                                        <strong
                                            class="text-dark">{{ $courseItem->latest_session_stats->session_title }}</strong>
                                    </div>

                                    <div class="row text-center g-0 divide-x">
                                        <div class="col">
                                            <div class="display-6 fw-bold text-success mb-1">
                                                {{ $courseItem->latest_session_stats->attendance_rate }}%</div>
                                            <div class="small text-muted fw-bold text-uppercase">Rate</div>
                                        </div>
                                        <div class="col p-2"
                                            style="border-left: 1px solid #e9ecef; border-right: 1px solid #e9ecef;">
                                            <div class="display-6 fw-bold text-dark mb-1">
                                                {{ $courseItem->latest_session_stats->present_count }}</div>
                                            <div class="small text-muted fw-bold text-uppercase">Present</div>
                                        </div>
                                        <div class="col">
                                            <div class="display-6 fw-bold text-warning mb-1">
                                                {{ $courseItem->latest_session_stats->late_count }}</div>
                                            <div class="small text-muted fw-bold text-uppercase">Late</div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-4 text-muted bg-light rounded-3 border border-dashed">
                                    <i class="fas fa-calendar-times fa-2x mb-3 opacity-50"></i>
                                    <p class="mb-0 fw-medium">No sessions conducted yet.</p>
                                </div>
                            @endif
                        </div>
                        <div class="card-footer bg-white border-top p-0">
                            <a href="{{ route('lecturer.course.show', $courseItem->id) }}"
                                class="btn btn-link text-decoration-none fw-medium w-100 py-3 text-primary">
                                View All Sessions & Records <i class="fas fa-arrow-right ms-2 small"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-5 bg-white rounded-3 shadow-sm border border-dashed">
                        <i class="fas fa-folder-open fa-3x text-primary opacity-50 mb-3"></i>
                        <h4 class="fw-bold text-dark">You haven't added any courses yet.</h4>
                        <p class="text-muted mb-4">Get started by adding your first course.</p>
                        <button type="button" class="btn btn-primary px-4 py-2 fw-bold shadow-sm" data-bs-toggle="modal"
                            data-bs-target="#addCourseModal">
                            <i class="fas fa-plus me-2"></i> Add New Course
                        </button>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    @include('lecturer.partials.add_course_modal')
    {{-- The modal included here will no longer see the leftover variable from the loop above --}}
    @include('lecturer.partials.create_session_modal')

    <style>
        .font-sans-serif {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .letter-spacing-1 {
            letter-spacing: 1px;
        }

        .divide-x>*+* {
            border-left-width: 1px;
            border-left-color: #e9ecef;
        }

        .bg-primary-subtle {
            background-color: #e0e7ff !important;
        }

        .border-light-subtle {
            border-color: #e9ecef !important;
        }

        .border-dashed {
            border-style: dashed !important;
        }
    </style>
@endsection

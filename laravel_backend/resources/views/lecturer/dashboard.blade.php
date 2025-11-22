@extends('layouts.app')

@section('content')
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold text-primary mb-0">
                        <i class="fas fa-calendar-plus me-2"></i> New Session
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('lecturer.session.create') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label for="course_id"
                                    class="form-label text-secondary fw-semibold small text-uppercase mb-0">Course</label>
                                <button type="button" class="btn btn-sm btn-soft-primary py-1 px-2 fw-bold"
                                    style="font-size: 0.75rem;" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                                    <i class="fas fa-plus me-1"></i> Add New
                                </button>
                            </div>

                            <select name="course_id" id="course_id" class="form-select form-select-lg fs-6 text-dark"
                                required>
                                @if ($courses->isEmpty())
                                    <option value="" disabled selected class="text-muted">-- No courses available --
                                    </option>
                                @else
                                    <option value="" disabled selected>Select a course...</option>
                                @endif

                                @foreach ($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->course_code }} -
                                        {{ $course->course_name }}</option>
                                @endforeach
                            </select>
                            @if ($courses->isEmpty())
                                <div class="form-text text-danger small mt-1"><i class="fas fa-info-circle me-1"></i> You
                                    need to add a course first.</div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="session_title"
                                class="form-label text-secondary fw-semibold small text-uppercase">Session Title</label>
                            <input type="text" name="session_title" id="session_title" class="form-control"
                                placeholder="e.g. Week 1: Introduction" required>
                        </div>

                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <label for="starts_at"
                                    class="form-label text-secondary fw-semibold small text-uppercase">Starts</label>
                                <input type="datetime-local" name="starts_at" id="starts_at"
                                    class="form-control small-date-input" value="{{ now()->format('Y-m-d\TH:i') }}"
                                    required>
                            </div>
                            <div class="col-6">
                                <label for="ends_at"
                                    class="form-label text-secondary fw-semibold small text-uppercase">Ends</label>
                                <input type="datetime-local" name="ends_at" id="ends_at"
                                    class="form-control small-date-input"
                                    value="{{ now()->addHour()->format('Y-m-d\TH:i') }}" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold"
                            @if ($courses->isEmpty()) disabled @endif>
                            <i class="fas fa-check me-2"></i> Create Session
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-2">
                    <h5 class="fw-bold text-primary mb-0">
                        <i class="fas fa-list-ul me-2"></i> My Sessions
                    </h5>
                </div>
                <div class="card-body p-4">

                    @forelse($courses as $course)
                        <div class="mb-4">
                            <div class="d-flex align-items-center mb-3 border-bottom pb-2">
                                <div class="bg-light rounded p-2 me-3 text-primary fw-bold">
                                    {{ $course->course_code }}
                                </div>
                                <h6 class="fw-bold text-dark mb-0">{{ $course->course_name }}</h6>
                            </div>

                            @if ($course->attendance_sessions->isEmpty())
                                <div class="text-muted small fst-italic ps-2">No sessions created yet.</div>
                            @else
                                <div class="list-group list-group-flush">
                                    @foreach ($course->attendance_sessions->sortByDesc('starts_at') as $session)
                                        <div
                                            class="list-group-item border-0 px-0 py-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between hover-bg-light rounded px-2 transition-all">
                                            <div class="mb-2 mb-md-0">
                                                <div class="d-flex align-items-center mb-1">
                                                    <strong class="text-dark">{{ $session->session_title }}</strong>
                                                    <span
                                                        class="badge bg-light text-secondary border ms-2 font-monospace">{{ $session->referral_code }}</span>
                                                </div>
                                                <div class="small text-muted">
                                                    <i class="far fa-clock me-1"></i>
                                                    {{ $session->starts_at->format('M d, h:i A') }}

                                                    <span class="mx-2">•</span>

                                                    @if ($session->isActive())
                                                        <span class="text-success fw-bold"><i
                                                                class="fas fa-circle fa-xs me-1"></i> Active</span>
                                                    @elseif(now()->gt($session->ends_at))
                                                        <span class="text-secondary">Expired</span>
                                                    @else
                                                        <span class="text-warning">Pending</span>
                                                    @endif
                                                </div>
                                            </div>

                                            <a href="{{ route('lecturer.session.show', $session->id) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                Manage <i class="fas fa-chevron-right ms-1"></i>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle"
                                    style="width: 80px; height: 80px;">
                                    <i class="fas fa-chalkboard-teacher fa-3x text-muted opacity-50"></i>
                                </div>
                            </div>
                            <h4 class="fw-bold text-dark">No Courses Yet</h4>
                            <p class="text-muted mb-4" style="max-width: 400px; margin: 0 auto;">
                                It looks like you haven't added any subjects to your dashboard yet. Add a course to start
                                creating attendance sessions.
                            </p>
                            <button type="button" class="btn btn-primary px-4 py-2" data-bs-toggle="modal"
                                data-bs-target="#addCourseModal">
                                <i class="fas fa-plus me-2"></i> Create Your First Course
                            </button>
                        </div>
                    @endforelse

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addCourseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary">Add New Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-4">
                    <form action="{{ route('lecturer.course.create') }}" method="POST">
                        @csrf
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="course_code" name="course_code"
                                placeholder="e.g. BCS3043" required>
                            <label for="course_code">Course Code (e.g., CS101)</label>
                        </div>
                        <div class="form-floating mb-4">
                            <input type="text" class="form-control" id="course_name" name="course_name"
                                placeholder="e.g. Software Engineering" required>
                            <label for="course_name">Course Name</label>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-2 fw-bold">Save Course</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .btn-soft-primary {
            color: var(--bs-primary);
            background-color: rgba(32, 58, 141, 0.1);
            border: none;
        }

        .btn-soft-primary:hover {
            background-color: rgba(32, 58, 141, 0.2);
            color: var(--bs-primary);
        }

        .hover-bg-light:hover {
            background-color: #f8f9fa;
        }

        .transition-all {
            transition: all 0.2s ease;
        }

        .small-date-input {
            font-size: 0.85rem;
        }
    </style>

@endsection

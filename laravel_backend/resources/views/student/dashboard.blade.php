@extends('layouts.app')

@section('content')
    <div class="container py-4 font-sans-serif">

        <div class="row mb-4 align-items-end">
            <div class="col-md-8">
                <h6 class="text-uppercase text-muted small fw-bold ls-1 mb-1">Student Dashboard</h6>
                <h2 class="fw-bold text-dark mb-0">Welcome back, {{ $student->name }}</h2>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="d-inline-block text-start">
                    <small class="d-block text-muted mb-1">Current Date</small>
                    <div class="d-flex align-items-center bg-white px-3 py-2 rounded-pill shadow-sm border">
                        <i class="far fa-calendar-alt text-primary me-2"></i>
                        <span class="fw-bold text-dark">{{ now()->format('D, d M Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 overflow-hidden">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted small fw-bold text-uppercase mb-1">Biometric Status</p>
                            @if ($student->face_template_path)
                                <h5 class="fw-bold text-success mb-0"><i class="fas fa-check-circle me-2"></i>Enrolled</h5>
                            @else
                                <h5 class="fw-bold text-danger mb-0"><i class="fas fa-times-circle me-2"></i>Not Enrolled
                                </h5>
                            @endif
                        </div>
                        <div
                            class="bg-light rounded-circle p-3 text-{{ $student->face_template_path ? 'success' : 'danger' }}">
                            <i class="fas fa-user-lock fa-lg"></i>
                        </div>
                    </div>
                    @if (!$student->face_template_path)
                        <div class="card-footer bg-danger-subtle border-0 text-center p-2">
                            <a href="{{ route('student.enrollment.page') }}"
                                class="text-danger fw-bold small text-decoration-none stretched-link">Enroll Now <i
                                    class="fas fa-arrow-right ms-1"></i></a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted small fw-bold text-uppercase mb-1">Total Sessions</p>
                            <h3 class="fw-bold text-dark mb-0">{{ $courseStats->sum('attended_sessions') }}</h3>
                        </div>
                        <div class="bg-primary-subtle text-primary rounded-circle p-3">
                            <i class="fas fa-calendar-check fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 bg-primary text-white"
                    style="background: linear-gradient(135deg, #203a8d 0%, #4e6ecc 100%);">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start">
                            <div class="me-3 bg-white bg-opacity-25 rounded-circle p-2">
                                <i class="fas fa-qrcode fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Marking Attendance?</h6>
                                <p class="small opacity-75 mb-0" style="line-height: 1.4;">
                                    Use your phone camera or a QR scanner app to scan the code projected by your lecturer.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-secondary m-0">My Courses</h5>
                </div>

                @forelse($courseStats as $stat)
                    <div class="card border-0 shadow-sm mb-3 hover-scale">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light text-dark fw-bold rounded px-2 py-1 me-3 border"
                                            style="font-family: monospace;">
                                            {{ $stat->course_code }}
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0">{{ $stat->course_name }}</h6>
                                            <span class="small text-muted">{{ $stat->total_sessions }} Total Sessions</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="small fw-bold text-{{ $stat->status_color }}">Attendance Rate</span>
                                        <span class="small fw-bold text-dark">{{ $stat->percentage }}%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-{{ $stat->status_color }}" role="progressbar"
                                            style="width: {{ $stat->percentage }}%"></div>
                                    </div>
                                </div>
                                <div class="col-md-2 text-end">
                                    <span
                                        class="badge bg-{{ $stat->status_color }} bg-opacity-10 text-{{ $stat->status_color }} px-3 py-2 rounded-pill">
                                        {{ $stat->attended_sessions }} Present
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 bg-white rounded-3 shadow-sm border border-dashed">
                        <i class="fas fa-folder-open fa-3x text-muted opacity-25 mb-3"></i>
                        <h5 class="fw-bold text-secondary">No Courses Found</h5>
                        <p class="text-muted small">You haven't been added to any attendance lists yet.</p>
                    </div>
                @endforelse
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-dark mb-0">{{ $today->format('F Y') }}</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="calendar-grid">
                            <div class="cal-head">S</div>
                            <div class="cal-head">M</div>
                            <div class="cal-head">T</div>
                            <div class="cal-head">W</div>
                            <div class="cal-head">T</div>
                            <div class="cal-head">F</div>
                            <div class="cal-head">S</div>

                            @foreach ($days as $day)
                                @if ($day)
                                    @php
                                        $statusClass = '';
                                        if ($day->status == 'present') {
                                            $statusClass = 'bg-success text-white';
                                        } elseif ($day->status == 'late') {
                                            $statusClass = 'bg-warning text-dark';
                                        } elseif ($day->is_today) {
                                            $statusClass = 'bg-primary text-white fw-bold shadow-sm';
                                        }
                                    @endphp
                                    <div
                                        class="cal-day {{ $statusClass }} {{ !$day->status && !$day->is_today ? 'text-muted' : '' }}">
                                        {{ $day->date }}
                                    </div>
                                @else
                                    <div class="cal-day empty"></div>
                                @endif
                            @endforeach
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex justify-content-between small">
                                <span class="d-flex align-items-center"><span
                                        class="dot bg-success me-2"></span>Present</span>
                                <span class="d-flex align-items-center"><span class="dot bg-warning me-2"></span>Late</span>
                                <span class="d-flex align-items-center"><span
                                        class="dot bg-primary me-2"></span>Today</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .font-sans-serif {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .ls-1 {
            letter-spacing: 1px;
        }

        .bg-primary-subtle {
            background-color: #e0e7ff !important;
            color: #3730a3 !important;
        }

        .bg-danger-subtle {
            background-color: #fee2e2 !important;
        }

        .hover-scale {
            transition: transform 0.2s;
        }

        .hover-scale:hover {
            transform: scale(1.01);
        }

        /* Calendar Styles */
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
            text-align: center;
        }

        .cal-head {
            font-size: 0.75rem;
            font-weight: bold;
            color: #adb5bd;
            padding-bottom: 5px;
        }

        .cal-day {
            height: 35px;
            width: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            border-radius: 50%;
            font-size: 0.85rem;
        }

        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }
    </style>
@endsection

@extends('layouts.app')

@section('content')
    <div class="container py-4 font-sans-serif">

        <div class="row mb-4 align-items-end">
            <div class="col-md-8">
                <h6 class="text-uppercase text-muted small fw-bold ls-1 mb-1">Student Dashboard</h6>
                <h2 class="fw-bold text-dark mb-0 tracking-tight">Welcome back, {{ $student->name }}</h2>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="d-inline-block text-start">
                    <small class="d-block text-muted mb-1 fw-medium">Current Date</small>
                    <div class="d-flex align-items-center bg-white px-4 py-2 rounded-pill shadow-sm border border-light">
                        <i class="far fa-calendar-alt text-primary me-2"></i>
                        <span class="fw-bold text-dark">{{ now()->format('D, d M Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        @if (isset($activeSession) && $activeSession)
            <div class="card border-primary border-opacity-25 shadow-sm rounded-4 mb-4 slide-down-fade"
                style="background: linear-gradient(135deg, rgba(13, 110, 253, 0.08) 0%, rgba(13, 110, 253, 0.02) 100%);">
                <div
                    class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div class="d-flex align-items-start">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm flex-shrink-0"
                            style="width: 48px; height: 48px;">
                            <i class="fas fa-satellite-dish fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-primary mb-1 d-flex align-items-center">
                                <span class="spinner-grow spinner-grow-sm text-danger me-2" role="status"></span>
                                Live Session Active!
                            </h6>
                            <p class="mb-0 text-dark fw-medium small" style="line-height: 1.4;">
                                <strong>{{ $activeSession->course->course_code }}</strong>
                                ({{ $activeSession->session_title }}) is currently ongoing. <br class="d-none d-md-block">
                                <span class="text-muted">Ends at {{ $activeSession->ends_at->format('h:i A') }} (in
                                    {{ now()->diffInMinutes($activeSession->ends_at) }} mins).</span>
                            </p>
                        </div>
                    </div>

                    <div>
                        @if ($student->face_template_path)
                            <button onclick="startScanning()"
                                class="btn btn-primary fw-bold rounded-pill shadow-sm px-4 py-2 btn-hover-lift text-nowrap pulse-btn border-0">
                                <i class="fas fa-camera-retro me-2"></i>Scan to Attend
                            </button>
                        @else
                            <a href="{{ route('student.enrollment.page') }}"
                                class="btn btn-danger fw-bold rounded-pill shadow-sm px-4 py-2 btn-hover-lift text-nowrap">
                                <i class="fas fa-user-lock me-2"></i>Enroll Face First
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 overflow-hidden rounded-4 card-hover">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted small fw-bold text-uppercase mb-1 ls-1">Biometric Status</p>
                            @if ($student->face_template_path)
                                <h5 class="fw-bold text-success mb-0 d-flex align-items-center">
                                    <i class="fas fa-check-circle me-2"></i>Enrolled
                                </h5>
                            @else
                                <h5 class="fw-bold text-danger mb-0 d-flex align-items-center">
                                    <i class="fas fa-times-circle me-2"></i>Not Enrolled
                                </h5>
                            @endif
                        </div>
                        <div
                            class="bg-{{ $student->face_template_path ? 'success' : 'danger' }} bg-opacity-10 rounded-circle p-3 text-{{ $student->face_template_path ? 'success' : 'danger' }}">
                            <i class="fas fa-user-lock fa-lg"></i>
                        </div>
                    </div>
                    @if (!$student->face_template_path)
                        <div
                            class="card-footer bg-danger bg-opacity-10 border-0 text-center p-3 transition-all hover-bg-danger">
                            <a href="{{ route('student.enrollment.page') }}"
                                class="text-danger fw-bold small text-decoration-none stretched-link d-flex align-items-center justify-content-center">
                                Enroll Face Now <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 rounded-4 card-hover">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted small fw-bold text-uppercase mb-1 ls-1">Total Sessions</p>
                            <h2 class="fw-bolder text-dark mb-0 display-6">{{ $courseStats->sum('attended_sessions') }}
                            </h2>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3">
                            <i class="fas fa-calendar-check fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                @if ($student->face_template_path)
                    <div class="card border-0 shadow-sm h-100 text-white rounded-4 card-hover position-relative"
                        style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                        <div class="card-body p-4 d-flex flex-column justify-content-center">

                            <div class="d-flex align-items-center justify-content-between cursor-pointer mb-3"
                                onclick="startScanning()">
                                <div class="d-flex align-items-start">
                                    <div class="me-3 bg-white bg-opacity-25 rounded-circle p-3 shadow-sm text-center"
                                        style="width: 54px; height: 54px;">
                                        <i class="fas fa-camera fa-lg text-white" id="scan-icon"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1 fs-5" id="scan-title">Scan Now</h6>
                                        <p class="small text-white-50 mb-0 fw-medium" style="line-height: 1.3;">
                                            Tap here to scan class QR code.
                                        </p>
                                    </div>
                                </div>
                                <i class="fas fa-chevron-right text-white opacity-50 fa-2x"></i>
                            </div>

                            <hr class="border-white border-opacity-25 my-2">
                            <div class="text-center mt-1">
                                <a href="#"
                                    class="text-white-50 small fw-bold text-decoration-none hover-text-white transition-all"
                                    data-bs-toggle="modal" data-bs-target="#manualCodeModal">
                                    <i class="fas fa-keyboard me-1"></i> Camera broken? Enter code manually
                                </a>
                            </div>

                        </div>
                    </div>
                @else
                    <div class="card border-0 shadow-sm h-100 text-white rounded-4 position-relative"
                        style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); cursor: not-allowed; opacity: 0.95;">
                        <div class="card-body p-4 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-start">
                                <div class="me-3 bg-white bg-opacity-25 rounded-circle p-3 shadow-sm text-center"
                                    style="width: 54px; height: 54px;">
                                    <i class="fas fa-lock fa-lg text-white opacity-75"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1 fs-5 opacity-75">Scan Locked</h6>
                                    <p class="small text-white-50 mb-0 fw-medium" style="line-height: 1.3;">
                                        <i class="fas fa-exclamation-triangle text-warning me-1 opacity-75"></i> Enroll your
                                        face template first to unlock.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">

                <div class="card border-0 shadow-sm mb-5 rounded-4 bg-light bg-gradient">
                    <div class="card-body p-4 p-md-5">
                        <h6 class="fw-bold text-primary mb-3 d-flex align-items-center">
                            <i class="fas fa-sign-in-alt me-2"></i>Enroll in a New Course
                        </h6>
                        <form action="{{ route('student.enroll.course') }}" method="POST">
                            @csrf
                            <div class="d-flex flex-column flex-md-row gap-3">
                                <div
                                    class="input-group input-group-lg shadow-sm bg-white rounded-3 overflow-hidden border custom-focus-ring flex-grow-1">
                                    <span class="input-group-text bg-transparent border-0 text-muted px-3">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" name="course_code"
                                        class="form-control border-0 px-0 fw-medium text-dark"
                                        placeholder="Enter Course Code (e.g. CS101)" required>
                                </div>
                                <button type="submit"
                                    class="btn btn-primary btn-lg fw-bold px-4 rounded-3 shadow-sm btn-hover-lift text-nowrap">
                                    Enroll <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                            <div class="form-text mt-3 text-muted small fw-medium">
                                <i class="fas fa-info-circle me-1 text-primary opacity-75"></i> Ask your lecturer for the
                                exact course code to join their class.
                            </div>
                        </form>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-dark m-0">My Courses</h5>
                </div>

                @forelse($courseStats as $stat)
                    <div class="card border-0 shadow-sm mb-3 rounded-4 card-hover transition-all">
                        <div class="card-body p-4">
                            <div class="row align-items-center g-3">
                                <div class="col-md-5">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light text-primary fw-bold rounded-3 px-3 py-2 me-3 border border-light shadow-sm"
                                            style="font-family: monospace; letter-spacing: 0.5px;">
                                            {{ $stat->course_code }}
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-1">{{ $stat->course_name }}</h6>
                                            <span class="small text-muted fw-medium"><i
                                                    class="far fa-calendar-alt me-1"></i> {{ $stat->total_sessions }}
                                                Total
                                                Sessions</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="small fw-bold text-{{ $stat->status_color }}">Attendance Rate</span>
                                        <span class="small fw-bold text-dark">{{ $stat->percentage }}%</span>
                                    </div>
                                    <div class="progress rounded-pill bg-light" style="height: 8px;">
                                        <div class="progress-bar rounded-pill bg-{{ $stat->status_color }}"
                                            role="progressbar" style="width: {{ $stat->percentage }}%"></div>
                                    </div>
                                </div>
                                <div class="col-md-3 text-md-end">
                                    <span
                                        class="badge bg-{{ $stat->status_color }} bg-opacity-10 text-{{ $stat->status_color }} px-3 py-2 rounded-pill border border-{{ $stat->status_color }} border-opacity-25 shadow-sm">
                                        <i class="fas fa-user-check me-1"></i> {{ $stat->attended_sessions }} Present
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 bg-white rounded-4 shadow-sm border border-light">
                        <div class="bg-light rounded-circle d-inline-flex p-4 mb-3">
                            <i class="fas fa-folder-open fa-3x text-primary opacity-50"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">No Courses Found</h5>
                        <p class="text-muted small mb-0">You haven't enrolled in any courses yet.</p>
                    </div>
                @endforelse
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 20px;">
                    <div
                        class="card-header bg-white border-bottom border-light py-3 px-4 d-flex justify-content-between align-items-center rounded-top-4">
                        <h6 class="fw-bold text-dark mb-0">{{ $today->format('F Y') }}</h6>
                        <i class="far fa-calendar text-muted"></i>
                    </div>
                    <div class="card-body p-4">
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
                                            $statusClass = 'bg-success text-white shadow-sm';
                                        } elseif ($day->status == 'late') {
                                            $statusClass = 'bg-warning text-dark shadow-sm';
                                        } elseif ($day->is_today) {
                                            $statusClass = 'bg-primary text-white fw-bold shadow-sm ring-primary';
                                        }
                                    @endphp
                                    <div
                                        class="cal-day {{ $statusClass }} {{ !$day->status && !$day->is_today ? 'text-secondary bg-light bg-opacity-50 hover-bg-light' : '' }}">
                                        {{ $day->date }}
                                    </div>
                                @else
                                    <div class="cal-day empty"></div>
                                @endif
                            @endforeach
                        </div>

                        <div class="mt-4 pt-4 border-top border-light">
                            <div class="d-flex justify-content-between small fw-medium">
                                <span class="d-flex align-items-center text-muted"><span
                                        class="dot bg-success shadow-sm me-2"></span>Present</span>
                                <span class="d-flex align-items-center text-muted"><span
                                        class="dot bg-warning shadow-sm me-2"></span>Late</span>
                                <span class="d-flex align-items-center text-muted"><span
                                        class="dot bg-primary shadow-sm me-2"></span>Today</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="manualCodeModal" tabindex="-1" aria-labelledby="manualCodeModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="manualCodeModalLabel">
                        <i class="fas fa-keyboard text-primary me-2"></i>Enter Session Code
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-4">If you cannot scan the QR code, ask your lecturer for the 6-character
                        session code displayed on their screen.</p>

                    <form action="{{ route('student.session.find') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <input type="text" name="referral_code"
                                class="form-control form-control-lg text-center fw-bold text-uppercase custom-focus-ring"
                                placeholder="e.g., A1B2C3" maxlength="6" required
                                style="letter-spacing: 2px; font-size: 1.5rem;">
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold btn-hover-lift">
                                Verify & Continue to Face Scan <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function startScanning() {
            const ua = navigator.userAgent;
            const isAndroid = /Android/i.test(ua);

            if (isAndroid) {
                // 1. ANDROID: Try to open a native barcode scanner app
                window.location.href = "intent://scan/#Intent;scheme=zxing;package=com.google.zxing.client.android;end";

                // Fallback: If they don't have the app, prompt them to download it
                setTimeout(function() {
                    if (document.hidden) return; // If the app successfully opened, do nothing
                    if (confirm("You need a QR Scanner app to scan natively. Would you like to download one?")) {
                        window.location.href =
                            "https://play.google.com/store/apps/details?id=com.google.zxing.client.android";
                    }
                }, 1000);

            } else {
                // 2. iOS & DESKTOP: Redirect to the built-in web scanner!
                window.location.href = "{{ route('student.scanner') }}";
            }
        }
    </script>

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

        .card-hover {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-hover:hover {
            transform: translateY(-3px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .08) !important;
        }

        .btn-hover-lift {
            transition: all 0.2s ease-in-out;
        }

        .btn-hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .hover-text-white:hover {
            color: #ffffff !important;
        }

        .custom-focus-ring:focus-within {
            border-color: var(--bs-primary) !important;
            box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.15) !important;
        }

        .custom-focus-ring input:focus {
            box-shadow: none;
            outline: none;
        }

        .custom-focus-ring:focus-within .input-group-text {
            color: var(--bs-primary) !important;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
            text-align: center;
        }

        .cal-head {
            font-size: 0.75rem;
            font-weight: 700;
            color: #adb5bd;
            padding-bottom: 8px;
        }

        .cal-day {
            height: 38px;
            width: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            border-radius: 50%;
            font-size: 0.85rem;
            transition: all 0.2s;
        }

        .hover-bg-light:hover {
            background-color: #e9ecef !important;
            cursor: default;
        }

        .ring-primary {
            box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb), 0.2) !important;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }

        /* NEW ANIMATIONS FOR LIVE BANNER */
        @keyframes pulse-soft {
            0% {
                box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.4);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(13, 110, 253, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(13, 110, 253, 0);
            }
        }

        .pulse-btn {
            animation: pulse-soft 2s infinite;
        }

        .slide-down-fade {
            animation: slideDownFade 0.5s ease-out forwards;
        }

        @keyframes slideDownFade {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endsection

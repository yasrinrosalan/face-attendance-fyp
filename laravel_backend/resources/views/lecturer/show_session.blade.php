@extends('layouts.app')

@section('content')
    <div class="container py-4 font-sans-serif">

        <!-- Back Navigation -->
        <div class="mb-4">
            <a href="{{ route('lecturer.course.show', $session->course_id) }}"
                class="text-decoration-none fw-bold text-primary align-items-center d-inline-flex link-hover">
                <i class="fas fa-arrow-left me-2 transition-all"></i> Back to Course
            </a>
        </div>

        <!-- Session Header -->
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-5 gap-4">
            <div>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span
                        class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-3 py-2 rounded-pill fw-bold">
                        Week {{ $session->week_number }}
                    </span>
                    @if ($session->mode === 'online')
                        <span
                            class="badge bg-dark bg-opacity-10 text-dark border border-dark border-opacity-10 px-3 py-2 rounded-pill fw-bold">
                            <i class="fas fa-laptop-house me-1"></i> Online
                        </span>
                    @else
                        <span
                            class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 px-3 py-2 rounded-pill fw-bold">
                            <i class="fas fa-building me-1"></i> Physical
                        </span>
                    @endif
                </div>
                <h6 class="text-uppercase text-secondary fw-bold letter-spacing-1 mb-1">{{ $session->course->course_name }}
                </h6>
                <h1 class="fw-bolder text-dark mb-2 tracking-tight display-6">{{ $session->session_title }}</h1>
                <p class="text-muted fw-medium mb-0">
                    <i class="far fa-calendar-alt me-1 text-primary opacity-75"></i>
                    {{ $session->starts_at->format('l, F j, Y') }} &nbsp;|&nbsp;
                    <i class="far fa-clock me-1 text-primary opacity-75"></i> {{ $session->starts_at->format('h:i A') }} -
                    {{ $session->ends_at->format('h:i A') }}
                </p>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('lecturer.attendance.export', $session->id) }}"
                    class="btn btn-light border shadow-sm px-4 py-2 rounded-pill fw-bold btn-hover-lift text-primary">
                    <i class="fas fa-file-csv me-2"></i> Export CSV
                </a>
                <a href="{{ route('lecturer.attendance.pdf', $session->id) }}"
                    class="btn btn-light border shadow-sm px-4 py-2 rounded-pill fw-bold btn-hover-lift text-primary">
                    <i class="fas fa-file-pdf me-2"></i> Download PDF
                </a>
                <form action="{{ route('lecturer.session.delete', $session->id) }}" method="POST"
                    onsubmit="return confirm('Are you sure you want to delete this session?');" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="btn btn-outline-danger px-4 py-2 rounded-pill fw-bold shadow-sm btn-hover-lift">
                        <i class="fas fa-trash-alt me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>

        <div class="row g-4">

            <!-- Center Column: QR Code & Timer (Only if active) -->
            @if ($session->isActive())
                <div class="col-12 mb-4">
                    <div class="card border-0 shadow-sm rounded-4 text-center overflow-hidden">
                        <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                            <span
                                class="badge bg-success bg-opacity-10 text-success px-4 py-2 rounded-pill border border-success border-opacity-25 fw-bold fs-6">
                                <span class="spinner-grow spinner-grow-sm text-success me-2" role="status"
                                    style="width: 0.5rem; height: 0.5rem;"></span> Session Active
                            </span>
                        </div>
                        <div class="card-body p-5">

                            <!-- Timer -->
                            <div class="mb-4">
                                <div
                                    class="p-3 bg-primary bg-opacity-10 text-primary rounded-4 border border-primary border-opacity-10 d-inline-block shadow-sm">
                                    <h6 class="fw-bold text-uppercase ls-1 mb-1" style="font-size: 0.75rem;">Time Remaining
                                    </h6>
                                    <span id="countdown-timer"
                                        class="fw-bolder font-monospace fs-3 tracking-tight">Calculating...</span>
                                </div>
                            </div>

                            <!-- QR Code Container -->
                            <div class="d-inline-block p-3 bg-white rounded-4 shadow-sm border position-relative"
                                style="min-width: 310px; min-height: 310px;">
                                <div id="qr-loading" class="position-absolute top-50 start-50 translate-middle">
                                    <div class="spinner-border text-primary" role="status"></div>
                                </div>
                                <img id="dynamic-qr-image" src="" alt="Scan for Attendance" width="280"
                                    height="280" style="opacity: 0; transition: opacity 0.3s ease-in-out;">
                            </div>

                            <div class="text-muted small mt-3 fw-medium">
                                <i class="fas fa-sync fa-spin me-1 text-primary"></i> QR Code refreshes automatically every
                                10s for security.
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="col-12 mb-4">
                    <div class="alert alert-secondary border-0 shadow-sm rounded-4 d-flex align-items-center p-4">
                        <div class="bg-secondary bg-opacity-10 p-3 rounded-circle me-3">
                            <i class="fas fa-times-circle fa-2x text-secondary"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-1">Session Ended</h5>
                            <p class="mb-0 text-muted">This attendance session closed at
                                {{ $session->ends_at->format('h:i A') }}. QR scanning is disabled.</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Quick Stats Cards -->
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 text-center h-100 p-3 card-hover">
                    <h6 class="text-muted fw-bold small text-uppercase ls-1 mb-2">Enrolled</h6>
                    <h2 class="fw-bolder text-dark mb-0">{{ $totalStudents }}</h2>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 text-center h-100 p-3 card-hover">
                    <h6 class="text-success fw-bold small text-uppercase ls-1 mb-2">Present</h6>
                    <h2 class="fw-bolder text-success mb-0">{{ $presentCount }}</h2>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 text-center h-100 p-3 card-hover">
                    <h6 class="text-warning fw-bold small text-uppercase ls-1 mb-2">Late</h6>
                    <h2 class="fw-bolder text-warning mb-0">{{ $lateCount }}</h2>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 text-center h-100 p-3 card-hover">
                    <h6 class="text-danger fw-bold small text-uppercase ls-1 mb-2">Absent</h6>
                    <h2 class="fw-bolder text-danger mb-0">{{ $absentCount }}</h2>
                </div>
            </div>

            <!-- Left: Attendance Table -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                    <div
                        class="card-header bg-white border-bottom border-light p-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-dark m-0">Student Attendance Roster</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light bg-opacity-50">
                                <tr>
                                    <th class="px-4 py-3 text-uppercase text-muted small fw-bold ls-1 border-0">Student Info
                                    </th>
                                    <th class="px-4 py-3 text-uppercase text-muted small fw-bold ls-1 border-0">Matric No.
                                    </th>
                                    <th class="px-4 py-3 text-uppercase text-muted small fw-bold ls-1 border-0">Timestamp
                                    </th>
                                    <th class="px-4 py-3 text-uppercase text-muted small fw-bold ls-1 border-0 text-end">
                                        Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendanceData as $data)
                                    <tr>
                                        <td class="px-4 py-3 border-light">
                                            <div class="fw-bold text-dark">{{ $data->student->name }}</div>
                                            <div class="small text-muted">{{ $data->student->email }}</div>
                                        </td>
                                        <td class="px-4 py-3 border-light font-monospace text-muted fw-medium">
                                            {{ $data->student->student_id }}
                                        </td>
                                        <td class="px-4 py-3 border-light small text-muted fw-medium">
                                            {{ $data->attended_at ? \Carbon\Carbon::parse($data->attended_at)->format('h:i:s A') : '-- : --' }}
                                        </td>
                                        <td class="px-4 py-3 border-light text-end">
                                            @if ($data->status === 'present')
                                                <span
                                                    class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill fw-bold">Present</span>
                                            @elseif($data->status === 'late')
                                                <span
                                                    class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2 rounded-pill fw-bold">Late</span>
                                            @else
                                                <span
                                                    class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill fw-bold">Absent</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="fas fa-users-slash fa-2x mb-3 opacity-50"></i>
                                            <p class="mb-0">No students are currently enrolled in this course.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right: Manual Override -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 20px;">
                    <div class="card-header bg-white border-bottom border-light p-4">
                        <h6 class="fw-bold text-dark m-0"><i
                                class="fas fa-user-edit me-2 text-primary opacity-75"></i>Manual Override</h6>
                    </div>
                    <div class="card-body p-4">
                        <p class="small text-muted mb-4 fw-medium">Manually mark a student as Present. If they are not
                            enrolled, they will be auto-enrolled into the course.</p>

                        @if (session('success'))
                            <div class="alert alert-success border-0 shadow-sm rounded-3 py-2 px-3 small fw-medium mb-3">
                                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger border-0 shadow-sm rounded-3 py-2 px-3 small fw-medium mb-3">
                                <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
                            </div>
                        @endif

                        <form action="{{ route('lecturer.session.manual_attend', $session->id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="student_email" class="form-label small fw-bold text-muted ls-1">STUDENT
                                    EMAIL</label>
                                <div class="input-group shadow-sm-hover rounded-3 overflow-hidden border">
                                    <span class="input-group-text bg-light border-0 text-muted px-3"><i
                                            class="fas fa-envelope"></i></span>
                                    <input type="email" name="student_email" id="student_email"
                                        class="form-control bg-light border-0 py-2" placeholder="student@demo.com"
                                        required>
                                </div>
                            </div>
                            <button type="submit"
                                class="btn btn-primary w-100 fw-bold rounded-pill shadow-sm btn-hover-lift py-2">
                                Mark Present
                            </button>
                        </form>

                        <div class="mt-4 pt-3 border-top border-light">
                            <span class="text-muted small d-block text-uppercase fw-bold mb-1 ls-1">Session Code</span>
                            <span
                                class="h3 font-monospace fw-bolder text-secondary mb-0 tracking-tight">{{ $session->referral_code }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($session->isActive())
        <script>
            // --- 1. SESSION TIMER ---
            let remainingSeconds = {{ now()->diffInSeconds($session->ends_at, false) }};
            const timerElement = document.getElementById("countdown-timer");

            function updateTimer() {
                if (remainingSeconds <= 0) {
                    clearInterval(timerInterval);
                    timerElement.innerHTML = "EXPIRED";
                    timerElement.classList.add("text-danger");
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                    return;
                }
                const hours = Math.floor(remainingSeconds / 3600);
                const minutes = Math.floor((remainingSeconds % 3600) / 60);
                const seconds = remainingSeconds % 60;
                let output = "";
                if (hours > 0) output += `${hours}h `;
                output += `${minutes}m ${seconds < 10 ? '0' : ''}${seconds}s`;
                timerElement.innerHTML = output;
                remainingSeconds--;
            }
            updateTimer();
            const timerInterval = setInterval(updateTimer, 1000);

            // --- 2. DYNAMIC QR REFRESHER ---
            const qrImage = document.getElementById('dynamic-qr-image');
            const qrLoading = document.getElementById('qr-loading');
            const qrDataUrl = "{{ route('lecturer.session.qr_data', $session->id) }}";

            function fetchNewQr() {
                fetch(qrDataUrl)
                    .then(response => response.json())
                    .then(data => {
                        if (data.qr_url) {
                            const newSrc =
                                `https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=${encodeURIComponent(data.qr_url)}`;
                            const imgLoader = new Image();
                            imgLoader.onload = () => {
                                qrImage.src = newSrc;
                                qrImage.style.opacity = "1";
                                qrLoading.classList.add('d-none');
                            };
                            imgLoader.src = newSrc;
                        }
                    })
                    .catch(err => console.error("Failed to fetch new QR token:", err));
            }

            fetchNewQr();
            setInterval(fetchNewQr, 10000);
        </script>
    @endif

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
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .link-hover:hover i {
            transform: translateX(-4px);
        }
    </style>
@endsection

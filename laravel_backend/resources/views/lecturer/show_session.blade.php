@extends('layouts.app')

@section('content')
    <div class="container py-4 font-sans-serif">
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
                                    class="badge bg-success-subtle text-success px-3 py-2 rounded-pill border border-success-subtle">Active
                                    - Scan Now</span>
                            @else
                                <span
                                    class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill border border-secondary-subtle">Expired</span>
                            @endif
                        </div>
                    </div>

                    <div class="card-body text-center p-5">

                        @if ($session->isActive())
                            <div class="mb-4">
                                <div class="d-inline-block p-3 bg-white rounded-4 shadow-sm border position-relative"
                                    style="min-width: 310px; min-height: 310px;">
                                    <div id="qr-loading" class="position-absolute top-50 start-50 translate-middle">
                                        <div class="spinner-border text-primary" role="status"></div>
                                    </div>
                                    <img id="dynamic-qr-image" src="" alt="Scan for Attendance" width="280"
                                        height="280" style="opacity: 0; transition: opacity 0.3s ease-in-out;">
                                </div>
                                <div class="text-muted small mt-2 fw-bold">
                                    <i class="fas fa-sync fa-spin me-1"></i> QR Code refreshes automatically every 10s for
                                    security.
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning mb-4">
                                This session has ended. QR code is disabled.
                            </div>
                            <div class="mb-4">
                                <div class="d-inline-block bg-light px-4 py-2 rounded-3 border">
                                    <span class="text-muted small d-block text-uppercase fw-bold mb-1">SESSION CODE
                                        (EXPIRED)</span>
                                    <span
                                        class="h1 font-monospace fw-bold text-secondary mb-0">{{ $session->referral_code }}</span>
                                </div>
                            </div>
                        @endif


                        <div class="mb-4">
                            @if ($session->isActive())
                                <div
                                    class="p-2 bg-primary-subtle text-primary rounded border border-primary-subtle d-inline-block">
                                    <i class="fas fa-hourglass-half me-2"></i>
                                    Ends in: <span id="countdown-timer" class="fw-bold font-monospace">Calculating...</span>
                                </div>
                                <div class="text-muted small mt-2">
                                    Closes at {{ $session->ends_at->format('h:i A') }}
                                </div>
                            @else
                                <div class="text-danger fw-bold">
                                    <i class="fas fa-times-circle me-1"></i> Session Ended
                                </div>
                                <div class="text-muted small">
                                    Closed at {{ $session->ends_at->format('h:i A') }}
                                </div>
                            @endif
                        </div>

                        <hr class="my-4 opacity-10">

                        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                            <a href="{{ route('lecturer.attendance.export', $session->id) }}"
                                class="btn btn-outline-primary px-4 py-2 fw-medium">
                                <i class="fas fa-file-csv me-2"></i>CSV
                            </a>
                            <a href="{{ route('lecturer.attendance.pdf', $session->id) }}"
                                class="btn btn-primary px-4 py-2 fw-medium">
                                <i class="fas fa-file-pdf me-2"></i>PDF Report
                            </a>
                            <form action="{{ route('lecturer.session.delete', $session->id) }}" method="POST"
                                onsubmit="return confirm('Delete session?');">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="btn btn-outline-danger px-4 py-2 fw-medium w-100">Delete</button>
                            </form>
                        </div>

                        <div class="mt-5 border-top pt-4 text-start">
                            <h6 class="fw-bold text-secondary mb-3">
                                <i class="fas fa-user-edit me-2"></i>Manual Attendance Override
                            </h6>
                            @if (session('success'))
                                <div class="alert alert-success small py-2 mb-3">{{ session('success') }}</div>
                            @endif
                            @if (session('error'))
                                <div class="alert alert-danger small py-2 mb-3">{{ session('error') }}</div>
                            @endif
                            <form action="{{ route('lecturer.session.manual_attend', $session->id) }}" method="POST">
                                @csrf
                                <div class="input-group mb-2">
                                    <span class="input-group-text bg-light border-end-0"><i
                                            class="fas fa-envelope text-muted"></i></span>
                                    <input type="email" name="student_email" class="form-control border-start-0 ps-1"
                                        placeholder="Enter Student Email" required>
                                    <button type="submit" class="btn btn-secondary fw-medium px-4">Mark Present</button>
                                </div>
                            </form>
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
                            // Use goqr.me API to generate QR from our dynamic URL
                            const newSrc =
                                `https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=${encodeURIComponent(data.qr_url)}`;

                            // Preload image before showing to avoid flickering
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

            // Fetch immediately on load
            fetchNewQr();
            // Then fetch every 30 seconds
            setInterval(fetchNewQr, 10000);
        </script>
    @endif

    <style>
        .font-sans-serif {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .letter-spacing-1 {
            letter-spacing: 1px;
        }

        .bg-primary-subtle {
            background-color: #e0e7ff !important;
            color: #3730a3 !important;
            border-color: #c7d2fe !important;
        }
    </style>
@endsection

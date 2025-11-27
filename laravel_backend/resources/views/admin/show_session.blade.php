@extends('layouts.app')

@section('content')
    <div class="container py-4 font-sans-serif">
        <div class="mb-4">
            <a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted fw-medium">
                <i class="fas fa-arrow-left me-2"></i>Back to Admin Dashboard
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
                                    class="badge bg-success-subtle text-success px-3 py-2 rounded-pill border border-success-subtle">Active</span>
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
                                    <i class="fas fa-sync fa-spin me-1"></i> QR Code refreshes automatically every 10s.
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning mb-4">This session has ended.</div>
                        @endif

                        <div class="mb-4">
                            <div class="d-inline-block bg-light px-4 py-2 rounded-3 border">
                                <span class="text-muted small d-block text-uppercase fw-bold mb-1">CODE</span>
                                <span
                                    class="h1 font-monospace fw-bold text-secondary mb-0">{{ $session->referral_code }}</span>
                            </div>
                        </div>

                        <hr class="my-4 opacity-10">

                        <form action="{{ route('admin.session.delete', $session->id) }}" method="POST"
                            onsubmit="return confirm('Delete session?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger px-4 py-2 fw-medium w-100">Delete
                                Session</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($session->isActive())
        <script>
            const qrImage = document.getElementById('dynamic-qr-image');
            const qrLoading = document.getElementById('qr-loading');
            // Point to the ADMIN route for QR data
            const qrDataUrl = "{{ route('admin.session.qr_data', $session->id) }}";

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
                    .catch(err => console.error("Failed to fetch QR:", err));
            }

            fetchNewQr();
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
        }
    </style>
@endsection

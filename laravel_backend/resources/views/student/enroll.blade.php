@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 pt-4 text-center">
                    <h4 class="fw-bold text-primary"><i class="fas fa-camera me-2"></i>Face Enrollment</h4>
                    <p class="text-muted small">Please ensure good lighting and look directly at the camera.</p>
                </div>
                <div class="card-body text-center p-4">

                    <div
                        class="alert alert-info border-0 d-flex align-items-center justify-content-center mb-4 bg-light-info">
                        <div class="text-start small">
                            <ul class="mb-0 ps-3">
                                <li><i class="fas fa-lightbulb text-warning me-1"></i> Ensure your face is evenly lit (no
                                    backlighting).</li>
                                <li><i class="fas fa-user text-primary me-1"></i> Look straight at the camera.</li>
                                <li><i class="fas fa-glasses text-dark me-1"></i> Remove glasses if they reflect glare.</li>
                            </ul>
                        </div>
                    </div>

                    <div id="loading" style="display: none;">
                        <p>Starting camera...</p>
                    </div>

                    <div id="webcam-container" style="display: none;">
                        <div class="position-relative d-inline-block">
                            <video id="webcam" autoplay muted playsinline
                                class="img-fluid rounded-3 border border-primary shadow-sm"
                                style="max-height: 400px; transform: scaleX(-1);"></video>
                            <div class="position-absolute top-50 start-50 translate-middle border border-white border-2 rounded-3"
                                style="width: 200px; height: 250px; opacity: 0.5; pointer-events: none;"></div>
                        </div>
                        <canvas id="canvas" class="d-none"></canvas>

                        <div class="mt-4">
                            <button id="capture-btn" class="btn btn-primary btn-lg px-5 shadow-sm fw-bold">
                                <i class="fas fa-camera me-2"></i>Capture & Enroll
                            </button>
                        </div>
                    </div>

                    <div id="status" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .bg-light-info {
            background-color: #e0f2f1;
            color: #00695c;
        }
    </style>
@endsection

@push('scripts')
    <script>
        const video = document.getElementById('webcam');
        const canvas = document.getElementById('canvas');
        const captureBtn = document.getElementById('capture-btn');
        const statusDiv = document.getElementById('status');
        const loadingDiv = document.getElementById('loading');
        const webcamContainer = document.getElementById('webcam-container');

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const ENROLL_URL = "{{ route('student.enroll.face') }}";

        async function startWebcam() {
            loadingDiv.style.display = 'block';
            statusDiv.innerHTML = '';
            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        width: {
                            ideal: 1280
                        },
                        height: {
                            ideal: 720
                        },
                        facingMode: "user"
                    },
                    audio: false
                });
                video.srcObject = stream;

                video.onloadedmetadata = () => {
                    loadingDiv.style.display = 'none';
                    webcamContainer.style.display = 'block';
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                };
            } catch (err) {
                console.error("Error accessing webcam:", err);
                loadingDiv.style.display = 'none';
                statusDiv.innerHTML =
                    `<div class="alert alert-danger"><i class="fas fa-video-slash me-2"></i>Error: Could not access webcam. Check permissions.</div>`;
            }
        }

        async function captureAndEnroll() {
            // Disable button during processing
            captureBtn.disabled = true;
            captureBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Processing...`;
            statusDiv.innerHTML = '';

            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            const imageBase64 = canvas.toDataURL('image/jpeg', 0.95); // High quality

            try {
                const response = await fetch(ENROLL_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        image: imageBase64
                    })
                });

                const data = await response.json();

                if (data.success) {
                    statusDiv.innerHTML =
                        `<div class="alert alert-success shadow-sm"><i class="fas fa-check-circle me-2"></i>${data.message} Redirecting...</div>`;
                    setTimeout(() => {
                        window.location.href = "{{ route('student.dashboard') }}";
                    }, 2000);
                } else {
                    // Show the specific error from Python (e.g., "Too dark")
                    statusDiv.innerHTML =
                        `<div class="alert alert-warning shadow-sm"><i class="fas fa-exclamation-circle me-2"></i>${data.message}</div>`;
                    resetButton();
                }
            } catch (err) {
                console.error("Error sending image:", err);
                statusDiv.innerHTML =
                    `<div class="alert alert-danger shadow-sm"><i class="fas fa-times-circle me-2"></i>Network error. Please try again.</div>`;
                resetButton();
            }
        }

        function resetButton() {
            captureBtn.disabled = false;
            captureBtn.innerHTML = `<i class="fas fa-camera me-2"></i>Capture & Enroll`;
        }

        startWebcam();
        captureBtn.addEventListener('click', captureAndEnroll);
    </script>
@endpush

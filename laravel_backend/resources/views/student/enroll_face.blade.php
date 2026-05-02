@extends('layouts.app')

@section('content')
    <div class="container py-4 font-sans-serif">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-header bg-primary text-white text-center py-4">
                        <h4 class="fw-bold mb-0"><i class="fas fa-user-plus me-2"></i>Enroll Face Data</h4>
                        <p class="mb-0 opacity-75 small">Register your face for secure attendance.</p>
                    </div>

                    <div class="card-body p-4 text-center">

                        <div
                            class="alert alert-info border-0 d-flex align-items-start text-start mb-4 bg-primary-subtle text-primary">
                            <i class="fas fa-info-circle mt-1 me-2 flex-shrink-0"></i>
                            <div class="small">
                                <strong>Instructions:</strong>
                                <ul class="mb-0 ps-3 mt-1">
                                    <li>Ensure you are in a well-lit area.</li>
                                    <li>Remove masks or sunglasses.</li>
                                    <li>Look directly at the camera.</li>
                                </ul>
                            </div>
                        </div>

                        <div id="status-message" class="alert d-none fw-medium text-center mb-3" role="alert"></div>

                        <div class="position-relative rounded-4 overflow-hidden bg-dark mb-4 shadow-sm mx-auto"
                            style="max-width: 480px; aspect-ratio: 4/3;">
                            <video id="video" class="w-100 h-100 object-fit-cover" autoplay muted playsinline></video>
                            <canvas id="canvas" class="d-none"></canvas>

                            <div class="position-absolute top-50 start-50 translate-middle border border-2 border-white opacity-50 rounded-3"
                                style="width: 200px; height: 250px; pointer-events: none;"></div>

                            <div id="loading-overlay"
                                class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 d-flex align-items-center justify-content-center d-none">
                                <div class="d-flex flex-column align-items-center text-light">
                                    <div class="spinner-border mb-2" role="status"></div>
                                    <div class="small fw-bold">Processing...</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="button" id="capture-btn" class="btn btn-primary btn-lg fw-bold">
                                <i class="fas fa-camera me-2"></i> Capture & Enroll
                            </button>
                            <a href="{{ route('student.dashboard') }}" class="btn btn-light text-muted">Cancel</a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const captureBtn = document.getElementById('capture-btn');
            const loadingOverlay = document.getElementById('loading-overlay');
            const statusMessage = document.getElementById('status-message');

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                "{{ csrf_token() }}";

            // 1. Start Camera
            navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user',
                        width: {
                            ideal: 1280
                        },
                        height: {
                            ideal: 720
                        }
                    }
                })
                .then(stream => {
                    video.srcObject = stream;
                })
                .catch(err => {
                    console.error("Camera Error:", err);
                    showStatus('Camera access denied. Please allow permissions.', 'danger');
                    captureBtn.disabled = true;
                });

            // 2. Handle Capture
            captureBtn.addEventListener('click', async () => {
                statusMessage.classList.add('d-none');
                captureBtn.disabled = true;
                loadingOverlay.classList.remove('d-none');

                // Draw video frame to canvas
                // canvas.width = video.videoWidth;
                // canvas.height = video.videoHeight;
                // const context = canvas.getContext('2d');
                // context.drawImage(video, 0, 0, canvas.width, canvas.height);

                // Convert to Base64
                // const imageBase64 = canvas.toDataURL('image/jpeg', 0.95).split(',')[1];

                canvas.width = 640;
                canvas.height = 480;
                const context = canvas.getContext('2d');
                context.drawImage(video, 0, 0, canvas.width, canvas.height);

                // 2. Compress the JPEG quality to 70% to drastically reduce text size
                const imageBase64 = canvas.toDataURL('image/jpeg', 0.70).split(',')[1];

                try {
                    const response = await fetch("{{ route('student.enroll.face') }}", {
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
                        showStatus(data.message, 'success');
                        setTimeout(() => {
                            window.location.href = "{{ route('student.dashboard') }}";
                        }, 2000);
                    } else {
                        // Here is where we show the specific error (e.g., "Image is too blurry")
                        showStatus(data.message, 'warning');
                        captureBtn.disabled = false;
                    }
                } catch (error) {
                    console.error(error);
                    showStatus('A network error occurred. Please try again.', 'danger');
                    captureBtn.disabled = false;
                } finally {
                    loadingOverlay.classList.add('d-none');
                }
            });

            function showStatus(message, type) {
                statusMessage.textContent = message;
                statusMessage.className =
                    `alert alert-${type} fw-medium text-center animate__animated animate__fadeIn mb-3`;
                statusMessage.classList.remove('d-none');
            }
        });
    </script>

    <style>
        .font-sans-serif {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .bg-primary-subtle {
            background-color: #cfe2ff;
            color: #084298;
        }

        #video {
            transform: scaleX(-1);
            /* Mirror effect */
        }
    </style>
@endsection

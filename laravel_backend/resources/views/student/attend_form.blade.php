@extends('layouts.app')

@section('content')
    <form id="attendance-form" class="d-none">
        @csrf
        <input type="hidden" name="encrypted_token" value="{{ $encryptedToken }}">
        <input type="hidden" name="_token_value" value="{{ $formToken }}">
    </form>

    <div class="container py-4 font-sans-serif">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-header bg-primary text-white text-center py-4">
                        <h5 class="fw-bold mb-1 opacity-75 text-uppercase letter-spacing-1">
                            {{ $session->course->course_name }}</h5>
                        <h2 class="fw-bold mb-0">{{ $session->session_title }}</h2>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <div id="status-message" class="alert d-none fw-medium text-center" role="alert"></div>

                        <div id="step-1-face">
                            <div class="text-center mb-4">
                                <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3"
                                    style="width: 64px; height: 64px;">
                                    <i class="fas fa-user-check fa-2x"></i>
                                </div>
                                <h4 class="fw-bold mb-2">Verify Your Identity</h4>
                                <p class="text-muted">Please look directly at the camera.</p>
                            </div>

                            <div class="position-relative rounded-4 overflow-hidden bg-dark mb-4 shadow-sm"
                                style="padding-top: 75%;">
                                <video id="video" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover"
                                    autoplay muted playsinline></video>
                                <canvas id="canvas" class="d-none"></canvas>
                                <div id="loading-overlay"
                                    class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 d-flex align-items-center justify-content-center d-none">
                                    <div class="d-flex flex-column align-items-center text-light">
                                        <div class="spinner-border mb-2" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <div class="small fw-bold">Verifying...</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="button" id="verify-btn" class="btn btn-primary btn-lg fw-bold">
                                    <i class="fas fa-camera me-2"></i> Scan Face & Mark Attendance
                                </button>
                            </div>
                        </div>

                        <div id="step-2-success" class="d-none text-center py-5 animate__animated animate__fadeIn">
                            <div class="mb-4">
                                <i class="fas fa-check-circle text-success display-1"></i>
                            </div>
                            <h3 class="fw-bold text-success mb-3">Attendance Marked!</h3>
                            <p class="text-muted mb-4 fs-5" id="success-detail">You are present for this session.</p>
                            <a href="{{ route('student.dashboard') }}" class="btn btn-outline-primary fw-bold px-4">
                                <i class="fas fa-home me-2"></i> Return to Dashboard
                            </a>
                        </div>
                    </div>

                    <div class="card-footer bg-light text-center py-3 small text-muted">
                        Session ID: <span class="font-monospace fw-bold">{{ $session->referral_code }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const verifyBtn = document.getElementById('verify-btn');
            const loadingOverlay = document.getElementById('loading-overlay');
            const statusMessage = document.getElementById('status-message');
            const successDetail = document.getElementById('success-detail');
            const step1 = document.getElementById('step-1-face');
            const step2 = document.getElementById('step-2-success');

            // 1. Start Camera
            navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user',
                        width: {
                            ideal: 640
                        },
                        height: {
                            ideal: 480
                        }
                    }
                })
                .then(stream => {
                    video.srcObject = stream;
                })
                .catch(err => {
                    showStatus('Camera access denied. Please allow permissions.', 'danger');
                    verifyBtn.disabled = true;
                });

            // 2. Handle Click -> Get Location -> Send Request
            verifyBtn.addEventListener('click', async () => {
                statusMessage.classList.add('d-none');
                verifyBtn.disabled = true;
                loadingOverlay.classList.remove('d-none');

                // --- STEP A: GET LOCATION ---
                if (!navigator.geolocation) {
                    showStatus("Geolocation is not supported by your browser.", 'danger');
                    loadingOverlay.classList.add('d-none');
                    verifyBtn.disabled = false;
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    // Success Callback
                    async (position) => {
                            const lat = position.coords.latitude;
                            const long = position.coords.longitude;

                            await sendVerificationRequest(lat, long);
                        },
                        // Error Callback
                        (error) => {
                            console.error("Location Error:", error);
                            showStatus(
                                "Location access denied. You must allow location to mark attendance.",
                                'danger');
                            loadingOverlay.classList.add('d-none');
                            verifyBtn.disabled = false;
                        }, {
                            enableHighAccuracy: true
                        } // Require GPS
                );
            });

            async function sendVerificationRequest(lat, long) {
                // Capture Image
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const context = canvas.getContext('2d');
                context.drawImage(video, 0, 0, 640, 480); // Resize for mobile
                const imageBase64 = canvas.toDataURL('image/jpeg', 0.85).split(',')[1];

                const tokenInput = document.querySelector('#attendance-form input[name="encrypted_token"]');
                const formInput = document.querySelector('#attendance-form input[name="_token_value"]');
                const csrfInput = document.querySelector('#attendance-form input[name="_token"]');

                try {
                    const response = await fetch("{{ route('student.mark.attendance') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfInput.value,
                            'ngrok-skip-browser-warning': 'true'
                        },
                        body: JSON.stringify({
                            encrypted_token: tokenInput.value,
                            _token: formInput.value,
                            image: imageBase64,
                            latitude: lat, // Send location
                            longitude: long // Send location
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        step1.classList.add('d-none');
                        step2.classList.remove('d-none');
                        if (data.message.toLowerCase().includes('late')) {
                            successDetail.innerHTML =
                                'You have been marked as <span class="text-warning fw-bold">LATE</span>.';
                        } else {
                            successDetail.innerText = data.message;
                        }
                        if (video.srcObject) video.srcObject.getTracks().forEach(track => track.stop());
                    } else {
                        showStatus(data.message, 'danger');
                        verifyBtn.disabled = false;
                    }
                } catch (error) {
                    showStatus('Connection error. Please try again.', 'danger');
                    verifyBtn.disabled = false;
                } finally {
                    loadingOverlay.classList.add('d-none');
                }
            }

            function showStatus(message, type) {
                statusMessage.innerHTML = message;
                statusMessage.className =
                    `alert alert-${type} fw-medium text-center animate__animated animate__fadeIn`;
                statusMessage.classList.remove('d-none');
            }
        });
    </script>

    <style>
        .font-sans-serif {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .letter-spacing-1 {
            letter-spacing: 1px;
        }

        /* Mirror the video so it feels natural to the user */
        #video {
            object-fit: cover;
            transform: scaleX(-1);
        }
    </style>
@endsection

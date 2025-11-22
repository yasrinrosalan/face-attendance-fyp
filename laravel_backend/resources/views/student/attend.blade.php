@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header fs-4">
                    Marking Attendance for: {{ $session->session_title }}
                    <br>
                    <small class="text-muted">{{ $session->course->course_name }}</small>
                </div>
                <div class="card-body text-center">

                    <div id="loading" style="display: none;">
                        <p>Starting camera...</p>
                    </div>

                    <div id="webcam-container" style="display: none;">
                        <video id="webcam" autoplay muted playsinline class="img-fluid rounded border border-secondary"
                            style="transform: scaleX(-1);"></video>
                        <canvas id="canvas" class="d-none"></canvas>

                        <p class="fs-5 mt-3" id="instruction">Please look directly at the camera to verify your identity.
                        </p>

                        <button id="capture-btn" class="btn btn-primary btn-lg mt-2">
                            Verify My Face
                        </button>
                    </div>

                    <div id="status" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
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
        const referralCode = "{{ $session->referral_code }}";
        const attendanceToken = "{{ $attendance_token }}";
        const ATTENDANCE_URL = "{{ route('student.mark.attendance') }}";

        async function startWebcam() {
            loadingDiv.style.display = 'block';
            statusDiv.innerHTML = '';
            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        width: 640,
                        height: 480
                    },
                    audio: false
                });
                video.srcObject = stream;
                loadingDiv.style.display = 'none';
                webcamContainer.style.display = 'block';
            } catch (err) {
                console.error("Error accessing webcam:", err);
                loadingDiv.style.display = 'none';
                statusDiv.innerHTML =
                    `<div class="alert alert-danger">Error: Could not access webcam. Please allow camera permissions.</div>`;
            }
        }

        async function captureAndVerify() {
            statusDiv.innerHTML = `<div class="alert alert-info">Verifying... Please wait.</div>`;
            captureBtn.disabled = true;

            const context = canvas.getContext('2d');
            // We draw the image normally (not mirrored) for the server,
            // so the AI sees your face correctly.
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            const imageBase64 = canvas.toDataURL('image/jpeg', 0.9);

            try {
                const response = await fetch(ATTENDANCE_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        image: imageBase64,
                        referral_code: referralCode,
                        _token: attendanceToken
                    })
                });

                const data = await response.json();

                if (data.success) {
                    statusDiv.innerHTML = `<div class="alert alert-success">${data.message} Redirecting...</div>`;
                    captureBtn.style.display = 'none';
                    setTimeout(() => {
                        window.location.href = "{{ route('student.dashboard') }}";
                    }, 2000);
                } else {
                    statusDiv.innerHTML = `<div class="alert alert-danger">${data.message} Please try again.</div>`;
                    captureBtn.disabled = false;
                }

            } catch (err) {
                console.error("Error:", err);
                if (err.response && err.response.status === 419) {
                    statusDiv.innerHTML = `<div class="alert alert-danger">Session expired. Please reload.</div>`;
                } else {
                    statusDiv.innerHTML = `<div class="alert alert-danger">An error occurred. Please try again.</div>`;
                }
                captureBtn.disabled = false;
            }
        }

        startWebcam();
        captureBtn.addEventListener('click', captureAndVerify);
    </script>
@endpush

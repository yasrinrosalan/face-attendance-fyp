@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header fs-4">Face Enrollment</div>
                <div class="card-body text-center">

                    <div id="loading" style="display: none;">
                        <p>Starting camera...</p>
                    </div>

                    <div id="webcam-container" style="display: none;">
                        <p>Please look directly at the camera and hold still.</p>
                        <video id="webcam" autoplay muted playsinline class="img-fluid rounded"></video>
                        <canvas id="canvas" class="d-none"></canvas>
                        <button id="capture-btn" class="btn btn-primary btn-lg mt-3">Capture & Enroll Face</button>
                    </div>

                    <div id="status" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Get all the HTML elements we need
        const video = document.getElementById('webcam');
        const canvas = document.getElementById('canvas');
        const captureBtn = document.getElementById('capture-btn');
        const statusDiv = document.getElementById('status');
        const loadingDiv = document.getElementById('loading');
        const webcamContainer = document.getElementById('webcam-container');

        // Get the CSRF token from the meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Function to start the webcam
        async function startWebcam() {
            loadingDiv.style.display = 'block';
            statusDiv.innerHTML = '';
            try {
                // Request access to the user's camera
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        width: 640,
                        height: 480
                    },
                    audio: false
                });

                // If successful, show the video stream
                video.srcObject = stream;
                loadingDiv.style.display = 'none';
                webcamContainer.style.display = 'block';

                // Set canvas dimensions to match video
                video.addEventListener('loadedmetadata', () => {
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                });
            } catch (err) {
                // Handle errors (e.g., user denies camera access)
                console.error("Error accessing webcam:", err);
                loadingDiv.style.display = 'none';
                statusDiv.innerHTML =
                    `<div class="alert alert-danger">Error: Could not access webcam. Please allow camera permissions.</div>`;
            }
        }

        // Function to capture a photo and send it to the server
        async function captureAndEnroll() {
            // 1. Update UI to show processing
            statusDiv.innerHTML = `<div class="alert alert-info">Processing... Please wait.</div>`;
            captureBtn.disabled = true;

            // 2. Draw the current video frame onto the hidden canvas
            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            // 3. Get the image from the canvas as a Base64 data URL
            // format: "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQ..."
            const imageBase64 = canvas.toDataURL('image/jpeg', 0.9); // 90% quality

            try {
                // 4. Send the image to our Laravel backend
                const response = await fetch("{{ route('student.enroll.face') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken // Include the CSRF token
                    },
                    body: JSON.stringify({
                        image: imageBase64
                    })
                });

                const data = await response.json();

                // 5. Handle the response from the server
                if (data.success) {
                    statusDiv.innerHTML =
                        `<div class="alert alert-success">${data.message} You will be redirected.</div>`;
                    // Redirect to dashboard after 2 seconds
                    setTimeout(() => {
                        window.location.href = "{{ route('student.dashboard') }}";
                    }, 2000);
                } else {
                    statusDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                    captureBtn.disabled = false;
                }
            } catch (err) {
                console.error("Error sending image:", err);
                statusDiv.innerHTML = `<div class="alert alert-danger">An error occurred. Please try again.</div>`;
                captureBtn.disabled = false;
            }
        }

        // --- Event Listeners ---

        // Start the webcam when the page loads
        startWebcam();

        // Add click event to the capture button
        captureBtn.addEventListener('click', captureAndEnroll);
    </script>
@endpush

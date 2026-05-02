@extends('layouts.app')

@section('content')
    <!-- We override the main background just for this page to make it cinematic -->
    <div class="scanner-page-container d-flex flex-column align-items-center">

        <!-- Top Navigation Bar -->
        <div class="p-4 d-flex justify-content-between align-items-center w-100 z-3 position-relative"
            style="max-width: 600px;">
            <a href="{{ route('student.dashboard') }}"
                class="btn-glassmorphism rounded-circle d-flex align-items-center justify-content-center text-decoration-none shadow-sm">
                <i class="fas fa-arrow-left fa-lg text-white"></i>
            </a>
            <h5 class="text-white fw-bold m-0 tracking-wide">Scan Class QR</h5>
            <div style="width: 45px;"></div> <!-- Spacer to keep title centered -->
        </div>

        <!-- The Camera Viewfinder Wrapper -->
        <div class="flex-grow-1 d-flex flex-column justify-content-center align-items-center w-100 position-relative z-2">

            <div
                class="scanner-wrapper position-relative shadow-lg rounded-4 overflow-hidden border border-secondary border-opacity-25">

                <!-- The actual video feed will be injected here by the JS -->
                <div id="reader" class="w-100 h-100 bg-dark"></div>

                <!-- Custom Premium Overlay -->
                <div
                    class="scanner-overlay position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center">
                    <div class="target-box position-relative">
                        <!-- Crisp White Corner Markers -->
                        <div class="corner corner-tl"></div>
                        <div class="corner corner-tr"></div>
                        <div class="corner corner-bl"></div>
                        <div class="corner corner-br"></div>

                        <!-- Animated Blue Scanning Laser -->
                        <div class="laser-line"></div>
                    </div>
                </div>

            </div>

            <p class="text-white-50 small mt-4 text-center px-4 mb-0 fw-medium">
                <i class="fas fa-qrcode me-2 text-primary"></i> Point your camera at the screen. Scanning is automatic.
            </p>
        </div>
    </div>

    <!-- Include the HTML5-QRCode Library -->
    <script src="https://unpkg.com/html5-qrcode"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            // Use the lower-level API to avoid the ugly default UI
            const html5QrCode = new Html5Qrcode("reader");

            function onScanSuccess(decodedText, decodedResult) {
                // Stop scanning immediately on success to prevent multiple triggers
                html5QrCode.stop().then(() => {
                    // Redirect to the URL hidden inside the QR code
                    window.location.href = decodedText;
                }).catch(err => {
                    // Fallback redirect if stop fails
                    window.location.href = decodedText;
                });
            }

            // Fetch cameras and start automatically
            Html5Qrcode.getCameras().then(devices => {
                if (devices && devices.length) {
                    // Default to the first camera (usually the laptop webcam)
                    let cameraId = devices[0].id;

                    // If on mobile, intelligently search for the "back" or "environment" camera
                    for (let i = 0; i < devices.length; i++) {
                        let label = devices[i].label.toLowerCase();
                        if (label.includes("back") || label.includes("rear") || label.includes(
                                "environment")) {
                            cameraId = devices[i].id;
                            break;
                        }
                    }

                    // Start the camera without bounds (makes scanning faster)
                    // Our CSS handles the visual bounds instead
                    html5QrCode.start(
                        cameraId, {
                            fps: 15
                        },
                        onScanSuccess,
                        (errorMessage) => {
                            /* Ignore constant background scan errors */ }
                    ).catch(err => {
                        alert("Error starting camera. Please ensure permissions are granted.");
                    });
                } else {
                    alert("No cameras found on this device.");
                }
            }).catch(err => {
                alert("Camera access denied or not supported by this browser.");
            });
        });
    </script>

    <style>
        /* Absolute reset for the scanner page background */
        body,
        html {
            background-color: #0f172a !important;
            /* Premium Dark Slate */
        }

        .scanner-page-container {
            min-height: calc(100vh - 60px);
            background: radial-gradient(circle at center, #1e293b 0%, #0f172a 100%);
        }

        .tracking-wide {
            letter-spacing: 0.5px;
        }

        /* Glassmorphism Back Button */
        .btn-glassmorphism {
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            transition: all 0.2s ease;
        }

        .btn-glassmorphism:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        /* The Scanner Shape */
        .scanner-wrapper {
            width: 90%;
            max-width: 400px;
            aspect-ratio: 3/4;
            /* Creates a nice vertical rectangle for mobile/desktop */
            background-color: #000;
        }

        /* Force the injected video to fill our nice rounded box */
        #reader video {
            object-fit: cover !important;
            width: 100% !important;
            height: 100% !important;
        }

        /* Target Box Overlay */
        .scanner-overlay {
            pointer-events: none;
            /* Allows clicks to pass through to the video if needed */
            background: rgba(0, 0, 0, 0.4);
            /* Darkens the outer edges */
        }

        .target-box {
            width: 250px;
            height: 250px;
            background: transparent;
            box-shadow: 0 0 0 4000px rgba(0, 0, 0, 0.4);
            /* Creates the "cutout" effect */
            border-radius: 16px;
        }

        /* White Corner Brackets */
        .corner {
            position: absolute;
            width: 40px;
            height: 40px;
            border-color: #ffffff;
            border-style: solid;
            border-radius: 10px;
        }

        .corner-tl {
            top: -2px;
            left: -2px;
            border-width: 4px 0 0 4px;
            border-top-left-radius: 16px;
            border-bottom-right-radius: 0;
        }

        .corner-tr {
            top: -2px;
            right: -2px;
            border-width: 4px 4px 0 0;
            border-top-right-radius: 16px;
            border-bottom-left-radius: 0;
        }

        .corner-bl {
            bottom: -2px;
            left: -2px;
            border-width: 0 0 4px 4px;
            border-bottom-left-radius: 16px;
            border-top-right-radius: 0;
        }

        .corner-br {
            bottom: -2px;
            right: -2px;
            border-width: 0 4px 4px 0;
            border-bottom-right-radius: 16px;
            border-top-left-radius: 0;
        }

        /* Animated Laser Line */
        .laser-line {
            position: absolute;
            top: 0;
            left: 5%;
            width: 90%;
            height: 2px;
            background: #3b82f6;
            box-shadow: 0 0 12px 3px rgba(59, 130, 246, 0.8);
            animation: scan 2.5s infinite ease-in-out;
        }

        @keyframes scan {

            0%,
            100% {
                top: 5%;
                opacity: 0;
            }

            10% {
                opacity: 1;
            }

            50% {
                top: 95%;
            }

            90% {
                opacity: 1;
            }
        }
    </style>
@endsection

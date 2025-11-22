@extends('layouts.app')

@section('content')
    <div class="row justify-content-center align-items-center" style="min-height: 60vh;">
        <div class="col-md-6 col-lg-5">

            <div class="card shadow-sm border-0 text-center p-4">
                <div class="card-body">

                    @if ($student->face_template_path == null)
                        <div class="mb-4">
                            <div class="avatar-circle bg-light-danger text-danger mx-auto mb-3">
                                <i class="fas fa-user-times fa-3x"></i>
                            </div>
                            <h3 class="fw-bold text-dark">Face Not Enrolled</h3>
                            <p class="text-muted">
                                You have not set up your face data yet. You must enroll to mark your attendance.
                            </p>
                        </div>

                        <a href="{{ route('student.enroll.form') }}" class="btn btn-primary btn-lg w-100 shadow-sm">
                            <i class="fas fa-camera me-2"></i> Enroll Face Now
                        </a>
                    @elseif($student->requesting_face_change)
                        <div class="mb-4">
                            <div class="avatar-circle bg-light-info text-info mx-auto mb-3">
                                <i class="fas fa-user-clock fa-3x"></i>
                            </div>
                            <h3 class="fw-bold text-dark">Request Pending</h3>
                            <p class="text-muted">
                                You have requested to reset your enrollment. Please wait for an administrator to approve
                                your request.
                            </p>
                        </div>

                        <button class="btn btn-light text-muted btn-lg w-100" disabled>
                            <i class="fas fa-hourglass-half me-2"></i> Awaiting Approval
                        </button>
                    @else
                        <div class="mb-4">
                            <div class="avatar-circle bg-light-success text-success mx-auto mb-3">
                                <i class="fas fa-user-check fa-3x"></i>
                            </div>
                            <h3 class="fw-bold text-dark">You are Enrolled</h3>
                            <p class="text-muted px-3">
                                Your biometric data is registered and active. You can now use the attendance scanner.
                            </p>
                            <div class="badge bg-light text-dark border mt-2 px-3 py-2">
                                Student ID: {{ Auth::user()->student_id }}
                            </div>
                        </div>

                        <hr class="my-4">

                        <p class="small text-muted mb-3">Need to update your photo?</p>

                        <form action="{{ route('student.request.face.change') }}" method="POST"
                            onsubmit="return confirm('Are you sure? This will notify the admin.');">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary w-100">
                                Request to Change Photo
                            </button>
                        </form>
                    @endif

                </div>
            </div>

            <div class="text-center mt-4">
                <a href="{{ route('student.dashboard') }}" class="text-decoration-none text-muted small">
                    <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>

        </div>
    </div>

    <style>
        /* Circular Icon Backgrounds */
        .avatar-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Subtle Background Colors */
        .bg-light-success {
            background-color: #d1e7dd;
        }

        .text-success {
            color: #0f5132 !important;
        }

        .bg-light-danger {
            background-color: #f8d7da;
        }

        .text-danger {
            color: #842029 !important;
        }

        .bg-light-info {
            background-color: #cff4fc;
        }

        .text-info {
            color: #055160 !important;
        }
    </style>
@endsection

@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-7">

            <div class="card mb-4">
                <div
                    class="card-header fs-5
                @if ($student->face_template_path == null) bg-warning {{-- Removed text-dark as Bootstrap handles contrast --}}
                @elseif($student->requesting_face_change) bg-info {{-- Removed text-dark --}}
                @else bg-success text-white @endif">
                    Enrollment Status
                </div>
                <div class="card-body text-center">

                    @if ($student->face_template_path == null)
                        <div class="alert alert-warning fs-5">
                            <strong>Action Required:</strong> You must enroll your face before you can attend any session.
                        </div>
                        <a href="{{ route('student.enroll.form') }}" class="btn btn-primary btn-lg">
                            Enroll Your Face Now
                        </a>
                    @elseif($student->requesting_face_change)
                        <div class="alert alert-info fs-5">
                            <strong>Request Pending:</strong> You have requested to change your face enrollment.
                            <br>
                            An administrator is reviewing your request.
                        </div>
                        <button class="btn btn-secondary btn-lg" disabled>
                            Request Sent
                        </button>
                    @else
                        <div class="alert alert-success fs-5">
                            <strong>You are enrolled.</strong> Your face is registered with the system.
                        </div>
                        <form action="{{ route('student.request.face.change') }}" method="POST"
                            onsubmit="return confirm('Are you sure you want to request an enrollment change? You must wait for admin approval.')">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-lg">
                                Request Face Change
                            </button>
                        </form>
                    @endif

                </div>
            </div>

        </div>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    <div class="text-center">
        <p><a href="{{ route('lecturer.dashboard') }}">&larr; Back to Dashboard</a></p>

        <h1 class="display-4">{{ $session->session_title }}</h1>
        <p class="lead">{{ $session->course->course_name }}</p>

        <div class="row justify-content-center mt-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <p class="fs-4">Scan the QR code with your phone or enter the code manually:</p>

                        <div class="my-3 p-3 d-inline-block bg-white rounded">
                            {!! QrCode::size(300)->generate($attendance_url) !!}
                        </div>

                        <h2 class="display-3" style="letter-spacing: 0.1em;">
                            <kbd class="bg-secondary">{{ $session->referral_code }}</kbd> {{-- Changed bg-light text-dark to bg-secondary --}}
                        </h2>

                        <p class="mt-3 text-muted">
                            This session is
                            @if ($session->isActive())
                                <strong class="text-success">ACTIVE</strong> until {{ $session->ends_at->format('g:i A') }}
                            @elseif(now()->gt($session->ends_at))
                                <strong class="text-secondary">EXPIRED</strong>
                            @else
                                <strong class="text-warning">PENDING</strong> (Starts at
                                {{ $session->starts_at->format('g:i A') }})
                            @endif
                        </p>
                        <hr>

                        <div class="d-flex justify-content-center gap-2">
                            <a href="{{ route('lecturer.attendance.export', $session->id) }}" class="btn btn-primary">
                                Export ({{ $session->attendance_records->count() }} Attended)
                            </a>
                            <form action="{{ route('lecturer.session.delete', $session->id) }}" method="POST"
                                onsubmit="return confirm('Are you sure?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Delete Session</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header fs-5">Create New Attendance Session</div>
                <div class="card-body">
                    <form action="{{ route('lecturer.session.create') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="course_id" class="form-label">Course</label>
                            <select name="course_id" id="course_id" class="form-select" required>
                                @foreach ($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->course_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="session_title" class="form-label">Session Title</label>
                            <input type="text" name="session_title" id="session_title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="starts_at" class="form-label">Starts At</label>
                            <input type="datetime-local" name="starts_at" id="starts_at" class="form-control"
                                value="{{ now()->format('Y-m-d\TH:i') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="ends_at" class="form-label">Ends At</label>
                            <input type="datetime-local" name="ends_at" id="ends_at" class="form-control"
                                value="{{ now()->addHour()->format('Y-m-d\TH:i') }}" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Create Session</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header fs-5">My Attendance Sessions</div>
                <div class="card-body">
                    @forelse($courses as $course)
                        <h4 class="mt-3">{{ $course->course_name }}</h4>
                        @if ($course->attendance_sessions->isEmpty())
                            <p>No attendance sessions created for this course.</p>
                        @else
                            <ul class="list-group">
                                @foreach ($course->attendance_sessions->sortByDesc('starts_at') as $session)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $session->session_title }}</strong>
                                            <span class="badge bg-light text-dark ms-2">{{ $session->referral_code }}</span>
                                            <br>
                                            <small class="text-muted">
                                                {{ $session->starts_at->format('M d, g:i A') }} to
                                                {{ $session->ends_at->format('g:i A') }}
                                                -
                                                @if ($session->isActive())
                                                    <span class="text-success">Active</span>
                                                @elseif(now()->gt($session->ends_at))
                                                    <span class="text-secondary">Expired</span>
                                                @else
                                                    <span class="text-warning">Pending</span>
                                                @endif
                                            </small>
                                        </div>
                                        <div>
                                            <a href="{{ route('lecturer.session.show', $session->id) }}"
                                                class="btn btn-sm btn-primary">
                                                Show Code / QR ({{ $session->attendance_records->count() }} Attended)
                                            </a>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    @empty
                        <p>You have not created any courses.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

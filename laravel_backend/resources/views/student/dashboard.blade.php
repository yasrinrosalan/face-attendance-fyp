@extends('layouts.app')

<style>
    .calendar-table {
        width: 100%;
        border-collapse: collapse;
    }

    .calendar-table th,
    .calendar-table td {
        border: 1px solid #dee2e6;
        width: 14.28%;
        /* 100 / 7 */
        height: 80px;
        vertical-align: top;
        padding: 5px;
    }

    .calendar-table th {
        background-color: #f8f9fa;
        text-align: center;
        height: auto;
        padding: 10px 5px;
        font-weight: bold;
    }

    .calendar-day-number {
        font-weight: bold;
        font-size: 0.9rem;
    }

    .calendar-day-muted .calendar-day-number {
        color: #adb5bd;
        /* Muted color for days not in this month */
    }

    .calendar-day-today .calendar-day-number {
        /* Highlight today's date number with primary color */
        color: var(--bs-primary);
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        border-radius: 50%;
        display: inline-block;
        width: 28px;
        height: 28px;
        text-align: center;
        line-height: 28px;
    }

    .calendar-day-attended {
        /* Use the teal color for attended days */
        background-color: rgba(var(--bs-secondary-rgb), 0.7);
        color: white;
    }

    .calendar-day-attended .calendar-day-number {
        color: white;
        /* Make number white on attended days */
    }
</style>


@section('content')
    <div class="row">

        <div class="col-lg-7 mb-4">

            <div class="card mb-4">
                <div class="card-header fs-5">Mark Attendance</div>
                <div class="card-body">
                    @if ($student->face_template_path == null)
                        <div class="alert alert-warning text-center">
                            <strong>Action Required:</strong> You must
                            <a href="{{ route('student.enrollment.page') }}" class="alert-link">enroll your face</a>
                            before you can mark attendance.
                        </div>
                    @endif

                    <p>Enter the attendance code provided by your lecturer or scan the QR code.</p>
                    <hr>
                    <form action="{{ route('student.find.session') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="referral_code" class="form-label fs-4">Attendance Code</label>
                            <input type="text" name="referral_code" id="referral_code"
                                class="form-control form-control-lg text-uppercase" placeholder="e.g. A7B2N9" required
                                @if ($student->face_template_path == null) disabled @endif>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100"
                            @if ($student->face_template_path == null) disabled @endif>
                            Find Session
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header fs-5 d-flex justify-content-between align-items-center">
                    <span>My Attendance Calendar</span>
                    <span class="fs-5 fw-bold" style="color: var(--bs-primary);">{{ $today->format('F Y') }}</span>
                </div>
                <div class="card-body">
                    <table class="calendar-table">
                        <thead>
                            <tr>
                                <th>Sun</th>
                                <th>Mon</th>
                                <th>Tue</th>
                                <th>Wed</th>
                                <th>Thu</th>
                                <th>Fri</th>
                                <th>Sat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                @foreach ($days as $day)
                                    @php
                                        $class = 'calendar-day';
                                        $dayKey = $day->format('Y-m-d');
                                        $isToday = $day->format('Y-m-d') == $today->format('Y-m-d');
                                        $isThisMonth = $day->format('m') == $today->format('m');
                                        $isAttended = isset($attendedDates[$dayKey]);

                                        if (!$isThisMonth) {
                                            $class .= ' calendar-day-muted';
                                        }
                                        if ($isToday) {
                                            $class .= ' calendar-day-today';
                                        }
                                        if ($isAttended) {
                                            $class .= ' calendar-day-attended';
                                        }
                                    @endphp

                                    <td class="{{ $class }}">
                                        <div class="calendar-day-number">{{ $day->format('j') }}</div>
                                        @if ($isAttended)
                                            <span class="badge bg-white text-dark">Present</span>
                                        @endif
                                    </td>

                                    {{-- If this day is Saturday, close the row and start a new one --}}
                                    @if ($day->format('w') == 6 && !$loop->last)
                                        {{-- 6 = Saturday --}}
                            </tr>
                            <tr>
                                @endif
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5 mb-4">

            <div class="card mb-4">
                <div class="card-header fs-5">My Profile</div>
                <div class="card-body">
                    <h5 class="card-title" style="color: var(--bs-primary);">{{ $student->name }}</h5>
                    <p class="card-text text-muted">{{ $student->email }}</p>
                    <hr>

                    <h6 class="text-muted">Enrollment Status</h6>
                    @if ($student->face_template_path == null)
                        <p class="fs-5 fw-bold text-danger">❌ Not Enrolled</p>
                    @elseif($student->requesting_face_change)
                        <p class="fs-5 fw-bold text-info">⏳ Request Pending</p>
                    @else
                        <p class="fs-5 fw-bold text-success">✅ Enrolled</p>
                    @endif

                    <a href="{{ route('student.enrollment.page') }}" class="btn btn-outline-primary w-100">
                        Manage My Enrollment
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-header fs-5">My Courses Summary</div>
                <div class="card-body">
                    @if ($groupedRecords->isEmpty())
                        <p class="text-muted text-center">You have not attended any sessions yet.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($groupedRecords as $courseName => $records)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0" style="color: var(--bs-primary);">{{ $courseName }}</h6>
                                        <small class="text-muted">{{ $records->count() }} sessions attended</small>
                                    </div>
                                    <span class="badge bg-primary rounded-pill fs-6">{{ $records->count() }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <hr>

                    <div class="d-flex justify-content-between align-items-center px-2">
                        <h5 class="mb-0">Total Attended:</h5>
                        <h5 class="mb-0 text-primary fw-bold">{{ $totalAttended }}</h5>
                    </div>

                </div>
            </div>

        </div>
    </div>
@endsection

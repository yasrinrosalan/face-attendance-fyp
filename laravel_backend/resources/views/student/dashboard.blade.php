@extends('layouts.app')

<style>
    .calendar-wrapper {
        padding: 10px;
    }

    .calendar-table {
        width: 100%;
        border-collapse: separate;
        /* Allows spacing */
        border-spacing: 0 8px;
        /* Vertical spacing between rows */
    }

    .calendar-table th {
        font-size: 0.85rem;
        color: #6c757d;
        text-transform: uppercase;
        font-weight: 600;
        text-align: center;
        padding-bottom: 15px;
        border-bottom: 1px solid #f0f0f0;
    }

    .calendar-table td {
        text-align: center;
        vertical-align: middle;
        height: 50px;
        /* Fixed height for alignment */
        position: relative;
    }

    /* The Date Circle */
    .day-circle {
        width: 36px;
        height: 36px;
        line-height: 36px;
        border-radius: 50%;
        margin: 0 auto;
        font-weight: 500;
        color: #333;
        position: relative;
        transition: all 0.2s;
    }

    /* Muted Days (Previous/Next Month) */
    .day-muted {
        color: #cbd5e1;
    }

    /* Today's Date */
    .day-today {
        background-color: var(--bs-primary);
        color: white;
        box-shadow: 0 4px 10px rgba(32, 58, 141, 0.3);
    }

    /* Attendance Indicator (Small Dot) */
    .attendance-dot {
        width: 6px;
        height: 6px;
        background-color: #198754;
        /* Success Green */
        border-radius: 50%;
        position: absolute;
        bottom: 4px;
        left: 50%;
        transform: translateX(-50%);
    }

    /* If attended, change the circle background subtly */
    .day-attended {
        background-color: #f0fff4;
        /* Very light green */
        color: #198754;
        font-weight: 700;
    }
</style>

@section('content')
    <div class="row g-4">

        <div class="col-lg-8">

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-light rounded-circle p-3 me-3">
                            <i class="fas fa-qrcode fa-lg text-primary"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-0">Mark Attendance</h5>
                            <small class="text-muted">Enter the 6-character code from your lecturer.</small>
                        </div>
                    </div>

                    @if ($student->face_template_path == null)
                        <div class="alert alert-warning border-0 d-flex align-items-center" role="alert">
                            <i class="fas fa-exclamation-circle me-2 text-warning"></i>
                            <div>
                                <strong>Action Required:</strong> You must
                                <a href="{{ route('student.enrollment.page') }}"
                                    class="alert-link text-decoration-underline">enroll your face</a>
                                before marking attendance.
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('student.find.session') }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-md-8">
                            <div class="form-floating">
                                <input type="text" name="referral_code" id="referral_code"
                                    class="form-control text-uppercase fw-bold letter-spacing-1" placeholder="Code" required
                                    @if ($student->face_template_path == null) disabled @endif>
                                <label for="referral_code" class="text-muted">Session Code (e.g., A7B2N9)</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100 h-100 fw-bold shadow-sm"
                                @if ($student->face_template_path == null) disabled @endif>
                                Find Session <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div
                    class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0">Attendance Calendar</h5>
                    <span class="badge bg-light text-primary border px-3 py-2 rounded-pill fs-6">
                        {{ $today->format('F Y') }}
                    </span>
                </div>
                <div class="card-body p-4">
                    <div class="calendar-wrapper">
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
                                            $dayKey = $day->format('Y-m-d');
                                            $isToday = $day->format('Y-m-d') == $today->format('Y-m-d');
                                            $isThisMonth = $day->format('m') == $today->format('m');
                                            $isAttended = isset($attendedDates[$dayKey]);

                                            $circleClass = 'day-circle';
                                            if (!$isThisMonth) {
                                                $circleClass .= ' day-muted';
                                            }
                                            if ($isToday) {
                                                $circleClass .= ' day-today';
                                            }
                                            if ($isAttended && !$isToday) {
                                                $circleClass .= ' day-attended';
                                            }
                                        @endphp

                                        <td>
                                            <div class="{{ $circleClass }}">
                                                {{ $day->format('j') }}

                                                @if ($isAttended)
                                                    <div class="attendance-dot" title="Present"></div>
                                                @endif
                                            </div>
                                        </td>

                                        @if ($day->format('w') == 6 && !$loop->last)
                                </tr>
                                <tr>
                                    @endif
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center gap-4 mt-3 small text-muted">
                        <div class="d-flex align-items-center gap-2">
                            <div style="width: 8px; height: 8px; background-color: #198754; border-radius: 50%;"></div>
                            Present
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width: 8px; height: 8px; background-color: var(--bs-primary); border-radius: 50%;">
                            </div> Today
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">

            <div class="card border-0 shadow-sm mb-4 text-center">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center bg-light text-primary rounded-circle"
                            style="width: 80px; height: 80px; font-size: 2rem; font-weight: bold;">
                            {{ substr($student->name, 0, 1) }}
                        </div>
                    </div>
                    <h5 class="fw-bold mb-0">{{ $student->name }}</h5>
                    <p class="text-muted mb-3">{{ $student->student_id ?? 'No ID' }}</p>

                    @if ($student->face_template_path)
                        <div
                            class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill mb-4">
                            <i class="fas fa-check-circle me-1"></i> Face Data Enrolled
                        </div>
                    @else
                        <div
                            class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill mb-4">
                            <i class="fas fa-times-circle me-1"></i> Not Enrolled
                        </div>
                    @endif

                    <a href="{{ route('student.enrollment.page') }}" class="btn btn-outline-secondary w-100 btn-sm">
                        <i class="fas fa-cog me-1"></i> Manage Enrollment
                    </a>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-2">
                    <h6 class="fw-bold text-dark mb-0">My Attendance Summary</h6>
                </div>
                <div class="card-body p-0">
                    @if ($groupedRecords->isEmpty())
                        <div class="text-center py-4 px-4">
                            <p class="text-muted small mb-0">You haven't attended any sessions yet.</p>
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach ($groupedRecords as $courseName => $records)
                                <div
                                    class="list-group-item border-0 px-4 py-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0 text-primary fw-semibold" style="font-size: 0.9rem;">
                                            {{ $courseName }}</h6>
                                        <span class="text-muted small">Last:
                                            {{ $records->first()->attended_at->format('M d') }}</span>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">{{ $records->count() }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="bg-light p-3 text-center border-top rounded-bottom">
                        <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Total Sessions</small>
                        <div class="h2 fw-bold text-dark mb-0">{{ $totalAttended }}</div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <style>
        /* Page specific polish */
        .letter-spacing-1 {
            letter-spacing: 2px;
        }

        .bg-success-subtle {
            background-color: #d1e7dd;
        }

        .border-success-subtle {
            border-color: #a3cfbb;
        }

        .bg-danger-subtle {
            background-color: #f8d7da;
        }

        .border-danger-subtle {
            border-color: #f1aeb5;
        }
    </style>
@endsection

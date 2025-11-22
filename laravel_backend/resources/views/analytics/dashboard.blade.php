@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Performance Analytics</h4>
            <p class="text-muted small mb-0">Real-time insights into student attendance and engagement.</p>
        </div>

        <div class="d-flex gap-2">
            @if (Auth::user()->isAdmin())
                <div class="px-3 py-2 bg-primary text-white rounded-pill shadow-sm small fw-bold">
                    <i class="fas fa-globe me-2"></i>Admin View
                </div>
            @else
                <div class="px-3 py-2 bg-white text-primary border rounded-pill shadow-sm small fw-bold">
                    <i class="fas fa-user-tie me-2"></i>Lecturer View
                </div>
            @endif
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 overflow-hidden">
                <div class="card-body p-4 position-relative">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-uppercase text-muted small fw-bold mb-1">Total Sessions</p>
                            <h2 class="fw-bold text-dark mb-0">{{ count($attendanceOverTime['labels']) }}</h2>
                        </div>
                        <div class="bg-primary-subtle text-primary rounded-3 p-2">
                            <i class="fas fa-calendar-check fa-lg"></i>
                        </div>
                    </div>
                    <i class="fas fa-chart-line position-absolute text-primary opacity-10"
                        style="font-size: 6rem; bottom: -20px; right: -20px;"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 overflow-hidden">
                <div class="card-body p-4 position-relative">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-uppercase text-muted small fw-bold mb-1">At-Risk Students</p>
                            <h2 class="fw-bold text-danger mb-0">{{ $leastActiveStudents->count() }}</h2>
                        </div>
                        <div class="bg-danger-subtle text-danger rounded-3 p-2">
                            <i class="fas fa-user-clock fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 overflow-hidden">
                <div class="card-body p-4 position-relative">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-uppercase text-muted small fw-bold mb-1">Active Courses</p>
                            <h2 class="fw-bold text-success mb-0">{{ count($attendanceByCourse['labels']) }}</h2>
                        </div>
                        <div class="bg-success-subtle text-success rounded-3 p-2">
                            <i class="fas fa-book-open fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div
                    class="card-header bg-white border-bottom-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0">Attendance Trends</h6>
                    <button class="btn btn-sm btn-light border"><i class="fas fa-download me-1"></i> Export</button>
                </div>
                <div class="card-body px-4 pb-4">
                    <div style="position: relative; height: 350px; width: 100%;">
                        @if (empty($attendanceOverTime['labels']))
                            <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted">
                                <i class="fas fa-chart-area fa-3x mb-3 opacity-25"></i>
                                <p>No trend data available yet.</p>
                            </div>
                        @else
                            <canvas id="attendanceOverTimeChart"></canvas>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                    <h6 class="fw-bold text-danger mb-0">⚠️ At-Risk Students</h6>
                    <small class="text-muted">Lowest attendance counts</small>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($leastActiveStudents as $student)
                            <li class="list-group-item px-4 py-3 border-bottom-0">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-initial rounded-circle me-3 fw-bold text-white shadow-sm d-flex align-items-center justify-content-center"
                                            style="width: 36px; height: 36px; background-color: #dc3545;">
                                            {{ substr($student->name, 0, 1) }}
                                        </div>
                                        <div style="line-height: 1.2;">
                                            <div class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $student->name }}
                                            </div>
                                            <div class="text-muted" style="font-size: 0.75rem;">{{ $student->email }}</div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span
                                            class="h5 fw-bold text-danger mb-0 d-block">{{ $student->attendance_records_count }}</span>
                                        <span class="text-muted"
                                            style="font-size: 0.65rem; text-transform: uppercase;">Sessions</span>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted py-5 border-0">
                                <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                                <p class="mb-0">No at-risk students found.</p>
                            </li>
                        @endforelse
                    </ul>
                </div>
                <div class="card-footer bg-white border-top-0 text-center pb-3">
                    <a href="#" class="text-decoration-none small fw-bold text-primary">View All Students <i
                            class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                    <h6 class="fw-bold text-dark mb-0">Course Distribution</h6>
                </div>
                <div class="card-body p-4">
                    <div style="position: relative; height: 250px; width: 100%;">
                        @if (empty($attendanceByCourse['labels']))
                            <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted">
                                <i class="fas fa-chart-pie fa-3x mb-3 opacity-25"></i>
                                <p>No course data.</p>
                            </div>
                        @else
                            <canvas id="attendanceByCourseChart"></canvas>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100 bg-primary text-white"
                style="background: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-secondary) 100%);">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="fw-bold mb-2">System Health</h4>
                        <p class="mb-0 opacity-75">All systems operational. Biometric engine is online.</p>
                    </div>
                    <i class="fas fa-server fa-3x opacity-25"></i>
                </div>
            </div>
        </div>

    </div>

    <style>
        .bg-primary-subtle {
            background-color: #e0e7ff !important;
            color: #3730a3 !important;
        }

        .bg-success-subtle {
            background-color: #dcfce7 !important;
            color: #166534 !important;
        }

        .bg-danger-subtle {
            background-color: #fee2e2 !important;
            color: #991b1b !important;
        }

        .opacity-10 {
            opacity: 0.1;
        }

        .avatar-initial {
            font-size: 0.9rem;
        }
    </style>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // --- 1. Attendance Trends (Line Chart) ---
            const ctxLine = document.getElementById('attendanceOverTimeChart');
            if (ctxLine) {
                const ctx = ctxLine.getContext('2d');

                // Create a beautiful gradient
                const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(32, 58, 141, 0.2)'); // Brand Blue
                gradient.addColorStop(1, 'rgba(32, 58, 141, 0.0)');

                new Chart(ctxLine, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($attendanceOverTime['labels']) !!},
                        datasets: [{
                            label: 'Attendance',
                            data: {!! json_encode($attendanceOverTime['data']) !!},
                            borderColor: '#203A8D',
                            backgroundColor: gradient,
                            borderWidth: 2,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#203A8D',
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            fill: true,
                            tension: 0.4 // Smooth curves
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                padding: 10,
                                cornerRadius: 8,
                                displayColors: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    borderDash: [5, 5],
                                    color: '#e2e8f0'
                                },
                                ticks: {
                                    precision: 0
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }

            // --- 2. Course Distribution (Doughnut) ---
            const ctxDoughnut = document.getElementById('attendanceByCourseChart');
            if (ctxDoughnut) {
                new Chart(ctxDoughnut, {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode($attendanceByCourse['labels']) !!},
                        datasets: [{
                            data: {!! json_encode($attendanceByCourse['data']) !!},
                            backgroundColor: [
                                '#203A8D', // Brand Blue
                                '#0A7D87', // Brand Teal
                                '#64748b', // Slate
                                '#f59e0b', // Amber
                                '#10b981' // Emerald
                            ],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '75%', // Thin modern ring
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 8
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
@endpush

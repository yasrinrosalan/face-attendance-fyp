@extends('layouts.app')

@section('content')
    <div class="container py-4 font-sans-serif">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-3">
            <div>
                <h6 class="text-uppercase text-muted small fw-bold ls-1 mb-1">Overview</h6>
                <h3 class="fw-bold text-dark mb-1 tracking-tight">Performance Analytics</h3>
                <p class="text-muted small fw-medium mb-0">Real-time insights into student attendance and engagement.</p>
            </div>

            <div class="d-flex gap-2">
                @if (Auth::user()->isAdmin())
                    <div
                        class="px-4 py-2 bg-primary text-white rounded-pill shadow-sm small fw-bold d-flex align-items-center">
                        <i class="fas fa-globe me-2"></i>Admin View
                    </div>
                @else
                    <div
                        class="px-4 py-2 bg-white text-primary border border-light rounded-pill shadow-sm small fw-bold d-flex align-items-center">
                        <i class="fas fa-user-tie me-2"></i>Lecturer View
                    </div>
                @endif
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 overflow-hidden rounded-4 card-hover">
                    <div class="card-body p-4 position-relative">

                        <div class="d-flex justify-content-between align-items-start"
                            style="position: relative; z-index: 2;">
                            <div>
                                <p class="text-uppercase text-muted small fw-bold mb-1 ls-1">Total Sessions</p>
                                <h2 class="fw-bolder text-dark mb-0 display-6 tracking-tight">
                                    {{ count($attendanceOverTime['labels']) }}</h2>
                            </div>
                            <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                                <i class="fas fa-calendar-check fa-lg"></i>
                            </div>
                        </div>

                        <i class="fas fa-chart-line position-absolute text-primary"
                            style="font-size: 8rem; bottom: -20px; right: -10px; opacity: 0.0; z-index: 1; transform: rotate(-5deg);"></i>

                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 overflow-hidden rounded-4 card-hover">
                    <div class="card-body p-4 position-relative">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-uppercase text-muted small fw-bold mb-1 ls-1">At-Risk Students</p>
                                <h2 class="fw-bolder text-danger mb-0 display-6 tracking-tight">
                                    {{ $leastActiveStudents->count() }}</h2>
                            </div>
                            <div class="bg-danger bg-opacity-10 text-danger rounded-3 p-3">
                                <i class="fas fa-user-clock fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 overflow-hidden rounded-4 card-hover">
                    <div class="card-body p-4 position-relative">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-uppercase text-muted small fw-bold mb-1 ls-1">Active Courses</p>
                                <h2 class="fw-bolder text-success mb-0 display-6 tracking-tight">
                                    {{ count($attendanceByCourse['labels']) }}</h2>
                            </div>
                            <div class="bg-success bg-opacity-10 text-success rounded-3 p-3">
                                <i class="fas fa-book-open fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100 rounded-4">
                    <div
                        class="card-header bg-white border-bottom-0 pt-4 px-4 d-flex justify-content-between align-items-center rounded-top-4">
                        <h6 class="fw-bold text-dark mb-0">Attendance Trends</h6>
                        <button
                            class="btn btn-sm btn-light border shadow-sm fw-medium rounded-pill px-3 btn-hover-lift text-primary">
                            <i class="fas fa-download me-1"></i> Export
                        </button>
                    </div>
                    <div class="card-body px-4 pb-4 pt-2">
                        <div style="position: relative; height: 350px; width: 100%;">
                            @if (empty($attendanceOverTime['labels']))
                                <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted">
                                    <div class="bg-light rounded-circle p-4 mb-3">
                                        <i class="fas fa-chart-area fa-2x opacity-25"></i>
                                    </div>
                                    <p class="fw-medium">No trend data available yet.</p>
                                </div>
                            @else
                                <canvas id="attendanceOverTimeChart"></canvas>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100 rounded-4 flex-column d-flex">
                    <div class="card-header bg-white border-bottom border-light pt-4 px-4 pb-3 rounded-top-4">
                        <h6 class="fw-bold text-danger mb-1 d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle me-2"></i> At-Risk Students
                        </h6>
                        <small class="text-muted fw-medium">Lowest attendance counts</small>
                    </div>
                    <div class="card-body p-0 flex-grow-1">
                        <ul class="list-group list-group-flush h-100">
                            @forelse($leastActiveStudents as $student)
                                <li class="list-group-item px-4 py-3 border-bottom border-light">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-initial rounded-circle me-3 fw-bold text-white shadow-sm d-flex align-items-center justify-content-center"
                                                style="width: 40px; height: 40px; background-color: #dc3545;">
                                                {{ substr($student->name, 0, 1) }}
                                            </div>
                                            <div style="line-height: 1.3;">
                                                <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                                                    {{ $student->name }}</div>
                                                <div class="text-muted" style="font-size: 0.8rem;">{{ $student->email }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-end bg-light rounded px-2 py-1 border border-light">
                                            <span
                                                class="h5 fw-bolder text-danger mb-0 d-block lh-1">{{ $student->attendance_records_count }}</span>
                                            <span class="text-muted fw-bold"
                                                style="font-size: 0.6rem; text-transform: uppercase; letter-spacing: 0.5px;">Sessions</span>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li
                                    class="list-group-item text-center text-muted py-5 border-0 h-100 d-flex flex-column justify-content-center align-items-center">
                                    <i class="fas fa-check-circle text-success fa-3x mb-3 opacity-75"></i>
                                    <p class="mb-0 fw-medium">No at-risk students found.</p>
                                </li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="card-footer bg-white border-top border-light text-center py-3 rounded-bottom-4 mt-auto">
                        <a href="#" class="text-decoration-none small fw-bold text-primary link-hover">
                            View All Students <i class="fas fa-arrow-right ms-1 transition-all"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100 rounded-4">
                    <div class="card-header bg-white border-bottom-0 pt-4 px-4 rounded-top-4">
                        <h6 class="fw-bold text-dark mb-0">Course Distribution</h6>
                    </div>
                    <div class="card-body p-4 pt-2">
                        <div style="position: relative; height: 220px; width: 100%;">
                            @if (empty($attendanceByCourse['labels']))
                                <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted">
                                    <i class="fas fa-chart-pie fa-2x mb-2 opacity-25"></i>
                                    <p class="small fw-medium">No course data.</p>
                                </div>
                            @else
                                <canvas id="attendanceByCourseChart"></canvas>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100 bg-primary text-white overflow-hidden position-relative rounded-4 card-hover"
                    style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">

                    <div class="position-absolute top-0 end-0 bg-white opacity-10 rounded-circle"
                        style="width: 200px; height: 200px; margin-right: -50px; margin-top: -50px;"></div>
                    <div class="position-absolute bottom-0 start-0 bg-white opacity-10 rounded-circle"
                        style="width: 100px; height: 100px; margin-left: -20px; margin-bottom: -20px;"></div>

                    <div
                        class="card-body p-4 p-md-5 d-flex align-items-center justify-content-between position-relative z-1">
                        <div>
                            <span class="badge bg-white text-primary mb-3 px-3 py-2 rounded-pill fw-bold shadow-sm">
                                <i class="fas fa-check-circle me-1 text-success"></i> Operational
                            </span>
                            <h3 class="fw-bolder mb-2 tracking-tight">System Health</h3>
                            <p class="mb-0 text-white-50 fw-medium" style="font-size: 1.1rem;">All systems operational.
                                Biometric engine is online and responding.</p>
                        </div>
                        <i class="fas fa-server fa-4x opacity-25 me-md-4"></i>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <style>
        .font-sans-serif {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .tracking-tight {
            letter-spacing: -0.5px;
        }

        .ls-1 {
            letter-spacing: 0.5px;
        }

        .transition-all {
            transition: all 0.2s ease-in-out;
        }

        /* Card Hover Effects */
        .card-hover {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-hover:hover {
            transform: translateY(-3px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .08) !important;
        }

        /* Button Hover Lift */
        .btn-hover-lift {
            transition: all 0.2s ease-in-out;
        }

        .btn-hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        /* Link Hover Animation */
        .link-hover:hover i {
            transform: translateX(4px);
        }

        .avatar-initial {
            font-size: 1.1rem;
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
                gradient.addColorStop(0, 'rgba(32, 58, 141, 0.25)'); // Brand Blue
                gradient.addColorStop(1, 'rgba(32, 58, 141, 0.0)');

                new Chart(ctxLine, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($attendanceOverTime['labels'] ?? []) !!},
                        datasets: [{
                            label: 'Attendance',
                            data: {!! json_encode($attendanceOverTime['data'] ?? []) !!},
                            borderColor: '#2a5298',
                            backgroundColor: gradient,
                            borderWidth: 3,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#2a5298',
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            pointBorderWidth: 2,
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
                                backgroundColor: 'rgba(30, 41, 59, 0.9)',
                                padding: 12,
                                cornerRadius: 8,
                                displayColors: false,
                                titleFont: {
                                    size: 13,
                                    family: "'Inter', sans-serif"
                                },
                                bodyFont: {
                                    size: 14,
                                    weight: 'bold',
                                    family: "'Inter', sans-serif"
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                border: {
                                    display: false
                                },
                                grid: {
                                    color: '#f1f5f9',
                                    drawBorder: false,
                                },
                                ticks: {
                                    precision: 0,
                                    color: '#64748b',
                                    font: {
                                        family: "'Inter', sans-serif"
                                    }
                                }
                            },
                            x: {
                                border: {
                                    display: false
                                },
                                grid: {
                                    display: false,
                                    drawBorder: false,
                                },
                                ticks: {
                                    color: '#64748b',
                                    font: {
                                        family: "'Inter', sans-serif"
                                    }
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
                        labels: {!! json_encode($attendanceByCourse['labels'] ?? []) !!},
                        datasets: [{
                            data: {!! json_encode($attendanceByCourse['data'] ?? []) !!},
                            backgroundColor: [
                                '#1e3c72', // Dark Blue
                                '#2980b9', // Lighter Blue
                                '#64748b', // Slate
                                '#f59e0b', // Amber
                                '#10b981' // Emerald
                            ],
                            borderWidth: 0,
                            hoverOffset: 6
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
                                    boxWidth: 8,
                                    color: '#475569',
                                    font: {
                                        family: "'Inter', sans-serif",
                                        weight: '500'
                                    },
                                    padding: 20
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(30, 41, 59, 0.9)',
                                padding: 12,
                                cornerRadius: 8,
                                bodyFont: {
                                    size: 14,
                                    weight: 'bold',
                                    family: "'Inter', sans-serif"
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
@endpush

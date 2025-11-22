@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Support Tickets</h2>
            <p class="text-muted small mb-0">Manage and track issue reports submitted by users.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary shadow-sm fw-medium">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold text-primary mb-0">
                <i class="fas fa-inbox me-2"></i>Recent Reports
            </h5>
            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill">
                {{ $reports->total() }} Reports
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase small fw-bold text-secondary">
                        <tr>
                            <th class="px-4 py-3 border-0">Reporter Details</th>
                            <th class="px-4 py-3 border-0" style="width: 40%;">Issue Summary</th>
                            <th class="px-4 py-3 border-0">Date Submitted</th>
                            <th class="px-4 py-3 border-0">Status</th>
                            <th class="px-4 py-3 border-0 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-3 shadow-sm"
                                            style="background-color: {{ $report->user->isLecturer() ? 'var(--bs-primary)' : 'var(--bs-secondary)' }}">
                                            {{ substr($report->user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $report->user->name }}</div>
                                            <div class="small text-muted">{{ $report->user->email }}</div>
                                            <span class="badge bg-light text-dark border mt-1"
                                                style="font-size: 0.65rem; letter-spacing: 0.5px;">
                                                {{ strtoupper($report->user->role) }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="mb-1 fw-bold text-dark">{{ $report->subject }}</div>
                                    <p class="mb-0 text-muted small text-truncate" style="max-width: 350px;"
                                        title="{{ $report->message }}">
                                        {{ $report->message }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="d-flex flex-column">
                                        <span
                                            class="fw-medium text-dark">{{ $report->created_at->format('M d, Y') }}</span>
                                        <span class="small text-muted">{{ $report->created_at->format('h:i A') }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($report->status == 'pending')
                                        <span
                                            class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-2">
                                            <i class="fas fa-clock me-1"></i> Pending
                                        </span>
                                    @else
                                        <span
                                            class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2">
                                            <i class="fas fa-check-circle me-1"></i> Resolved
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-end">
                                    @if ($report->status == 'pending')
                                        <form action="{{ route('admin.report.resolve', $report->id) }}" method="POST">
                                            @csrf
                                            <button class="btn btn-sm btn-success shadow-sm px-3 fw-bold"
                                                title="Mark as Resolved">
                                                <i class="fas fa-check me-1"></i> Mark Done
                                            </button>
                                        </form>
                                    @else
                                        <button class="btn btn-sm btn-light text-muted border px-3" disabled>
                                            <i class="fas fa-lock me-1"></i> Closed
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center text-muted">
                                        <div class="bg-light rounded-circle p-3 mb-3">
                                            <i class="fas fa-check-double fa-2x text-success opacity-50"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark">All Clear!</h6>
                                        <p class="mb-0 small">No issue reports found in the system.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-white border-top-0 py-3 d-flex justify-content-end">
            {{ $reports->links() }}
        </div>
    </div>

    <style>
        .avatar-circle {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .bg-warning-subtle {
            background-color: #fffbeb !important;
            color: #b45309 !important;
            border-color: #fcd34d !important;
        }

        .bg-success-subtle {
            background-color: #ecfdf5 !important;
            color: #047857 !important;
            border-color: #6ee7b7 !important;
        }

        /* Override Pagination to fit card footer */
        .pagination {
            margin-bottom: 0;
        }
    </style>
@endsection

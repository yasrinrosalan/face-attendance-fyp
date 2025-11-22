<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Overview</h4>
        <p class="text-muted small mb-0">System administration and monitoring.</p>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('admin.reports') }}"
            class="btn btn-white border shadow-sm fw-medium text-secondary d-flex align-items-center">
            <i class="fas fa-inbox me-2 text-primary"></i> View Reports
        </a>

        <button class="btn btn-primary px-4 py-2 shadow-sm fw-medium" data-bs-toggle="modal"
            data-bs-target="#createUserModal">
            <i class="fas fa-user-plus me-2"></i>New User
        </button>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 card-stat"
            style="border-top: 4px solid var(--bs-primary) !important;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold mb-1">Total Users</h6>
                        <h2 class="fw-bold text-dark mb-0">{{ $users->total() }}</h2>
                    </div>
                    <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 50px; height: 50px;">
                        <i class="fas fa-users fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 card-stat" style="border-top: 4px solid #198754 !important;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold mb-1">Active Sessions</h6>
                        <h2 class="fw-bold text-dark mb-0">{{ $sessions->where('ends_at', '>=', now())->count() }}</h2>
                    </div>
                    <div class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 50px; height: 50px;">
                        <i class="fas fa-video fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 card-stat" style="border-top: 4px solid #0dcaf0 !important;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold mb-1">Total Sessions</h6>
                        <h2 class="fw-bold text-dark mb-0">{{ $sessions->total() }}</h2>
                    </div>
                    <div class="bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 50px; height: 50px;">
                        <i class="fas fa-history fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

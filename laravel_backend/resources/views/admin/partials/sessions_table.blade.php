<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light">
            <tr class="text-secondary small text-uppercase">
                <th class="px-4 py-3 border-0">Session Details</th>
                <th class="px-4 py-3 border-0">Lecturer</th>
                <th class="px-4 py-3 border-0">Status</th>
                <th class="px-4 py-3 border-0 text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sessions as $session)
                <tr>
                    <td class="px-4 py-3">
                        <div class="fw-bold text-dark">{{ $session->session_title }}</div>
                        <div class="small text-muted">{{ $session->course->course_name }}</div>
                        <span
                            class="badge bg-light text-dark border font-monospace mt-1">{{ $session->referral_code }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar-initial rounded-circle me-2 bg-light text-primary border d-flex align-items-center justify-content-center"
                                style="width: 32px; height: 32px; font-size: 0.8rem;">
                                {{ substr($session->course->lecturer->name, 0, 1) }}
                            </div>
                            <span class="small">{{ $session->course->lecturer->name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        @if ($session->isActive())
                            <span
                                class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Active</span>
                        @else
                            <span
                                class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">Expired</span>
                        @endif
                        <div class="small text-muted mt-1">{{ $session->attendance_records->count() }} Attended</div>
                    </td>
                    <!-- ... inside the table loop ... -->
                    <td class="px-4 py-3 text-end">
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm border rounded-circle" type="button"
                                data-bs-toggle="dropdown" style="width: 32px; height: 32px;">
                                <i class="fas fa-ellipsis-v text-muted"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                                <li>
                                    <!-- FIX: Changed route from 'lecturer.session.show' to 'admin.session.show' -->
                                    <a class="dropdown-item" href="{{ route('admin.session.show', $session->id) }}">
                                        <i class="fas fa-qrcode w-20 text-muted me-2"></i> View QR Code
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <form action="{{ route('admin.session.delete', $session->id) }}" method="POST"
                                        onsubmit="return confirm('Delete this session?');">
                                        @csrf @method('DELETE')
                                        <button class="dropdown-item text-danger">
                                            <i class="fas fa-trash w-20 me-2"></i> Delete Session
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">No sessions found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="d-flex justify-content-end p-3 border-top bg-light rounded-bottom">
    {{ $sessions->appends(['users_page' => request('users_page')])->links() }}
</div>

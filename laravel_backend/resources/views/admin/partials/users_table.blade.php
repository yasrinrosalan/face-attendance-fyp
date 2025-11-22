<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light">
            <tr class="text-secondary small text-uppercase">
                <th class="px-4 py-3 border-0">User Info</th>
                <th class="px-4 py-3 border-0">Role</th>
                <th class="px-4 py-3 border-0">Status</th>
                <th class="px-4 py-3 border-0 text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr>
                    <td class="px-4 py-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar-initial rounded-circle me-3 fw-bold text-white shadow-sm d-flex align-items-center justify-content-center"
                                style="width: 40px; height: 40px; background-color: {{ $user->role == 'admin' ? '#334155' : ($user->role == 'lecturer' ? '#203A8D' : '#0A7D87') }}">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="fw-bold text-dark">{{ $user->name }}</div>
                                <div class="small text-muted">{{ $user->email }}</div>
                                @if ($user->student_id)
                                    <div class="small text-muted font-monospace">{{ $user->student_id }}</div>
                                @endif
                            </div>
                        </div>
                        @if ($user->requesting_face_change)
                            <span class="badge bg-warning text-dark mt-2 ms-5"><i
                                    class="fas fa-exclamation-circle me-1"></i> Requesting Reset</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span
                            class="badge bg-light text-dark border fw-normal text-capitalize">{{ $user->role }}</span>
                    </td>
                    <td class="px-4 py-3">
                        @if ($user->isStudent())
                            @if ($user->face_template_path)
                                <span
                                    class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Enrolled</span>
                            @else
                                <span
                                    class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">Not
                                    Enrolled</span>
                            @endif
                        @else
                            <span class="text-muted small">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-end">
                        @if (Auth::id() !== $user->id)
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm border rounded-circle" type="button"
                                    data-bs-toggle="dropdown" style="width: 32px; height: 32px;">
                                    <i class="fas fa-ellipsis-v text-muted"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.user.loginas', $user->id) }}"
                                            onclick="return confirm('Login as this user?');">
                                            <i class="fas fa-sign-in-alt w-20 text-muted me-2"></i> Login As
                                        </a>
                                    </li>
                                    @if ($user->isStudent() && $user->face_template_path)
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <form action="{{ route('admin.user.enrollment.delete', $user->id) }}"
                                                method="POST">
                                                @csrf @method('DELETE')
                                                <button class="dropdown-item text-warning"
                                                    onclick="return confirm('Reset face data?');">
                                                    <i class="fas fa-redo w-20 me-2"></i> Reset Face ID
                                                </button>
                                            </form>
                                        </li>
                                    @endif
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <form action="{{ route('admin.user.delete', $user->id) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button class="dropdown-item text-danger"
                                                onclick="return confirm('Delete this user?');">
                                                <i class="fas fa-trash-alt w-20 me-2"></i> Delete User
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">No users found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="d-flex justify-content-end p-3 border-top bg-light rounded-bottom">
    {{ $users->appends(['sessions_page' => request('sessions_page')])->links() }}
</div>

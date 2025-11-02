@extends('layouts.app')

@section('content')
    <h1 class="mb-4">Admin Dashboard</h1>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header fs-5">User Management (Total: {{ $users->total() }})</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle"> {{-- Changed from table-dark --}}
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Face Enrolled?</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr class="{{ $user->requesting_face_change ? 'table-warning' : '' }}">
                                        <td>{{ $user->id }}</td>
                                        <td>
                                            {{ $user->name }}
                                            @if ($user->requesting_face_change)
                                                <br><small class="fw-bold text-danger">REQUESTING CHANGE</small>
                                            @endif
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td><span class="badge bg-secondary">{{ $user->role }}</span></td>
                                        <td>
                                            @if ($user->face_template_path)
                                                <span class="badge bg-success">Yes</span>
                                            @else
                                                <span class="badge bg-secondary">No</span> {{-- Changed bg-light text-dark to bg-secondary --}}
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if (Auth::id() !== $user->id)
                                                <a href="{{ route('admin.user.loginas', $user->id) }}"
                                                    class="btn btn-sm btn-primary"
                                                    onclick="return confirm('Are you sure you want to log in as this user?');">
                                                    Login As
                                                </a>

                                                @if ($user->isStudent() && $user->face_template_path)
                                                    <form action="{{ route('admin.user.enrollment.delete', $user->id) }}"
                                                        method="POST" class="d-inline"
                                                        onsubmit="return confirm('Are you sure you want to reset this enrollment? The student will be forced to re-enroll.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        @if ($user->requesting_face_change)
                                                            <button type="submit" class="btn btn-sm btn-success"
                                                                title="Accept Request">
                                                                Accept Change
                                                            </button>
                                                        @else
                                                            <button type="submit" class="btn btn-sm btn-warning"
                                                                title="Delete Enrollment">
                                                                Del. Enroll
                                                            </button>
                                                        @endif
                                                    </form>
                                                @endif

                                                <form action="{{ route('admin.user.delete', $user->id) }}" method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('WARNING: This will permanently delete the user and all their data. Are you sure?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                        title="Delete User">Delete User</button>
                                                </form>
                                            @else
                                                <span class="text-muted"> (Current User) </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No users found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $users->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header fs-5">Attendance Session Management (Total: {{ $sessions->total() }})</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle"> {{-- Changed from table-dark --}}
                            <thead>
                                <tr>
                                    <th>Session</th>
                                    <th>Course</th>
                                    <th>Lecturer</th>
                                    <th>Code</th>
                                    <th>Status</th>
                                    <th>Attended</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sessions as $session)
                                    <tr>
                                        <td>{{ $session->session_title }}</td>
                                        <td>{{ $session->course->course_name }}</td>
                                        <td>{{ $session->course->lecturer->name }}</td>
                                        <td><span class="badge bg-secondary">{{ $session->referral_code }}</span></td>
                                        {{-- Changed bg-dark to bg-secondary --}}
                                        <td>
                                            @if ($session->isActive())
                                                <span class="badge bg-success">Active</span>
                                            @elseif(now()->gt($session->ends_at))
                                                <span class="badge bg-secondary">Expired</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @endif
                                        </td>
                                        <td>{{ $session->attendance_records->count() }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('lecturer.session.show', $session->id) }}"
                                                class="btn btn-sm btn-primary">
                                                Show QR
                                            </a>
                                            <form action="{{ route('admin.session.delete', $session->id) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete this session?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No sessions found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $sessions->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

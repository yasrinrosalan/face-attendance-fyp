<div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Create New Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                <form action="{{ route('admin.user.create') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">ACCOUNT ROLE</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="role" id="roleStudent"
                                    value="student" checked onchange="toggleStudentId(true)">
                                <label class="form-check-label" for="roleStudent">Student</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="role" id="roleLecturer"
                                    value="lecturer" onchange="toggleStudentId(false)">
                                <label class="form-check-label" for="roleLecturer">Lecturer</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">FULL NAME</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3" id="studentIdGroup">
                        <label class="form-label small fw-bold text-secondary">STUDENT ID</label>
                        <input type="text" name="student_id" class="form-control" placeholder="e.g. CB20012">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">EMAIL ADDRESS</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-secondary">PASSWORD</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary py-2 fw-bold">Create Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleStudentId(isStudent) {
        const group = document.getElementById('studentIdGroup');
        if (isStudent) {
            group.style.display = 'block';
        } else {
            group.style.display = 'none';
        }
    }
</script>

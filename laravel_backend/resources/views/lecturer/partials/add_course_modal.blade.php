<div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-primary" id="addCourseModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Add New Course
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-4 font-sans-serif">
                <form action="{{ route('lecturer.course.create') }}" method="POST">
                    @csrf

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control bg-light border-0 fw-medium" id="course_code"
                            name="course_code" placeholder="e.g. CS101" required>
                        <label for="course_code" class="text-secondary">Course Code (e.g. CS101)</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control bg-light border-0 fw-medium" id="course_name"
                            name="course_name" placeholder="e.g. Intro to Computer Science" required>
                        <label for="course_name" class="text-secondary">Course Name</label>
                    </div>

                    <div class="form-floating mb-3">
                        <select class="form-select bg-light border-0 fw-medium" id="academic_year" name="academic_year"
                            aria-label="Academic Year" required>
                            <option value="2024/2025">2024/2025</option>
                            <option value="2025/2026" selected>2025/2026</option>
                            <option value="2026/2027">2026/2027</option>
                        </select>
                        <label for="academic_year" class="text-secondary">Academic Year</label>
                    </div>

                    <div class="form-floating mb-4">
                        <select class="form-select bg-light border-0 fw-medium" id="semester" name="semester"
                            aria-label="Semester" required>
                            <option value="1">Semester 1</option>
                            <option value="2">Semester 2</option>
                            <option value="3">Semester 3 (Short)</option>
                        </select>
                        <label for="semester" class="text-secondary">Semester</label>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold">
                            Save Course <i class="fas fa-check ms-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

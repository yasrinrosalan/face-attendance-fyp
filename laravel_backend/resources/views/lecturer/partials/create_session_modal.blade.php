<div class="modal fade" id="createSessionModal" tabindex="-1" aria-labelledby="createSessionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-primary" id="createSessionModalLabel">
                    <i class="fas fa-calendar-plus me-2"></i>Create New Session
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-4 font-sans-serif">
                <form action="{{ route('lecturer.session.create') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label for="course_id_display"
                            class="form-label fw-bold small text-uppercase text-secondary">Course</label>

                        {{-- Check if a single course variable exists (we are on the Course Details page) --}}
                        @if (isset($course) && $course instanceof \App\Models\Course)
                            <input type="text" id="course_id_display"
                                class="form-control form-control-lg bg-light border-0 fw-medium mb-1"
                                value="{{ $course->course_code }} - {{ $course->course_name }}" readonly>
                            <input type="hidden" name="course_id" value="{{ $course->id }}">
                            <div class="form-text small text-muted">Creating session for the current course.</div>

                            {{-- Otherwise, check if the list of courses exists (we are on the Dashboard) --}}
                        @elseif(isset($courses))
                            <select name="course_id" id="course_id"
                                class="form-select form-select-lg bg-light border-0 fw-medium" required>
                                @if ($courses->isEmpty())
                                    <option value="" disabled selected>-- No courses available --</option>
                                @else
                                    <option value="" disabled selected>Choose a course...</option>
                                @endif
                                @foreach ($courses as $c)
                                    <option value="{{ $c->id }}">{{ $c->course_code }} - {{ $c->course_name }}
                                    </option>
                                @endforeach
                            </select>
                            @if ($courses->isEmpty())
                                <div class="form-text text-danger mt-2">
                                    <i class="fas fa-exclamation-circle me-1"></i> You need to add a course before
                                    creating a session.
                                </div>
                            @endif
                        @else
                            <div class="alert alert-danger small">Error: Could not load course data.</div>
                        @endif
                    </div>

                    <div class="mb-4">
                        <label for="session_title"
                            class="form-label fw-bold small text-uppercase text-secondary">Session Title</label>
                        <input type="text" name="session_title" id="session_title"
                            class="form-control form-control-lg bg-light border-0 fw-medium"
                            placeholder="e.g. Week 5: Midterm Review" required>
                    </div>

                    <div class="mb-4">
                        <label for="duration" class="form-label fw-bold small text-uppercase text-secondary">
                            <i class="fas fa-hourglass-half me-1"></i> Duration
                        </label>
                        <select name="duration" id="duration"
                            class="form-select form-select-lg bg-light border-0 fw-medium" required>
                            <option value="5">5 Minutes (Quick Check)</option>
                            <option value="15">15 Minutes</option>
                            <option value="30">30 Minutes</option>
                            <option value="60" selected>1 Hour (Standard)</option>
                            <option value="120">2 Hours</option>
                        </select>
                        <div class="form-text small text-muted">
                            Session will automatically expire after this time.
                        </div>
                    </div>

                    <div class="d-grid">
                        {{-- Disable button only if on dashboard AND courses list is empty --}}
                        <button type="submit" class="btn btn-primary btn-lg fw-bold"
                            @if (isset($courses) && $courses->isEmpty() && !isset($course)) disabled @endif>
                            Start Session Now <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

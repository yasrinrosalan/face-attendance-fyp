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
                            placeholder="e.g. Lecture 1: Intro" required>
                    </div>

                    <div class="mb-4">
                        <label for="week_number" class="form-label fw-bold small text-uppercase text-secondary">
                            <i class="fas fa-calendar-week me-1"></i> Academic Week
                        </label>
                        <select name="week_number" id="week_number"
                            class="form-select form-select-lg bg-light border-0 fw-medium" required>
                            <option value="" disabled selected>Select Week...</option>
                            @for ($i = 1; $i <= 14; $i++)
                                <option value="{{ $i }}">Week {{ $i }}</option>
                            @endfor
                        </select>
                        <div class="form-text small text-muted">
                            Used to group data for the 14-week end-of-semester report.
                        </div>
                    </div>

                    <!-- Session Mode -->
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase text-secondary">Session Mode</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <!-- Added onchange trigger for Physical -->
                                <input type="radio" class="btn-check" name="mode" id="mode_physical"
                                    value="physical" onchange="toggleLocation(true)" checked>
                                <label class="btn btn-outline-primary w-100 py-2" for="mode_physical">
                                    <i class="fas fa-building me-1"></i> Physical
                                </label>
                            </div>
                            <div class="col-6">
                                <!-- Added onchange trigger for Online -->
                                <input type="radio" class="btn-check" name="mode" id="mode_online" value="online"
                                    onchange="toggleLocation(false)">
                                <label class="btn btn-outline-primary w-100 py-2" for="mode_online">
                                    <i class="fas fa-laptop-house me-1"></i> Online
                                </label>
                            </div>
                        </div>
                        <div class="form-text small text-muted mt-2">
                            <strong>Physical:</strong> Enforces Geofencing (Students must be in class).<br>
                            <strong>Online:</strong> Disables Geofencing (Students can attend remotely).
                        </div>
                    </div>

                    <!-- NEW: Dynamic Faculty Location Dropdown -->
                    <div class="mb-4" id="locationSelector">
                        <label for="location_coords" class="form-label fw-bold small text-uppercase text-secondary">
                            <i class="fas fa-map-marker-alt me-1"></i> Faculty / Location
                        </label>
                        <select name="location_coords" id="location_coords"
                            class="form-select form-select-lg bg-light border-0 fw-medium" required>
                            <option value="" disabled selected>Select Faculty Building...</option>
                            <option value="3.546758,103.427747">Faculty of Computing</option>
                            <option value="3.543012,103.428105">Faculty of Electrical and Electronics Engineering
                                Technology</option>
                            <option value="3.545123,103.429456">Faculty of Chemical and Process Engineering Technology
                            </option>
                            <option value="3.548987,103.430123">Faculty of Mechanical and Automotive Engineering
                                Technology</option>
                            <option value="3.549876,103.431987">Faculty of Manufacturing and Mechatronic Engineering
                                Technology</option>
                        </select>
                        <div class="form-text small text-muted">Required to set the exact geofencing radius.</div>
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

                    <div class="d-grid mt-2">
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

<!-- JavaScript to toggle the Faculty Dropdown -->
<script>
    function toggleLocation(isPhysical) {
        const locationDiv = document.getElementById('locationSelector');
        const locationSelect = document.getElementById('location_coords');

        if (isPhysical) {
            // Show dropdown and make it required
            locationDiv.style.display = 'block';
            locationSelect.setAttribute('required', 'required');
        } else {
            // Hide dropdown and remove required attribute
            locationDiv.style.display = 'none';
            locationSelect.removeAttribute('required');
            locationSelect.value = ''; // Reset selection
        }
    }

    // Run this once when the modal loads to ensure the initial state is correct
    document.addEventListener('DOMContentLoaded', function() {
        const physicalRadio = document.getElementById('mode_physical');
        if (physicalRadio) {
            toggleLocation(physicalRadio.checked);
        }
    });
</script>

@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                        <h4 class="fw-bold text-primary"><i class="fas fa-headset me-2"></i>Report an Issue</h4>
                        <p class="text-muted small mb-0">Describe the problem you are facing.</p>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('report.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-secondary">SUBJECT</label>
                                <input type="text" name="subject" class="form-control"
                                    placeholder="e.g. Cannot enroll face" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold small text-secondary">MESSAGE / DETAILS</label>
                                <textarea name="message" class="form-control" rows="5" placeholder="Describe what happened..." required></textarea>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary fw-bold py-2">Submit Report</button>
                                <a href="{{ url()->previous() }}" class="btn btn-light text-muted">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

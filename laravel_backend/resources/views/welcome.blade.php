@extends('layouts.app')

@section('content')
    <div class="p-5 mb-4 bg-light text-dark rounded-3 text-center border"> {{-- Changed to bg-light text-dark --}}
        <div class="container-fluid py-5">
            <h1 class="display-5 fw-bold">Face Recognition Attendance System</h1>
            {{-- <p class="fs-4">Welcome to your Final Year Project.</p> --}}
            @guest
                <a href="{{ route('login') }}" class="btn btn-primary btn-lg">Login</a>
                <a href="{{ route('register') }}" class="btn btn-secondary btn-lg">Register as Student</a>
            @else
                @if (Auth::user()->isStudent())
                    <a href="{{ route('student.dashboard') }}" class="btn btn-primary btn-lg">Go to My Dashboard</a>
                @elseif(Auth::user()->isLecturer())
                    <a href="{{ route('lecturer.dashboard') }}" class="btn btn-primary btn-lg">Go to My Dashboard</a>
                @else
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-lg">Go to Admin Dashboard</a>
                @endif
            @endguest
        </div>
    </div>
@endsection

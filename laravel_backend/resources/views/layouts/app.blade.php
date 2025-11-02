<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Face Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root {
            --bs-primary: #203A8D;
            /* Deep Blue from logo */
            --bs-primary-rgb: 32, 58, 141;
            --bs-secondary: #0A7D87;
            /* Teal from logo */
            --bs-secondary-rgb: 10, 125, 135;
            --bs-btn-color: #fff;
            --bs-btn-hover-color: #fff;
            --bs-btn-active-color: #fff;
            --bs-body-bg: #f8f9fa;
            --bs-body-color: #212529;
        }

        a {
            color: var(--bs-primary);
        }

        .btn-link {
            color: var(--bs-primary);
        }

        a:hover {
            color: var(--bs-primary);
            text-decoration: underline;
        }

        .banner-impersonating {
            background-color: var(--bs-secondary);
            color: #fff;
            font-weight: bold;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 9999;
            border-radius: 0;
            padding: 0.75rem 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, .1);
        }

        .banner-impersonating .alert-link {
            color: #fff;
            font-weight: bold;
            text-decoration: underline;
        }

        .banner-impersonating+nav {
            margin-top: 50px;
        }

        .navbar-light {
            background-color: #ffffff !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, .05);
        }

        .navbar-brand img {
            height: 40px;
        }

        .navbar-light .navbar-nav .nav-link,
        .navbar-light .navbar-text {
            color: var(--bs-primary) !important;
            font-weight: 500;
        }

        .navbar-light .navbar-nav .nav-link:hover {
            color: var(--bs-secondary) !important;
        }

        .navbar-light .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgb%2832, 58, 141%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        .live-clock {
            color: var(--bs-secondary);
            font-weight: bold;
            font-size: 1.1rem;
            padding-right: 15px;
        }

        .card {
            background-color: #ffffff;
            border-color: #e9ecef;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
    </style>
</head>

<body>
    @if (session()->has('admin_impersonating_id'))
        <div class="banner-impersonating text-center">
            You are currently impersonating {{ Auth::user()->name }}.
            <a href="{{ route('return.to.admin') }}" class="alert-link">Return to Admin Dashboard</a>
        </div>
    @endif

    <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
        <div class="container">
            <a class="navbar-brand" href="/">
                <img src="{{ asset('logo.png') }}" alt="FaceAttendance Logo">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">

                <ul class="navbar-nav me-auto">
                    @auth
                        <li class="nav-item">
                            <span class="nav-link live-clock" id="live-clock">Loading Clock...</span>
                        </li>
                    @endauth
                </ul>

                <ul class="navbar-nav ms-auto">
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">Register (Student)</a>
                        </li>
                    @else
                        @if (Auth::user()->isAdmin())
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.dashboard') }}">Admin Dashboard</a>
                            </li>
                        @elseif (Auth::user()->isLecturer())
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('lecturer.dashboard') }}">Dashboard</a>
                            </li>
                        @elseif (Auth::user()->isStudent())
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('student.dashboard') }}">Dashboard</a>
                            </li>
                        @endif

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                @if (Auth::user()->isStudent())
                                    <li><a class="dropdown-item" href="{{ route('student.enrollment.page') }}">My
                                            Enrollment</a></li>
                                @endif

                                <li>
                                    <a class="dropdown-item"
                                        href="mailto:admin@demo.com?subject=Attendance System Issue - ({{ Auth::user()->email }})">
                                        Report an Issue
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                        onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <main class="container">
        @if (session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger" role="alert">
                {{ session('error') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function updateClock() {
            const clockElement = document.getElementById('live-clock');
            if (clockElement) {
                const now = new Date();
                const options = {
                    weekday: 'short',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                };
                clockElement.textContent = now.toLocaleString('en-US', options);
            }
        }

        setInterval(updateClock, 1000);
        updateClock();
    </script>
    @stack('scripts')
</body>

</html>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Face Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root {
            --bs-primary: #203A8D;
            --bs-primary-rgb: 32, 58, 141;
            --bs-secondary: #0A7D87;
            --bs-secondary-rgb: 10, 125, 135;
            --bs-body-bg: #F5F7FA;
            --bs-body-color: #334155;
            --bs-btn-color: #ffffff;
        }

        body {
            background-color: var(--bs-body-bg);
            font-family: 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        a {
            text-decoration: none;
        }

        /* --- REFINED NAVBAR --- */
        .navbar-custom {
            background-color: #ffffff !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .navbar-brand img {
            height: 48px;
        }

        /* Cleaner Nav Links */
        .nav-link {
            color: #64748b !important;
            font-weight: 500;
            font-size: 0.95rem;
            padding: 0.5rem 1rem !important;
            margin: 0 0.2rem;
            transition: color 0.2s;
        }

        .nav-link:hover {
            color: var(--bs-primary) !important;
        }

        /* Active State */
        .nav-link.active {
            color: var(--bs-primary) !important;
            font-weight: 700;
        }

        /* --- MODERN INPUTS --- */
        .form-control,
        .form-select {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            font-size: 0.95rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--bs-secondary);
            box-shadow: 0 0 0 3px rgba(10, 125, 135, 0.1);
        }

        label {
            font-weight: 500;
            margin-bottom: 0.4rem;
            color: #475569;
        }

        /* --- MODERN CARDS --- */
        .card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.01), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid #f1f5f9;
            font-weight: 600;
            color: #1e293b;
            padding: 1.25rem;
        }

        .card-body {
            padding: 1.25rem;
        }

        /* --- BUTTONS --- */
        .btn-primary {
            background-color: var(--bs-primary);
            border: none;
            font-weight: 500;
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background-color: #1a2e70;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        /* --- UTILS --- */
        .live-clock {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 500;
            background-color: #f1f5f9;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .user-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #334155 !important;
            font-weight: 600;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            background-color: var(--bs-secondary);
            color: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            font-weight: 600;
        }

        /* Dropdown Polish */
        .dropdown-menu {
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 6px;
        }

        .dropdown-item {
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 0.9rem;
            color: #475569;
        }

        .dropdown-item:hover {
            background-color: #f1f5f9;
            color: var(--bs-primary);
        }

        .banner-impersonating {
            background-color: var(--bs-secondary);
            color: white;
            font-weight: 600;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1030;
            padding: 10px;
            font-size: 0.9rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .banner-impersonating+nav {
            margin-top: 45px;
        }
    </style>
</head>

<body>
    @php
        $isFullWidth = Request::is('login') || Request::is('register');
    @endphp
    @if (session()->has('admin_impersonating_id'))
        <div class="banner-impersonating text-center">
            <i class="fas fa-mask me-2"></i>
            Acting as <strong>{{ Auth::user()->name }}</strong>
            <a href="{{ route('return.to.admin') }}" class="text-white ms-3 text-decoration-underline">Return to
                Admin</a>
        </div>
    @endif

    <nav class="navbar navbar-expand-lg navbar-custom {{ $isFullWidth ? 'mb-0' : 'mb-0' }} sticky-top">
        <div class="container">
            <a class="navbar-brand" href="/">
                <img src="{{ asset('logo.png') }}" alt="Logo">
            </a>

            <button class="navbar-toggler border-0 p-2" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon text-secondary"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">

                <ul class="navbar-nav mx-auto">
                    @auth
                        @if (Auth::user()->isAdmin())
                            <li class="nav-item"><a
                                    class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                                    href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="nav-item"><a
                                    class="nav-link {{ request()->routeIs('admin.analytics') ? 'active' : '' }}"
                                    href="{{ route('admin.analytics') }}">Analytics</a></li>
                        @elseif (Auth::user()->isLecturer())
                            <li class="nav-item"><a
                                    class="nav-link {{ request()->routeIs('lecturer.dashboard') ? 'active' : '' }}"
                                    href="{{ route('lecturer.dashboard') }}">Dashboard</a></li>
                            <li class="nav-item"><a
                                    class="nav-link {{ request()->routeIs('lecturer.analytics') ? 'active' : '' }}"
                                    href="{{ route('lecturer.analytics') }}">Analytics</a></li>
                        @elseif (Auth::user()->isStudent())
                            <li class="nav-item"><a
                                    class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}"
                                    href="{{ route('student.dashboard') }}">Dashboard</a></li>
                        @endif
                    @endauth
                </ul>

                <ul class="navbar-nav ms-auto align-items-center">
                    @guest
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Register</a></li>
                    @else
                        <li class="nav-item me-3 d-none d-lg-block">
                            <div class="live-clock">
                                <i class="far fa-clock text-secondary"></i>
                                <span id="live-clock">--:--</span>
                            </div>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle user-toggle" href="#" id="navbarDropdown"
                                role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="user-avatar">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                                <span class="d-inline-block text-truncate"
                                    style="max-width: 150px;">{{ Auth::user()->name }}</span>
                            </a>

                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li class="px-2 py-2">
                                    <div class="bg-light rounded p-2">
                                        <small class="text-muted d-block" style="font-size: 0.75rem;">Signed in as</small>
                                        <strong class="text-dark text-truncate d-block"
                                            style="font-size: 0.85rem;">{{ Auth::user()->email }}</strong>
                                    </div>
                                </li>

                                @if (Auth::user()->isStudent())
                                    <li><a class="dropdown-item" href="{{ route('student.enrollment.page') }}"><i
                                                class="fas fa-id-badge me-2 text-secondary"></i> My Enrollment</a></li>
                                @endif

                                @if (!Auth::user()->isAdmin())
                                    <li>
                                        <a class="dropdown-item" href="{{ route('report.create') }}">
                                            <i class="fas fa-headset me-2 text-secondary"></i> Report Issue
                                        </a>
                                    </li>
                                @endif
                                <li>
                                    <hr class="dropdown-divider">
                                </li>

                                <li>
                                    <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="fas fa-sign-out-alt me-2"></i> {{ __('Logout') }}
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf</form>
                                </li>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <main class="{{ $isFullWidth ? 'container-fluid p-0' : 'container py-3' }}">

        <div class="{{ $isFullWidth ? 'container mt-3' : '' }}">
            @if (session('success'))
                <div class="alert alert-success border-0 shadow-sm mb-4 d-flex align-items-center"
                    style="border-left: 4px solid var(--bs-success);">
                    <i class="fas fa-check-circle me-2 fs-5 text-success"></i> {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger border-0 shadow-sm mb-4 d-flex align-items-center"
                    style="border-left: 4px solid var(--bs-danger);">
                    <i class="fas fa-exclamation-triangle me-2 fs-5 text-danger"></i> {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm mb-4">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
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
                    minute: '2-digit'
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

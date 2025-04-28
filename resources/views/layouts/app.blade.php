<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'COD Manager TN') }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        .sidebar {
            min-height: 100vh;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            background-color: #343a40;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.75);
            padding: 0.8rem 1.5rem;
            border-radius: 0.25rem;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        .sidebar .nav-link.active {
            color: #fff;
            background-color: #007bff;
        }
        .sidebar .nav-item .nav-link i {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }
        .main-content {
            padding: 20px;
        }
        .nav-section {
            margin-bottom: 20px;
        }
        .nav-section-title {
            font-size: 0.8rem;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.5);
            font-weight: bold;
            padding: 0.5rem 1.5rem;
            margin-top: 1rem;
        }
        .logo-container {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1rem;
        }
        .logo-container h4 {
            color: white;
            margin-bottom: 0.25rem;
        }
        .logo-container p {
            color: rgba(255, 255, 255, 0.5);
            margin-bottom: 0;
            font-size: 0.85rem;
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar - à n'afficher que si l'utilisateur est connecté -->
                @if(Auth::guard('admin')->check() || Auth::check())
                    @include('layouts.sidebar')
                @endif
                
                <!-- Main content -->
                <main class="{{ (Auth::guard('admin')->check() || Auth::check()) ? 'col-md-10 ms-sm-auto col-lg-10 px-4' : 'col-12' }} main-content">
                    <!-- Alert messages -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    @if(isset($reminder))
                        <div class="alert alert-info alert-dismissible fade show mt-3" role="alert">
                            <i class="bi bi-bell"></i> {{ $reminder }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    <!-- Page content -->
                    @yield('content')
                </main>
            </div>
        </div>
    </div>
    
    @stack('scripts')
</body>
</html>
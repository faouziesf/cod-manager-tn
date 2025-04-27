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
            color: white;
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
                <!-- Sidebar -->
                <div class="col-md-2 col-lg-2 d-md-block sidebar collapse">
                    <div class="position-sticky pt-3">
                        <div class="logo-container">
                            <h4>COD Manager TN</h4>
                            <p>
                                @if(Auth::guard('admin')->check())
                                    {{ Auth::guard('admin')->user()->name }}
                                    <small class="d-block text-muted">Admin</small>
                                @elseif(Auth::guard('web')->check())
                                    {{ Auth::user()->name }}
                                    <small class="d-block text-muted">{{ ucfirst(Auth::user()->role) }}</small>
                                @endif
                            </p>
                        </div>
                        
                        <ul class="nav flex-column">
                            <!-- Dashboard -->
                            <li class="nav-item">
                                @if(Auth::guard('admin')->check())
                                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                        <i class="bi bi-speedometer2"></i> Tableau de bord
                                    </a>
                                @elseif(Auth::user() && Auth::user()->role == 'manager')
                                    <a class="nav-link {{ request()->routeIs('manager.dashboard') ? 'active' : '' }}" href="{{ route('manager.dashboard') }}">
                                        <i class="bi bi-speedometer2"></i> Tableau de bord
                                    </a>
                                @elseif(Auth::user())
                                    <a class="nav-link {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}" href="{{ route('employee.dashboard') }}">
                                        <i class="bi bi-speedometer2"></i> Tableau de bord
                                    </a>
                                @endif
                            </li>
                            
                            <li class="nav-section">
                                <div class="nav-section-title">Commandes</div>
                            </li>
                            
                            <!-- Commandes Standard -->
                            <li class="nav-item">
                                @if(Auth::guard('admin')->check())
                                    <a class="nav-link {{ request()->routeIs('admin.orders.standard') ? 'active' : '' }}" href="{{ route('admin.orders.standard') }}">
                                        <i class="bi bi-telephone-fill"></i> Standard
                                    </a>
                                @elseif(Auth::user() && Auth::user()->role == 'manager')
                                    <a class="nav-link {{ request()->routeIs('manager.orders.standard') ? 'active' : '' }}" href="{{ route('manager.orders.standard') }}">
                                        <i class="bi bi-telephone-fill"></i> Standard
                                    </a>
                                @elseif(Auth::user())
                                    <a class="nav-link {{ request()->routeIs('employee.orders.standard') ? 'active' : '' }}" href="{{ route('employee.orders.standard') }}">
                                        <i class="bi bi-telephone-fill"></i> Standard
                                    </a>
                                @endif
                            </li>
                            
                            <!-- Commandes Datées -->
                            <li class="nav-item">
                                @if(Auth::guard('admin')->check())
                                    <a class="nav-link {{ request()->routeIs('admin.orders.scheduled') ? 'active' : '' }}" href="{{ route('admin.orders.scheduled') }}">
                                        <i class="bi bi-calendar-event"></i> Datées
                                    </a>
                                @elseif(Auth::user() && Auth::user()->role == 'manager')
                                    <a class="nav-link {{ request()->routeIs('manager.orders.scheduled') ? 'active' : '' }}" href="{{ route('manager.orders.scheduled') }}">
                                        <i class="bi bi-calendar-event"></i> Datées
                                    </a>
                                @elseif(Auth::user())
                                    <a class="nav-link {{ request()->routeIs('employee.orders.scheduled') ? 'active' : '' }}" href="{{ route('employee.orders.scheduled') }}">
                                        <i class="bi bi-calendar-event"></i> Datées
                                    </a>
                                @endif
                            </li>
                            
                            <!-- Commandes Anciennes -->
                            <li class="nav-item">
                                @if(Auth::guard('admin')->check())
                                    <a class="nav-link {{ request()->routeIs('admin.orders.old') ? 'active' : '' }}" href="{{ route('admin.orders.old') }}">
                                        <i class="bi bi-archive"></i> Anciennes
                                    </a>
                                @elseif(Auth::user() && Auth::user()->role == 'manager')
                                    <a class="nav-link {{ request()->routeIs('manager.orders.old') ? 'active' : '' }}" href="{{ route('manager.orders.old') }}">
                                        <i class="bi bi-archive"></i> Anciennes
                                    </a>
                                @elseif(Auth::user())
                                    <a class="nav-link {{ request()->routeIs('employee.orders.old') ? 'active' : '' }}" href="{{ route('employee.orders.old') }}">
                                        <i class="bi bi-archive"></i> Anciennes
                                    </a>
                                @endif
                            </li>
                            
                            <!-- Recherche -->
                            <li class="nav-item">
                                @if(Auth::guard('admin')->check())
                                    <a class="nav-link {{ request()->routeIs('admin.orders.search') ? 'active' : '' }}" href="{{ route('admin.orders.search') }}">
                                        <i class="bi bi-search"></i> Recherche
                                    </a>
                                @elseif(Auth::user() && Auth::user()->role == 'manager')
                                    <a class="nav-link {{ request()->routeIs('manager.orders.search') ? 'active' : '' }}" href="{{ route('manager.orders.search') }}">
                                        <i class="bi bi-search"></i> Recherche
                                    </a>
                                @elseif(Auth::user())
                                    <a class="nav-link {{ request()->routeIs('employee.orders.search') ? 'active' : '' }}" href="{{ route('employee.orders.search') }}">
                                        <i class="bi bi-search"></i> Recherche
                                    </a>
                                @endif
                            </li>
                            
                            <!-- Nouvelle commande (seulement pour Admin et Manager) -->
                            @if(Auth::guard('admin')->check())
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.orders.create') ? 'active' : '' }}" href="{{ route('admin.orders.create') }}">
                                        <i class="bi bi-plus-circle"></i> Nouvelle commande
                                    </a>
                                </li>
                            @elseif(Auth::user() && Auth::user()->role == 'manager')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('manager.orders.create') ? 'active' : '' }}" href="{{ route('manager.orders.create') }}">
                                        <i class="bi bi-plus-circle"></i> Nouvelle commande
                                    </a>
                                </li>
                            @endif
                            
                            <!-- Commandes à vérifier (seulement pour Admin et Manager) -->
                            @if(Auth::guard('admin')->check() || (Auth::user() && Auth::user()->role == 'manager'))
                                <li class="nav-item">
                                    @if(Auth::guard('admin')->check())
                                        <a class="nav-link {{ request()->routeIs('admin.orders.needs-verification') ? 'active' : '' }}" href="{{ route('admin.orders.needs-verification') }}">
                                            <i class="bi bi-exclamation-triangle"></i> À vérifier
                                        </a>
                                    @else
                                        <a class="nav-link {{ request()->routeIs('manager.orders.needs-verification') ? 'active' : '' }}" href="{{ route('manager.orders.needs-verification') }}">
                                            <i class="bi bi-exclamation-triangle"></i> À vérifier
                                        </a>
                                    @endif
                                </li>
                            @endif
                            
                            <!-- Importation (seulement pour Admin et Manager) -->
                            @if(Auth::guard('admin')->check() || (Auth::user() && Auth::user()->role == 'manager'))
                                <li class="nav-item">
                                    @if(Auth::guard('admin')->check())
                                        <a class="nav-link {{ request()->routeIs('admin.orders.import') ? 'active' : '' }}" href="{{ route('admin.orders.import') }}">
                                            <i class="bi bi-upload"></i> Importation
                                        </a>
                                    @else
                                        <a class="nav-link {{ request()->routeIs('manager.orders.import') ? 'active' : '' }}" href="{{ route('manager.orders.import') }}">
                                            <i class="bi bi-upload"></i> Importation
                                        </a>
                                    @endif
                                </li>
                            @endif
                            
                            <!-- Produits -->
                            <li class="nav-section">
                                <div class="nav-section-title">Catalogue</div>
                            </li>
                            <li class="nav-item">
                                @if(Auth::guard('admin')->check())
                                    <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">
                                        <i class="bi bi-box-seam"></i> Produits
                                    </a>
                                @elseif(Auth::user() && Auth::user()->role == 'manager')
                                    <a class="nav-link {{ request()->routeIs('manager.products.*') ? 'active' : '' }}" href="{{ route('manager.products.index') }}">
                                        <i class="bi bi-box-seam"></i> Produits
                                    </a>
                                @endif
                            </li>
                            
                            <!-- Administration (Admin uniquement) -->
                            @if(Auth::guard('admin')->check())
                                <li class="nav-section">
                                    <div class="nav-section-title">Administration</div>
                                </li>
                                
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                                        <i class="bi bi-people"></i> Utilisateurs
                                    </a>
                                </li>
                                
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}">
                                        <i class="bi bi-gear"></i> Paramètres
                                    </a>
                                </li>
                            @endif
                            
                            <!-- Déconnexion -->
                            <li class="nav-section">
                                <div class="nav-section-title">Compte</div>
                            </li>
                            <li class="nav-item">
                                @if(Auth::guard('admin')->check())
                                    <form method="POST" action="{{ route('admin.logout') }}">
                                        @csrf
                                        <a class="nav-link" href="{{ route('admin.logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                                            <i class="bi bi-box-arrow-right"></i> Déconnexion
                                        </a>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <a class="nav-link" href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                                            <i class="bi bi-box-arrow-right"></i> Déconnexion
                                        </a>
                                    </form>
                                @endif
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Main content -->
                <main class="col-md-10 ms-sm-auto col-lg-10 px-4 main-content">
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
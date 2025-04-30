<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="COD Manager TN - Système de gestion des commandes">
    <meta name="author" content="COD Manager TN">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title') - COD Manager TN Admin</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.ico') }}">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom styles -->
    <style>
        :root {
            --primary: #4e73df;
            --secondary: #858796;
            --success: #1cc88a;
            --info: #36b9cc;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --light: #f8f9fc;
            --dark: #5a5c69;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fc;
        }
        
        /* Sidebar */
        .sidebar {
            width: 250px;
            min-height: 100vh;
            background-color: #4e73df;
            background-image: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            background-size: cover;
            position: fixed;
            z-index: 1;
            top: 0;
            left: 0;
            overflow-x: hidden;
            transition: 0.5s;
        }
        
        .sidebar-brand {
            padding: 1.5rem 1rem;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.05rem;
            color: white;
        }
        
        .sidebar hr {
            margin: 0 1rem;
            border-top: 1px solid rgba(255,255,255,0.15);
        }
        
        .sidebar-heading {
            padding: 0 1rem;
            font-weight: 800;
            font-size: 0.65rem;
            color: rgba(255,255,255,0.4);
            text-transform: uppercase;
            letter-spacing: 0.13rem;
            margin-top: 1rem;
        }
        
        .nav-item {
            position: relative;
        }
        
        .nav-link {
            display: block;
            padding: 0.75rem 1rem;
            color: rgba(255,255,255,0.8);
            font-weight: 700;
            font-size: 0.85rem;
        }
        
        .nav-link:hover {
            color: white;
        }
        
        .nav-link i {
            margin-right: 0.25rem;
            opacity: 0.75;
        }
        
        .nav-link.active {
            font-weight: 700;
            color: white;
        }
        
        .nav-link.active i {
            opacity: 1;
        }
        
        /* Content wrapper */
        #content-wrapper {
            margin-left: 250px;
            min-height: 100vh;
            background-color: #f8f9fc;
            transition: margin-left 0.5s;
        }
        
        /* Topbar */
        .topbar {
            height: 4.375rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58,59,69,0.15);
            background-color: white;
            z-index: 1;
        }
        
        .topbar .dropdown-menu {
            min-width: 17.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58,59,69,0.15);
        }
        
        .topbar .dropdown-list .dropdown-header {
            background-color: #4e73df;
            border: 1px solid #4e73df;
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            color: white;
        }
        
        /* Cards */
        .card {
            margin-bottom: 24px;
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58,59,69,0.1);
        }
        
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }
        
        .card-header h6 {
            font-weight: 700;
            font-size: 1rem;
            color: #4e73df;
        }
        
        .border-left-primary {
            border-left: 0.25rem solid #4e73df !important;
        }
        
        .border-left-success {
            border-left: 0.25rem solid #1cc88a !important;
        }
        
        .border-left-info {
            border-left: 0.25rem solid #36b9cc !important;
        }
        
        .border-left-warning {
            border-left: 0.25rem solid #f6c23e !important;
        }
        
        .border-left-danger {
            border-left: 0.25rem solid #e74a3b !important;
        }
        
        /* Responsive */
        .sidebar-toggled .sidebar {
            width: 100px;
        }
        
        .sidebar-toggled .sidebar .sidebar-brand {
            padding: 1rem;
        }
        
        .sidebar-toggled .sidebar .sidebar-brand .sidebar-brand-text {
            display: none;
        }
        
        .sidebar-toggled .sidebar .nav-item .nav-link {
            text-align: center;
            padding: 0.75rem 1rem;
        }
        
        .sidebar-toggled .sidebar .nav-item .nav-link span {
            display: none;
        }
        
        .sidebar-toggled .sidebar .nav-item .nav-link i {
            margin-right: 0;
            font-size: 1.2rem;
        }
        
        .sidebar-toggled .sidebar .sidebar-heading {
            text-align: center;
        }
        
        .sidebar-toggled #content-wrapper {
            margin-left: 100px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100px;
            }
            
            .sidebar .sidebar-brand {
                padding: 1rem;
            }
            
            .sidebar .sidebar-brand .sidebar-brand-text {
                display: none;
            }
            
            .sidebar .nav-item .nav-link {
                text-align: center;
                padding: 0.75rem 1rem;
            }
            
            .sidebar .nav-item .nav-link span {
                display: none;
            }
            
            .sidebar .nav-item .nav-link i {
                margin-right: 0;
                font-size: 1.2rem;
            }
            
            .sidebar .sidebar-heading {
                text-align: center;
            }
            
            #content-wrapper {
                margin-left: 100px;
            }
        }
        
        /* Chart styling */
        .chart-area {
            position: relative;
            height: 300px;
            margin: 0 auto;
        }
        
        /* Footer */
        footer.sticky-footer {
            padding: 2rem 0;
            margin-top: auto;
            background-color: white;
        }
        
        .bg-gradient-primary {
            background-color: #4e73df;
            background-image: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            background-size: cover;
        }
    </style>
    
    @stack('styles')
</head>

<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-brand d-flex align-items-center justify-content-center">
                <div class="sidebar-brand-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="sidebar-brand-text mx-3">COD Manager</div>
            </div>

            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Tableau de bord</span>
                </a>
            </li>

            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Commandes
            </div>

            <!-- Nav Item - Orders -->
            <li class="nav-item {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.orders.index') }}">
                    <i class="fas fa-fw fa-shopping-cart"></i>
                    <span>Toutes les commandes</span>
                </a>
            </li>

            <!-- Nav Item - Import Orders -->
            <li class="nav-item {{ request()->routeIs('admin.orders.import') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.orders.import') }}">
                    <i class="fas fa-fw fa-file-import"></i>
                    <span>Importer des commandes</span>
                </a>
            </li>

            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Produits
            </div>

            <!-- Nav Item - Products -->
            <li class="nav-item {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.products.index') }}">
                    <i class="fas fa-fw fa-cube"></i>
                    <span>Gestion des produits</span>
                </a>
            </li>

            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Utilisateurs
            </div>

            <!-- Nav Item - Users -->
            <li class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.users.index') }}">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Gestion des utilisateurs</span>
                </a>
            </li>

            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Configuration
            </div>

            <!-- Nav Item - Settings -->
            <li class="nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.settings.index') }}">
                    <i class="fas fa-fw fa-cog"></i>
                    <span>Paramètres</span>
                </a>
            </li>

            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler -->
            <div class="text-center d-none d-md-inline mt-3">
                <button class="btn rounded-circle border-0" id="sidebarToggle">
                    <i class="fas fa-angle-left text-white"></i>
                </button>
            </div>
        </div>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Search -->
                    <form class="d-none d-sm-inline-block form-inline me-auto ms-md-3 my-2 my-md-0 w-100">
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" placeholder="Rechercher..." aria-label="Search" aria-describedby="basic-addon2">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                        <li class="nav-item dropdown no-arrow d-sm-none">
                            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-search fa-fw"></i>
                            </a>
                            <!-- Dropdown - Messages -->
                            <div class="dropdown-menu dropdown-menu-end p-3 shadow animated--grow-in" aria-labelledby="searchDropdown">
                                <form class="form-inline me-auto w-100 navbar-search">
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-light border-0 small" placeholder="Rechercher..." aria-label="Search" aria-describedby="basic-addon2">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button">
                                                <i class="fas fa-search fa-sm"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="me-2 d-none d-lg-inline text-gray-600 small">{{ auth()->guard('admin')->user()->name }}</span>
                                <i class="fas fa-user-circle fa-fw fa-lg text-gray-600"></i>
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="{{ route('admin.settings.index') }}">
                                    <i class="fas fa-cog fa-sm fa-fw me-2 text-gray-400"></i>
                                    Paramètres
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw me-2 text-gray-400"></i>
                                    Déconnexion
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <main>
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @yield('content')
                </main>
                <!-- End of Page Content -->
            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; COD Manager TN {{ date('Y') }}</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Prêt à partir?</h5>
                    <button class="close" type="button" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Sélectionnez "Déconnexion" ci-dessous si vous êtes prêt à mettre fin à votre session en cours.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Annuler</button>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">Déconnexion</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Core plugin JavaScript-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
    
    <!-- Custom scripts -->
    <script>
        // Toggle the side navigation
        document.getElementById('sidebarToggle').addEventListener('click', function(e) {
            document.body.classList.toggle('sidebar-toggled');
            document.querySelector('.sidebar').classList.toggle('toggled');
        });
        
        document.getElementById('sidebarToggleTop').addEventListener('click', function(e) {
            document.body.classList.toggle('sidebar-toggled');
            document.querySelector('.sidebar').classList.toggle('toggled');
        });
        
        // Close any open menu accordions when window is resized
        window.addEventListener('resize', function() {
            if (window.innerWidth < 768) {
                document.body.classList.add('sidebar-toggled');
                document.querySelector('.sidebar').classList.add('toggled');
            }
        });
        
        // Scroll to top button appear
        document.addEventListener('scroll', function() {
            const scrollToTop = document.querySelector('.scroll-to-top');
            if (document.documentElement.scrollTop > 100) {
                scrollToTop.style.display = 'block';
            } else {
                scrollToTop.style.display = 'none';
            }
        });
    </script>
    
    @yield('scripts')
</body>
</html>
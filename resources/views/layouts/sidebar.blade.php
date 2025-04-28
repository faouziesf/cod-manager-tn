@if(Auth::guard('admin')->check() || Auth::check())
    @if(Auth::guard('admin')->check() && Auth::guard('admin')->user()->is_super_admin)
    <!-- Sidebar pour Super Admin -->
    <div class="col-md-2 col-lg-2 d-md-block bg-dark sidebar collapse">
        <div class="position-sticky pt-3">
            <div class="text-center mb-4 text-white">
                <h4 class="fw-bold">COD Manager TN</h4>
                <p class="text-muted small">
                    {{ Auth::guard('admin')->user()->name }}
                    <small class="d-block text-muted">Super Admin</small>
                </p>
            </div>
            
            <ul class="nav flex-column">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}" href="{{ route('superadmin.dashboard') }}">
                        <i class="bi bi-speedometer2"></i> Tableau de bord
                    </a>
                </li>
                
                <li class="nav-section">
                    <div class="nav-section-title text-white-50">Administration</div>
                </li>
                
                <!-- Gestion des administrateurs -->
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('superadmin.admins.*') ? 'active' : '' }}" href="{{ route('superadmin.admins.index') }}">
                        <i class="bi bi-people-fill"></i> Administrateurs
                    </a>
                </li>
                
                <!-- Paramètres -->
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('superadmin.settings.*') ? 'active' : '' }}" href="{{ route('superadmin.settings.index') }}">
                        <i class="bi bi-gear"></i> Paramètres système
                    </a>
                </li>
                
                <li class="nav-section">
                    <div class="nav-section-title text-white-50">Compte</div>
                </li>
                <li class="nav-item">
                    <form method="POST" action="{{ route('superadmin.logout') }}">
                        @csrf
                        <a class="nav-link text-white" href="{{ route('superadmin.logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                            <i class="bi bi-box-arrow-right"></i> Déconnexion
                        </a>
                    </form>
                </li>
            </ul>
        </div>
    </div>
    @elseif(Auth::guard('admin')->check() && !Auth::guard('admin')->user()->is_super_admin)
    <!-- Sidebar pour Admin -->
    <div class="col-md-2 col-lg-2 d-md-block bg-dark sidebar collapse">
        <div class="position-sticky pt-3">
            <div class="text-center mb-4 text-white">
                <h4 class="fw-bold">COD Manager TN</h4>
                <p class="text-muted small">
                    {{ Auth::guard('admin')->user()->name }}
                    <small class="d-block text-muted">Administrateur</small>
                </p>
            </div>
            
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-speedometer2"></i> Tableau de bord
                    </a>
                </li>
                
                <li class="nav-section">
                    <div class="nav-section-title text-white-50">Commandes</div>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('admin.orders.standard') ? 'active' : '' }}" href="{{ route('admin.orders.standard') }}">
                        <i class="bi bi-telephone-fill"></i> Standard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('admin.orders.scheduled') ? 'active' : '' }}" href="{{ route('admin.orders.scheduled') }}">
                        <i class="bi bi-calendar-event"></i> Datées
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('admin.orders.old') ? 'active' : '' }}" href="{{ route('admin.orders.old') }}">
                        <i class="bi bi-archive"></i> Anciennes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('admin.orders.search') ? 'active' : '' }}" href="{{ route('admin.orders.search') }}">
                        <i class="bi bi-search"></i> Recherche
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('admin.orders.create') ? 'active' : '' }}" href="{{ route('admin.orders.create') }}">
                        <i class="bi bi-plus-circle"></i> Nouvelle commande
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('admin.orders.needs-verification') ? 'active' : '' }}" href="{{ route('admin.orders.needs-verification') }}">
                        <i class="bi bi-exclamation-triangle"></i> À vérifier
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('admin.orders.import') ? 'active' : '' }}" href="{{ route('admin.orders.import') }}">
                        <i class="bi bi-upload"></i> Importation
                    </a>
                </li>
                
                <li class="nav-section">
                    <div class="nav-section-title text-white-50">Catalogue</div>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">
                        <i class="bi bi-box-seam"></i> Produits
                    </a>
                </li>
                
                <li class="nav-section">
                    <div class="nav-section-title text-white-50">Administration</div>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                        <i class="bi bi-people"></i> Utilisateurs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}">
                        <i class="bi bi-gear"></i> Paramètres
                    </a>
                </li>
                
                <li class="nav-section">
                    <div class="nav-section-title text-white-50">Compte</div>
                </li>
                <li class="nav-item">
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <a class="nav-link text-white" href="{{ route('admin.logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                            <i class="bi bi-box-arrow-right"></i> Déconnexion
                        </a>
                    </form>
                </li>
            </ul>
        </div>
    </div>
    @elseif(Auth::check() && Auth::user()->role == 'manager')
    <!-- Sidebar pour Manager -->
    <div class="col-md-2 col-lg-2 d-md-block bg-dark sidebar collapse">
        <div class="position-sticky pt-3">
            <div class="text-center mb-4 text-white">
                <h4 class="fw-bold">COD Manager TN</h4>
                <p class="text-muted small">
                    {{ Auth::user()->name }}
                    <small class="d-block text-muted">Manager</small>
                </p>
            </div>
            
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('manager.dashboard') ? 'active' : '' }}" href="{{ route('manager.dashboard') }}">
                        <i class="bi bi-speedometer2"></i> Tableau de bord
                    </a>
                </li>
                
                <li class="nav-section">
                    <div class="nav-section-title text-white-50">Commandes</div>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('manager.orders.standard') ? 'active' : '' }}" href="{{ route('manager.orders.standard') }}">
                        <i class="bi bi-telephone-fill"></i> Standard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('manager.orders.scheduled') ? 'active' : '' }}" href="{{ route('manager.orders.scheduled') }}">
                        <i class="bi bi-calendar-event"></i> Datées
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('manager.orders.old') ? 'active' : '' }}" href="{{ route('manager.orders.old') }}">
                        <i class="bi bi-archive"></i> Anciennes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('manager.orders.search') ? 'active' : '' }}" href="{{ route('manager.orders.search') }}">
                        <i class="bi bi-search"></i> Recherche
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('manager.orders.create') ? 'active' : '' }}" href="{{ route('manager.orders.create') }}">
                        <i class="bi bi-plus-circle"></i> Nouvelle commande
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('manager.orders.needs-verification') ? 'active' : '' }}" href="{{ route('manager.orders.needs-verification') }}">
                        <i class="bi bi-exclamation-triangle"></i> À vérifier
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('manager.orders.import') ? 'active' : '' }}" href="{{ route('manager.orders.import') }}">
                        <i class="bi bi-upload"></i> Importation
                    </a>
                </li>
                
                <li class="nav-section">
                    <div class="nav-section-title text-white-50">Catalogue</div>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('manager.products.*') ? 'active' : '' }}" href="{{ route('manager.products.index') }}">
                        <i class="bi bi-box-seam"></i> Produits
                    </a>
                </li>
                
                <li class="nav-section">
                    <div class="nav-section-title text-white-50">Compte</div>
                </li>
                <li class="nav-item">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a class="nav-link text-white" href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                            <i class="bi bi-box-arrow-right"></i> Déconnexion
                        </a>
                    </form>
                </li>
            </ul>
        </div>
    </div>
    @elseif(Auth::check())
    <!-- Sidebar pour Employee -->
    <div class="col-md-2 col-lg-2 d-md-block bg-dark sidebar collapse">
        <div class="position-sticky pt-3">
            <div class="text-center mb-4 text-white">
                <h4 class="fw-bold">COD Manager TN</h4>
                <p class="text-muted small">
                    {{ Auth::user()->name }}
                    <small class="d-block text-muted">Employé</small>
                </p>
            </div>
            
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}" href="{{ route('employee.dashboard') }}">
                        <i class="bi bi-speedometer2"></i> Tableau de bord
                    </a>
                </li>
                
                <li class="nav-section">
                    <div class="nav-section-title text-white-50">Commandes</div>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('employee.orders.standard') ? 'active' : '' }}" href="{{ route('employee.orders.standard') }}">
                        <i class="bi bi-telephone-fill"></i> Standard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('employee.orders.scheduled') ? 'active' : '' }}" href="{{ route('employee.orders.scheduled') }}">
                        <i class="bi bi-calendar-event"></i> Datées
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('employee.orders.old') ? 'active' : '' }}" href="{{ route('employee.orders.old') }}">
                        <i class="bi bi-archive"></i> Anciennes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->routeIs('employee.orders.search') ? 'active' : '' }}" href="{{ route('employee.orders.search') }}">
                        <i class="bi bi-search"></i> Recherche
                    </a>
                </li>
                
                <li class="nav-section">
                    <div class="nav-section-title text-white-50">Compte</div>
                </li>
                <li class="nav-item">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a class="nav-link text-white" href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                            <i class="bi bi-box-arrow-right"></i> Déconnexion
                        </a>
                    </form>
                </li>
            </ul>
        </div>
    </div>
    @endif
@endif
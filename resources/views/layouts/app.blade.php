<!-- Sidebar -->
<div class="col-md-2 col-lg-2 d-md-block bg-white sidebar collapse">
    <div class="position-sticky pt-3">
        <div class="text-center mb-4">
            <h4 class="fw-bold">COD Manager TN</h4>
            <p class="text-muted small">Bienvenue, {{ Auth::user()->name }}</p>
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-section">
                <div class="nav-section-title">Menu principal</div>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2"></i> Tableau de bord
                </a>
            </li>
            
            <li class="nav-section">
                <div class="nav-section-title">Commandes</div>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('orders.standard') ? 'active' : '' }}" href="{{ route('orders.standard') }}">
                    <i class="bi bi-telephone-fill"></i> Standard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('orders.scheduled') ? 'active' : '' }}" href="{{ route('orders.scheduled') }}">
                    <i class="bi bi-calendar-event"></i> Datées
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('orders.old') ? 'active' : '' }}" href="{{ route('orders.old') }}">
                    <i class="bi bi-archive"></i> Anciennes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('orders.search') ? 'active' : '' }}" href="{{ route('orders.search') }}">
                    <i class="bi bi-search"></i> Recherche
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('orders.create') ? 'active' : '' }}" href="{{ route('orders.create') }}">
                    <i class="bi bi-plus-circle"></i> Nouvelle commande
                </a>
            </li>
            
            @if(auth()->user()->hasAnyRole(['admin', 'superadmin', 'manager']))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('orders.needs-verification') ? 'active' : '' }}" href="{{ route('orders.needs-verification') }}">
                        <i class="bi bi-exclamation-triangle"></i> À vérifier
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('orders.import') ? 'active' : '' }}" href="{{ route('orders.import') }}">
                        <i class="bi bi-upload"></i> Importation
                    </a>
                </li>
            @endif
            
            <li class="nav-section">
                <div class="nav-section-title">Catalogue</div>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                    <i class="bi bi-box-seam"></i> Produits
                </a>
            </li>
            
            @if(auth()->user()->hasAnyRole(['admin', 'superadmin']))
                <li class="nav-section">
                    <div class="nav-section-title">Administration</div>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.index') }}">
                        <i class="bi bi-gear"></i> Paramètres
                    </a>
                </li>
                
                @if(auth()->user()->hasRole('superadmin'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                        <i class="bi bi-people"></i> Utilisateurs
                    </a>
                </li>
                @endif
            @endif
            
            <li class="nav-section">
                <div class="nav-section-title">Compte</div>
            </li>
            <li class="nav-item">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a class="nav-link" href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                        <i class="bi bi-box-arrow-right"></i> Déconnexion
                    </a>
                </form>
            </li>
        </ul>
    </div>
</div>
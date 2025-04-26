@extends('layouts.app')

@extends('layouts.sidebar')

@section('navbar-right')
    <li class="nav-item dropdown">
        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
            {{ Auth::user()->name }}
        </a>

        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
            <a class="dropdown-item" href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                {{ __('Déconnexion') }}
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </li>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Tableau de bord Manager') }}</div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card mb-3">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ __('Commandes') }}</h5>
                                    <p class="card-text display-4">{{ $totalOrders ?? 0 }}</p>
                                    <a href="{{ route('manager.orders.index') }}" class="btn btn-primary">Gérer</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card mb-3">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ __('À confirmer') }}</h5>
                                    <p class="card-text display-4">{{ $newOrders ?? 0 }}</p>
                                    <a href="{{ route('manager.orders.standard') }}" class="btn btn-info">Voir</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card mb-3">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ __('Datées') }}</h5>
                                    <p class="card-text display-4">{{ $datedOrders ?? 0 }}</p>
                                    <a href="{{ route('manager.orders.dated') }}" class="btn btn-warning">Voir</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span>{{ __('Dernières actions') }}</span>
                                    <a href="{{ route('manager.orders.create') }}" class="btn btn-success btn-sm">Nouvelle commande</a>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <h5>Bienvenue sur votre tableau de bord</h5>
                                        <p>En tant que Manager, vous pouvez :</p>
                                        <ul>
                                            <li>Voir toutes les commandes de votre entreprise</li>
                                            <li>Créer de nouvelles commandes</li>
                                            <li>Assigner des commandes aux employés</li>
                                            <li>Suivre l'évolution des commandes</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
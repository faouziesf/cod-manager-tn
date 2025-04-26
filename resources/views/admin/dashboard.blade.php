@extends('layouts.app')

@section('navbar-left')
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('admin.dashboard') }}">{{ __('Tableau de bord') }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#">{{ __('Utilisateurs') }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#">{{ __('Produits') }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#">{{ __('Commandes') }}</a>
    </li>
@endsection

@section('navbar-right')
    <li class="nav-item dropdown">
        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
            {{ Auth::guard('admin')->user()->name }}
        </a>

        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
            <a class="dropdown-item" href="{{ route('admin.logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                {{ __('Déconnexion') }}
            </a>

            <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
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
                <div class="card-header">{{ __('Tableau de bord Administrateur') }}</div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card mb-3">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ __('Utilisateurs') }}</h5>
                                    <p class="card-text display-4">{{ $totalUsers ?? 0 }}</p>
                                    <a href="{{ route('admin.users.index') }}" class="btn btn-primary">Gérer</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card mb-3">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ __('Produits') }}</h5>
                                    <p class="card-text display-4">{{ $totalProducts ?? 0 }}</p>
                                    <a href="#" class="btn btn-primary">Gérer</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card mb-3">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ __('Commandes') }}</h5>
                                    <p class="card-text display-4">{{ $totalOrders ?? 0 }}</p>
                                    <a href="{{ route('admin.orders.index') }}" class="btn btn-primary">Gérer</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    {{ __('Statuts des commandes') }}
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="alert alert-info text-center">
                                                <h5>{{ __('Nouvelles') }}</h5>
                                                <span class="display-6">{{ $newOrders ?? 0 }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="alert alert-success text-center">
                                                <h5>{{ __('Confirmées') }}</h5>
                                                <span class="display-6">{{ $confirmedOrders ?? 0 }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="alert alert-warning text-center">
                                                <h5>{{ __('Datées') }}</h5>
                                                <span class="display-6">{{ $datedOrders ?? 0 }}</span>
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
    </div>
</div>
@endsection
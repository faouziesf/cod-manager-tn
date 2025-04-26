@extends('layouts.app')

@section('navbar-left')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.dashboard') }}">{{ __('Tableau de bord') }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.users.index') }}">{{ __('Utilisateurs') }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#">{{ __('Produits') }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#">{{ __('Commandes') }}</a>
    </li>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Détails de l\'utilisateur') }}</div>

                <div class="card-body">
                    <div class="mb-3 row">
                        <label class="col-md-4 col-form-label text-md-end fw-bold">{{ __('ID') }}:</label>
                        <div class="col-md-6">
                            <p class="form-control-static">{{ $user->id }}</p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-md-4 col-form-label text-md-end fw-bold">{{ __('Nom') }}:</label>
                        <div class="col-md-6">
                            <p class="form-control-static">{{ $user->name }}</p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-md-4 col-form-label text-md-end fw-bold">{{ __('Email') }}:</label>
                        <div class="col-md-6">
                            <p class="form-control-static">{{ $user->email }}</p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-md-4 col-form-label text-md-end fw-bold">{{ __('Rôle') }}:</label>
                        <div class="col-md-6">
                            @if ($user->role == 'manager')
                                <span class="badge bg-primary">Manager</span>
                            @else
                                <span class="badge bg-secondary">Employé</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-md-4 col-form-label text-md-end fw-bold">{{ __('Statut') }}:</label>
                        <div class="col-md-6">
                            @if ($user->active)
                                <span class="badge bg-success">Actif</span>
                            @else
                                <span class="badge bg-danger">Inactif</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-md-4 col-form-label text-md-end fw-bold">{{ __('Créé le') }}:</label>
                        <div class="col-md-6">
                            <p class="form-control-static">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-md-4 col-form-label text-md-end fw-bold">{{ __('Mis à jour le') }}:</label>
                        <div class="col-md-6">
                            <p class="form-control-static">{{ $user->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <div class="mb-3 row mb-0">
                        <div class="col-md-6 offset-md-4">
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                                {{ __('Modifier') }}
                            </a>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                {{ __('Retour à la liste') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
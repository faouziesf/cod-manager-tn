@extends('layouts.app')

@section('navbar-left')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('superadmin.dashboard') }}">{{ __('Tableau de bord') }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('superadmin.admins.index') }}">{{ __('Administrateurs') }}</a>
    </li>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Détails de l\'administrateur') }}</div>

                <div class="card-body">
                    <div class="mb-3 row">
                        <label class="col-md-4 col-form-label text-md-end fw-bold">{{ __('ID') }}:</label>
                        <div class="col-md-6">
                            <p class="form-control-static">{{ $admin->id }}</p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-md-4 col-form-label text-md-end fw-bold">{{ __('Nom') }}:</label>
                        <div class="col-md-6">
                            <p class="form-control-static">{{ $admin->name }}</p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-md-4 col-form-label text-md-end fw-bold">{{ __('Email') }}:</label>
                        <div class="col-md-6">
                            <p class="form-control-static">{{ $admin->email }}</p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-md-4 col-form-label text-md-end fw-bold">{{ __('Statut') }}:</label>
                        <div class="col-md-6">
                            @if ($admin->active)
                                <span class="badge bg-success">Actif</span>
                            @else
                                <span class="badge bg-danger">Inactif</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-md-4 col-form-label text-md-end fw-bold">{{ __('Date d\'expiration') }}:</label>
                        <div class="col-md-6">
                            <p class="form-control-static">{{ $admin->expiry_date ? $admin->expiry_date->format('d/m/Y') : 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-md-4 col-form-label text-md-end fw-bold">{{ __('Créé le') }}:</label>
                        <div class="col-md-6">
                            <p class="form-control-static">{{ $admin->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-md-4 col-form-label text-md-end fw-bold">{{ __('Mis à jour le') }}:</label>
                        <div class="col-md-6">
                            <p class="form-control-static">{{ $admin->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <div class="mb-3 row mb-0">
                        <div class="col-md-6 offset-md-4">
                            <a href="{{ route('superadmin.admins.edit', $admin) }}" class="btn btn-primary">
                                {{ __('Modifier') }}
                            </a>
                            <a href="{{ route('superadmin.admins.index') }}" class="btn btn-secondary">
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
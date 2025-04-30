@extends('layouts.manager')

@section('title', 'Mon Profil')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Mon Profil</h1>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Profile Information -->
        <div class="col-xl-6 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informations personnelles</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('manager.profile.update') }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Nom complet</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Adresse e-mail</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="role" class="form-label">Rôle</label>
                            <input type="text" class="form-control" id="role" value="{{ ucfirst($user->role) }}" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label for="created_at" class="form-label">Compte créé le</label>
                            <input type="text" class="form-control" id="created_at" value="{{ $user->created_at->format('d/m/Y') }}" readonly>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Enregistrer les modifications
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Change Password -->
        <div class="col-xl-6 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Changer de mot de passe</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('manager.profile.password') }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Mot de passe actuel</label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Nouveau mot de passe</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirmer le nouveau mot de passe</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key mr-1"></i> Changer le mot de passe
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Account Status -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statut du compte</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">État du compte</label>
                        <div class="d-flex align-items-center">
                            @if($user->active)
                                <span class="badge bg-success text-white p-2 me-2">Actif</span>
                                <span class="text-success">Votre compte est actif et en bon état.</span>
                            @else
                                <span class="badge bg-danger text-white p-2 me-2">Désactivé</span>
                                <span class="text-danger">Votre compte a été désactivé. Veuillez contacter votre administrateur.</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle me-2"></i> Si vous rencontrez des problèmes avec votre compte, veuillez contacter votre administrateur système.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
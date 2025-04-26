@extends('layouts.app')

@section('navbar-left')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('superadmin.dashboard') }}">{{ __('Tableau de bord') }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('superadmin.admins.index') }}">{{ __('Administrateurs') }}</a>
    </li>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('Liste des administrateurs') }}</span>
                    <a href="{{ route('superadmin.admins.create') }}" class="btn btn-primary btn-sm">{{ __('Ajouter') }}</a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Statut</th>
                                    <th>Date d'expiration</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($admins as $admin)
                                    <tr>
                                        <td>{{ $admin->id }}</td>
                                        <td>{{ $admin->name }}</td>
                                        <td>{{ $admin->email }}</td>
                                        <td>
                                            @if ($admin->active)
                                                <span class="badge bg-success">Actif</span>
                                            @else
                                                <span class="badge bg-danger">Inactif</span>
                                            @endif
                                        </td>
                                        <td>{{ $admin->expiry_date ? $admin->expiry_date->format('d/m/Y') : 'N/A' }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('superadmin.admins.show', $admin) }}" class="btn btn-info btn-sm">Voir</a>
                                                <a href="{{ route('superadmin.admins.edit', $admin) }}" class="btn btn-primary btn-sm">Modifier</a>
                                                <form action="{{ route('superadmin.admins.destroy', $admin) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet administrateur ?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Aucun administrateur trouvé</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
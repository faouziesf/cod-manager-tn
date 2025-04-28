@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('Recherche de commandes') }}</span>
                    <a href="{{ route('manager.orders.create') }}" class="btn btn-primary btn-sm">{{ __('Nouvelle commande') }}</a>
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

                    <div class="mb-3">
                        <ul class="nav nav-tabs">
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('manager.orders.index') }}">Toutes les commandes</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('manager.orders.standard') }}">À confirmer</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('manager.orders.dated') }}">Datées</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('manager.orders.old') }}">Anciennes</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" href="{{ route('manager.orders.search') }}">Recherche</a>
                            </li>
                        </ul>
                    </div>

                    <form action="{{ route('manager.orders.search') }}" method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="customer_name" class="form-label">Nom du client</label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name" value="{{ request('customer_name') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="customer_phone" class="form-label">Téléphone</label>
                                <input type="text" class="form-control" id="customer_phone" name="customer_phone" value="{{ request('customer_phone') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="city" class="form-label">Ville</label>
                                <input type="text" class="form-control" id="city" name="city" value="{{ request('city') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="status" class="form-label">Statut</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">Tous les statuts</option>
                                    <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>À confirmer</option>
                                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                                    <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Datée</option>
                                    <option value="old" {{ request('status') == 'old' ? 'selected' : '' }}>Ancienne</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulée</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="user_id" class="form-label">Assigné à</label>
                                <select class="form-select" id="user_id" name="user_id">
                                    <option value="">Tous les utilisateurs</option>
                                    @foreach($employees ?? [] as $employee)
                                        <option value="{{ $employee->id }}" {{ request('user_id') == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">Rechercher</button>
                                <a href="{{ route('manager.orders.search') }}" class="btn btn-secondary">Réinitialiser</a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client</th>
                                    <th>Téléphone</th>
                                    <th>Produits</th>
                                    <th>Statut</th>
                                    <th>Assigné à</th>
                                    <th>Date création</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($orders ?? [] as $order)
                                    <tr>
                                        <td>{{ $order->id }}</td>
                                        <td>{{ $order->customer_name }}</td>
                                        <td>{{ $order->customer_phone1 }}</td>
                                        <td>
                                            @if($order->products->count() > 0)
                                                {{ $order->products->count() }} produit(s)
                                            @else
                                                Aucun produit
                                            @endif
                                        </td>
                                        <td>
                                            @if ($order->status == 'new')
                                                <span class="badge bg-info">À confirmer</span>
                                            @elseif ($order->status == 'confirmed')
                                                <span class="badge bg-success">Confirmée</span>
                                            @elseif ($order->status == 'scheduled')
                                                <span class="badge bg-warning">Datée</span>
                                            @elseif ($order->status == 'old')
                                                <span class="badge bg-secondary">Ancienne</span>
                                            @elseif ($order->status == 'cancelled')
                                                <span class="badge bg-danger">Annulée</span>
                                            @endif
                                        </td>
                                        <td>{{ optional($order->assignedTo)->name ?? 'Non assignée' }}</td>
                                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('manager.orders.show', $order) }}" class="btn btn-info btn-sm">Voir</a>
                                                <a href="{{ route('manager.orders.edit', $order) }}" class="btn btn-primary btn-sm">Modifier</a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Aucune commande trouvée</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ isset($orders) ? $orders->appends(request()->except('page'))->links() : '' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
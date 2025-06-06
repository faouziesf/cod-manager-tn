@extends('layouts.app')

@extends('layouts.sidebar')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('Gestion des commandes') }}</span>
                    <a href="{{ route('admin.orders.create') }}" class="btn btn-primary btn-sm">{{ __('Nouvelle commande') }}</a>
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
                                <a class="nav-link active" href="{{ route('admin.orders.index') }}">Toutes les commandes</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.orders.standard') }}">À confirmer</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.orders.dated') }}">Datées</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.orders.old') }}">Anciennes</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.orders.search') }}">Recherche</a>
                            </li>
                        </ul>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client</th>
                                    <th>Téléphone</th>
                                    <th>Produit</th>
                                    <th>Statut</th>
                                    <th>Assigné à</th>
                                    <th>Date création</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($orders as $order)
                                    <tr>
                                        <td>{{ $order->id }}</td>
                                        <td>{{ $order->customer_name }}</td>
                                        <td>{{ $order->customer_phone1 }}</td>
                                        <td>{{ $order->product->name }}</td>
                                        <td>
                                            @if ($order->status == 'new')
                                                <span class="badge bg-info">À confirmer</span>
                                            @elseif ($order->status == 'confirmed')
                                                <span class="badge bg-success">Confirmée</span>
                                            @elseif ($order->status == 'dated')
                                                <span class="badge bg-warning">Datée ({{ $order->callback_date->format('d/m/Y') }})</span>
                                            @elseif ($order->status == 'recall')
                                                <span class="badge bg-secondary">À rappeler ({{ $order->current_attempts }}/{{ $order->max_attempts }})</span>
                                            @elseif ($order->status == 'canceled')
                                                <span class="badge bg-danger">Annulée</span>
                                            @endif
                                        </td>
                                        <td>{{ $order->user ? $order->user->name : 'Non assignée' }}</td>
                                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-info btn-sm">Voir</a>
                                                <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-primary btn-sm">Modifier</a>
                                                <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette commande ?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                                                </form>
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
                        {{ $orders->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
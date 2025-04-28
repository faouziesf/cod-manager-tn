@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ $title ?? 'Commandes datées' }}</span>
                    <div>
                        <a href="{{ route('manager.dashboard') }}" class="btn btn-outline-secondary btn-sm me-2">Retour au tableau de bord</a>
                        <a href="{{ route('manager.orders.create') }}" class="btn btn-success btn-sm">Nouvelle commande</a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
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
                                <a class="nav-link active" href="{{ route('manager.orders.dated') }}">Datées</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('manager.orders.old') }}">Anciennes</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('manager.orders.search') }}">Recherche</a>
                            </li>
                        </ul>
                    </div>

                    @if ($orders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client</th>
                                    <th>Téléphone</th>
                                    <th>Produits</th>
                                    <th>Total</th>
                                    <th>Date de livraison</th>
                                    <th>Assigné à</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                <tr>
                                    <td>{{ $order->id }}</td>
                                    <td>{{ $order->customer_name }}</td>
                                    <td>{{ $order->customer_phone1 }}</td>
                                    <td>
                                        @if ($order->products->count() > 0)
                                            {{ $order->products->count() }} produit(s)
                                        @else
                                            Aucun produit
                                        @endif
                                    </td>
                                    <td>{{ $order->total_price }} TND</td>
                                    <td>{{ $order->delivery_date ? date('d/m/Y', strtotime($order->delivery_date)) : 'Non programmée' }}</td>
                                    <td>
                                        @if ($order->assignedTo)
                                            {{ $order->assignedTo->name }}
                                        @else
                                            Non assigné
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('manager.orders.show', $order) }}" class="btn btn-info btn-sm">Voir</a>
                                            <a href="{{ route('manager.orders.edit', $order) }}" class="btn btn-primary btn-sm">Modifier</a>
                                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteOrderModal{{ $order->id }}">
                                                Supprimer
                                            </button>
                                        </div>

                                        <!-- Modal de confirmation de suppression -->
                                        <div class="modal fade" id="deleteOrderModal{{ $order->id }}" tabindex="-1" aria-labelledby="deleteOrderModalLabel{{ $order->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteOrderModalLabel{{ $order->id }}">Confirmer la suppression</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Êtes-vous sûr de vouloir supprimer cette commande ? Cette action est irréversible.
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                        <form action="{{ route('manager.orders.destroy', $order) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">Supprimer</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $orders->links() }}
                    </div>
                    @else
                    <div class="alert alert-info">
                        Aucune commande datée n'est disponible.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
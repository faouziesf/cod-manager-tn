@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Commandes à vérifier</h2>
    </div>
    
    @if($orders->isEmpty())
        <div class="text-center my-5 py-5">
            <div class="mb-4">
                <i class="bi bi-check2-circle" style="font-size: 3rem;"></i>
                <h3 class="mt-3">Aucune commande à vérifier</h3>
                <p class="text-muted">Toutes les commandes ont les produits en stock.</p>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> Ces commandes contiennent des produits en rupture de stock et nécessitent votre attention.
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Client</th>
                                <th>Téléphone</th>
                                <th>Produits</th>
                                <th>Statut</th>
                                <th>Date de création</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td>{{ $order->id }}</td>
                                    <td>{{ $order->customer_name }}</td>
                                    <td>{{ $order->customer_phone1 }}</td>
                                    <td>
                                        <ul class="list-unstyled mb-0">
                                            @foreach($order->products as $product)
                                                <li>
                                                    {{ $product->name }} ({{ $product->pivot->quantity }})
                                                    @if($product->stock <= 0)
                                                        <span class="badge bg-danger">Rupture</span>
                                                    @elseif($product->stock < $product->pivot->quantity)
                                                        <span class="badge bg-warning">Stock insuffisant</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td>
                                        @if($order->status == 'new')
                                            <span class="badge bg-info">Nouvelle</span>
                                        @elseif($order->status == 'confirmed')
                                            <span class="badge bg-success">Confirmée</span>
                                        @elseif($order->status == 'cancelled')
                                            <span class="badge bg-danger">Annulée</span>
                                        @elseif($order->status == 'scheduled')
                                            <span class="badge bg-primary">Datée</span>
                                        @elseif($order->status == 'old')
                                            <span class="badge bg-secondary">Ancienne</span>
                                        @endif
                                    </td>
                                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('manager.orders.show', $order) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> Voir
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-center mt-4">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
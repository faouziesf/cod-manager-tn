@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Recherche de commandes</h2>
    </div>
    
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.orders.search') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="search" class="form-label">Recherche</label>
                        <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Nom, téléphone, adresse, ID...">
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Statut</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Tous les statuts</option>
                            <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>Nouvelle</option>
                            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulée</option>
                            <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Datée</option>
                            <option value="old" {{ request('status') == 'old' ? 'selected' : '' }}>Ancienne</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Rechercher
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card shadow-sm">
        <div class="card-body">
            @if($orders->isEmpty())
                <div class="text-center my-5 py-5">
                    <div class="mb-4">
                        <i class="bi bi-search" style="font-size: 3rem;"></i>
                        <h3 class="mt-3">Aucun résultat</h3>
                        <p class="text-muted">Aucune commande ne correspond à votre recherche.</p>
                    </div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Client</th>
                                <th>Téléphone</th>
                                <th>Adresse</th>
                                <th>Produits</th>
                                <th>Total</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td>{{ $order->id }}</td>
                                    <td>{{ $order->customer_name }}</td>
                                    <td>{{ $order->customer_phone1 }}</td>
                                    <td>{{ $order->delivery_address }}, {{ $order->city }}, {{ $order->region }}</td>
                                    <td>
                                        <ul class="list-unstyled mb-0">
                                            @foreach($order->products as $product)
                                                <li>{{ $product->name }} ({{ $product->pivot->quantity }})</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td>{{ number_format($order->total_price, 3) }} TND</td>
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
                                    <td>{{ $order->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-center mt-4">
                    {{ $orders->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
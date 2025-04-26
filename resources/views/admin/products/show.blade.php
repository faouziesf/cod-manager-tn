@extends('layouts.app')

@extends('layouts.sidebar')


@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('Détails du produit') }}</span>
                    <div>
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary btn-sm">{{ __('Modifier') }}</a>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-sm">{{ __('Retour') }}</a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    <div class="row">
                        <div class="col-md-4">
                            @if($product->image_path)
                                <img src="{{ asset($product->image_path) }}" alt="{{ $product->name }}" class="img-fluid mb-3">
                            @else
                                <div class="text-center p-4 bg-light mb-3">
                                    <p class="text-muted">Aucune image</p>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <h4>{{ $product->name }}</h4>
                            <p><strong>ID:</strong> {{ $product->id }}</p>
                            <p><strong>Date de création:</strong> {{ $product->created_at->format('d/m/Y H:i') }}</p>
                            <p><strong>Dernière mise à jour:</strong> {{ $product->updated_at->format('d/m/Y H:i') }}</p>
                            
                            <h5 class="mt-4">Commandes associées</h5>
                            @if($product->orders->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Client</th>
                                                <th>Statut</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($product->orders()->latest()->take(5)->get() as $order)
                                                <tr>
                                                    <td>{{ $order->id }}</td>
                                                    <td>{{ $order->customer_name }}</td>
                                                    <td>
                                                        @if ($order->status == 'new')
                                                            <span class="badge bg-info">À confirmer</span>
                                                        @elseif ($order->status == 'confirmed')
                                                            <span class="badge bg-success">Confirmée</span>
                                                        @elseif ($order->status == 'dated')
                                                            <span class="badge bg-warning">Datée</span>
                                                        @elseif ($order->status == 'recall')
                                                            <span class="badge bg-secondary">À rappeler</span>
                                                        @elseif ($order->status == 'canceled')
                                                            <span class="badge bg-danger">Annulée</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $order->created_at->format('d/m/Y') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if($product->orders->count() > 5)
                                    <p class="text-muted">Affichage des 5 commandes les plus récentes</p>
                                @endif
                            @else
                                <p class="text-muted">Aucune commande associée à ce produit</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
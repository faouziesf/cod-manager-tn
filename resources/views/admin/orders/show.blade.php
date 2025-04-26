@extends('layouts.app')

@extends('layouts.sidebar')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('Détails de la commande #') }}{{ $order->id }}</span>
                    <div>
                        <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-primary btn-sm">{{ __('Modifier') }}</a>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm">{{ __('Retour') }}</a>
                    </div>
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

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header">Informations client</div>
                                <div class="card-body">
                                    <p><strong>Nom:</strong> {{ $order->customer_name }}</p>
                                    <p><strong>Téléphone 1:</strong> {{ $order->customer_phone1 }}</p>
                                    @if($order->customer_phone2)
                                        <p><strong>Téléphone 2:</strong> {{ $order->customer_phone2 }}</p>
                                    @endif
                                    <p><strong>Adresse:</strong> {{ $order->delivery_address }}</p>
                                    <p><strong>Région:</strong> {{ $order->region }}</p>
                                    <p><strong>Ville:</strong> {{ $order->city }}</p>
                                </div>
                                
                            </div>
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header">Détails de la commande</div>
                                <div class="card-body">
                                    <p><strong>Produit:</strong> {{ $order->product->name }}</p>
                                    <p><strong>Quantité:</strong> {{ $order->quantity }}</p>
                                    <p>
                                        <strong>Statut:</strong> 
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
                                    </p>
                                    <p><strong>Tentatives:</strong> {{ $order->current_attempts }}/{{ $order->max_attempts }}</p>
                                    <p><strong>Assignée à:</strong> {{ $order->user ? $order->user->name : 'Non assignée' }}</p>
                                    <p><strong>Date de création:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
                                    <p><strong>Dernière mise à jour:</strong> {{ $order->updated_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header">Historique</div>
                        <div class="card-body">
                            @if($history->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Utilisateur</th>
                                                <th>Statut</th>
                                                <th>Note</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($history as $entry)
                                                <tr>
                                                    <td>{{ $entry->created_at->format('d/m/Y H:i') }}</td>
                                                    <td>{{ $entry->user ? $entry->user->name : 'Système' }}</td>
                                                    <td>
                                                        @if ($entry->status == 'new')
                                                            <span class="badge bg-info">À confirmer</span>
                                                        @elseif ($entry->status == 'confirmed')
                                                            <span class="badge bg-success">Confirmée</span>
                                                        @elseif ($entry->status == 'dated')
                                                            <span class="badge bg-warning">Datée</span>
                                                        @elseif ($entry->status == 'recall')
                                                            <span class="badge bg-secondary">À rappeler</span>
                                                        @elseif ($entry->status == 'canceled')
                                                            <span class="badge bg-danger">Annulée</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $entry->private_note ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-center">Aucun historique disponible.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
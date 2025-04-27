@extends('layouts.app')

@extends('layouts.sidebar')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('Commandes datées') }}</span>
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
                                <a class="nav-link" href="{{ route('admin.orders.index') }}">Toutes les commandes</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.orders.standard') }}">À confirmer</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" href="{{ route('admin.orders.dated') }}">Datées</a>
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
                                    <th>Date de rappel</th>
                                    <th>Assigné à</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($orders as $order)
                                    <tr class="{{ now()->startOfDay()->gte($order->callback_date) ? 'table-warning' : '' }}">
                                        <td>{{ $order->id }}</td>
                                        <td>{{ $order->customer_name }}</td>
                                        <td>{{ $order->customer_phone1 }}</td>
                                        <td>{{ $order->product->name }}</td>
                                        <td>
                                            {{ $order->callback_date->format('d/m/Y') }}
                                            @if(now()->startOfDay()->gte($order->callback_date))
                                                <span class="badge bg-danger">Aujourd'hui</span>
                                            @elseif(now()->startOfDay()->addDay()->gte($order->callback_date))
                                                <span class="badge bg-warning">Demain</span>
                                            @endif
                                        </td>
                                        <td>{{ $order->user ? $order->user->name : 'Non assignée' }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-info btn-sm">Voir</a>
                                                <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-primary btn-sm">Modifier</a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Aucune commande datée trouvée</td>
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
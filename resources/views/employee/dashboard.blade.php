@extends('layouts.app')



@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Tableau de bord Employé') }}</div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card mb-3">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ __('Mes commandes') }}</h5>
                                    <p class="card-text display-4">{{ $totalOrders ?? 0 }}</p>
                                    <a href="{{ route('employee.orders.index') }}" class="btn btn-primary">Voir tout</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card mb-3">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ __('À confirmer') }}</h5>
                                    <p class="card-text display-4">{{ $newOrders ?? 0 }}</p>
                                    <a href="{{ route('employee.orders.standard') }}" class="btn btn-info">Voir</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card mb-3">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ __('Datées') }}</h5>
                                    <p class="card-text display-4">{{ $datedOrders ?? 0 }}</p>
                                    <a href="{{ route('employee.orders.dated') }}" class="btn btn-warning">Voir</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card mb-3">
                                <div class="card-body text-center">
                                    <h5 class="card-title">{{ __('Anciennes') }}</h5>
                                    <p class="card-text display-4">{{ $oldOrders ?? 0 }}</p>
                                    <a href="{{ route('employee.orders.old') }}" class="btn btn-secondary">Voir</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    {{ __('Commandes à traiter aujourd\'hui') }}
                                </div>
                                <div class="card-body">
                                    @if(isset($todayOrders) && $todayOrders->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Client</th>
                                                        <th>Téléphone</th>
                                                        <th>Produit</th>
                                                        <th>Statut</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($todayOrders as $order)
                                                        <tr>
                                                            <td>{{ $order->id }}</td>
                                                            <td>{{ $order->customer_name }}</td>
                                                            <td>{{ $order->customer_phone1 }}</td>
                                                            <td>{{ $order->product->name }}</td>
                                                            <td>
                                                                @if ($order->status == 'dated')
                                                                    <span class="badge bg-warning">Datée ({{ $order->callback_date->format('d/m/Y') }})</span>
                                                                @else
                                                                    <span class="badge bg-info">À confirmer</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <a href="{{ route('employee.orders.process', $order) }}" class="btn btn-primary btn-sm">Traiter</a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            <p class="mb-0">Aucune commande à traiter aujourd'hui.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
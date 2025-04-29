@extends('layouts.app')


@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">{{ __('Modifier la commande #') }}{{ $order->id }}</div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.orders.update', $order) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3 row">
                            <label for="product_id" class="col-md-3 col-form-label text-md-end">{{ __('Produit') }}</label>
                            <div class="col-md-8">
                                <select id="product_id" class="form-select @error('product_id') is-invalid @enderror" name="product_id" required>
                                    <option value="">-- Sélectionnez un produit --</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ old('product_id', $order->product_id) == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="user_id" class="col-md-3 col-form-label text-md-end">{{ __('Assigner à') }}</label>
                            <div class="col-md-8">
                                <select id="user_id" class="form-select @error('user_id') is-invalid @enderror" name="user_id">
                                    <option value="">-- Non assignée --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id', $order->user_id) == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ ucfirst($user->role) }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">
                        
                        <h5>Informations client</h5>

                        <div class="mb-3 row">
                            <label for="customer_name" class="col-md-3 col-form-label text-md-end">{{ __('Nom complet') }}</label>
                            <div class="col-md-8">
                                <input id="customer_name" type="text" class="form-control @error('customer_name') is-invalid @enderror" name="customer_name" value="{{ old('customer_name', $order->customer_name) }}" required>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="customer_phone1" class="col-md-3 col-form-label text-md-end">{{ __('Téléphone 1') }}</label>
                            <div class="col-md-8">
                                <input id="customer_phone1" type="text" class="form-control @error('customer_phone1') is-invalid @enderror" name="customer_phone1" value="{{ old('customer_phone1', $order->customer_phone1) }}" required>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="customer_phone2" class="col-md-3 col-form-label text-md-end">{{ __('Téléphone 2') }}</label>
                            <div class="col-md-8">
                                <input id="customer_phone2" type="text" class="form-control @error('customer_phone2') is-invalid @enderror" name="customer_phone2" value="{{ old('customer_phone2', $order->customer_phone2) }}">
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="delivery_address" class="col-md-3 col-form-label text-md-end">{{ __('Adresse de livraison') }}</label>
                            <div class="col-md-8">
                                <textarea id="delivery_address" class="form-control @error('delivery_address') is-invalid @enderror" name="delivery_address" rows="3" required>{{ old('delivery_address', $order->delivery_address) }}</textarea>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="region" class="col-md-3 col-form-label text-md-end">{{ __('Région') }}</label>
                            <div class="col-md-8">
                                <input id="region" type="text" class="form-control @error('region') is-invalid @enderror" name="region" value="{{ old('region', $order->region) }}" required>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="city" class="col-md-3 col-form-label text-md-end">{{ __('Ville') }}</label>
                            <div class="col-md-8">
                                <input id="city" type="text" class="form-control @error('city') is-invalid @enderror" name="city" value="{{ old('city', $order->city) }}" required>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5>Détails de la commande</h5>

                        <div class="mb-3 row">
                            <label for="quantity" class="col-md-3 col-form-label text-md-end">{{ __('Quantité') }}</label>
                            <div class="col-md-8">
                                <input id="quantity" type="number" class="form-control @error('quantity') is-invalid @enderror" name="quantity" value="{{ old('quantity', $order->quantity) }}" min="1" required>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="status" class="col-md-3 col-form-label text-md-end">{{ __('Statut') }}</label>
                            <div class="col-md-8">
                                <select id="status" class="form-select @error('status') is-invalid @enderror" name="status" required>
                                    <option value="new" {{ old('status', $order->status) == 'new' ? 'selected' : '' }}>À confirmer</option>
                                    <option value="confirmed" {{ old('status', $order->status) == 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                                    <option value="dated" {{ old('status', $order->status) == 'dated' ? 'selected' : '' }}>Datée</option>
                                    <option value="recall" {{ old('status', $order->status) == 'recall' ? 'selected' : '' }}>À rappeler</option>
                                    <option value="canceled" {{ old('status', $order->status) == 'canceled' ? 'selected' : '' }}>Annulée</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3 row" id="callback_date_container" style="{{ old('status', $order->status) == 'dated' ? '' : 'display: none;' }}">
                            <label for="callback_date" class="col-md-3 col-form-label text-md-end">{{ __('Date de rappel') }}</label>
                            <div class="col-md-8">
                                <input id="callback_date" type="date" class="form-control @error('callback_date') is-invalid @enderror" name="callback_date" value="{{ old('callback_date', $order->callback_date ? $order->callback_date->format('Y-m-d') : '') }}">
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="private_note" class="col-md-3 col-form-label text-md-end">{{ __('Note privée') }}</label>
                            <div class="col-md-8">
                                <textarea id="private_note" class="form-control @error('private_note') is-invalid @enderror" name="private_note" rows="3">{{ old('private_note') }}</textarea>
                                <small class="form-text text-muted">Cette note sera enregistrée dans l'historique de la commande.</small>
                            </div>
                        </div>

                        <div class="mb-3 row mb-0">
                            <div class="col-md-8 offset-md-3">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Mettre à jour') }}
                                </button>
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-secondary">
                                    {{ __('Annuler') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusSelect = document.getElementById('status');
        const callbackDateContainer = document.getElementById('callback_date_container');
        
        statusSelect.addEventListener('change', function() {
            if (this.value === 'dated') {
                callbackDateContainer.style.display = '';
            } else {
                callbackDateContainer.style.display = 'none';
            }
        });
    });
</script>
@endsection
@endsection
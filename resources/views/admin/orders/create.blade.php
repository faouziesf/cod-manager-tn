@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">{{ __('Nouvelle commande') }}</div>

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

                    <form method="POST" action="{{ route('admin.orders.store') }}">
                        @csrf

                        <div class="mb-3 row">
                            <label for="product_id" class="col-md-3 col-form-label text-md-end">{{ __('Produit') }}</label>
                            <div class="col-md-8">
                                <select id="product_id" class="form-select @error('product_id') is-invalid @enderror" name="product_id" required>
                                    <option value="">-- Sélectionnez un produit --</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
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
                                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ ucfirst($user->role) }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">
                        
                        <h5>Informations client</h5>

                        <div class="mb-3 row">
                            <label for="customer_name" class="col-md-3 col-form-label text-md-end">{{ __('Nom complet') }}</label>
                            <div class="col-md-8">
                                <input id="customer_name" type="text" class="form-control @error('customer_name') is-invalid @enderror" name="customer_name" value="{{ old('customer_name') }}" required>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="customer_phone1" class="col-md-3 col-form-label text-md-end">{{ __('Téléphone 1') }}</label>
                            <div class="col-md-8">
                                <input id="customer_phone1" type="text" class="form-control @error('customer_phone1') is-invalid @enderror" name="customer_phone1" value="{{ old('customer_phone1') }}" required>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="customer_phone2" class="col-md-3 col-form-label text-md-end">{{ __('Téléphone 2') }}</label>
                            <div class="col-md-8">
                                <input id="customer_phone2" type="text" class="form-control @error('customer_phone2') is-invalid @enderror" name="customer_phone2" value="{{ old('customer_phone2') }}">
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="delivery_address" class="col-md-3 col-form-label text-md-end">{{ __('Adresse de livraison') }}</label>
                            <div class="col-md-8">
                                <textarea id="delivery_address" class="form-control @error('delivery_address') is-invalid @enderror" name="delivery_address" rows="3" required>{{ old('delivery_address') }}</textarea>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="region" class="col-md-3 col-form-label text-md-end">{{ __('Région') }}</label>
                            <div class="col-md-8">
                                <input id="region" type="text" class="form-control @error('region') is-invalid @enderror" name="region" value="{{ old('region') }}" required>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="city" class="col-md-3 col-form-label text-md-end">{{ __('Ville') }}</label>
                            <div class="col-md-8">
                                <input id="city" type="text" class="form-control @error('city') is-invalid @enderror" name="city" value="{{ old('city') }}" required>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5>Détails de la commande</h5>

                        <div class="mb-3 row">
                            <label for="quantity" class="col-md-3 col-form-label text-md-end">{{ __('Quantité') }}</label>
                            <div class="col-md-8">
                                <input id="quantity" type="number" class="form-control @error('quantity') is-invalid @enderror" name="quantity" value="{{ old('quantity', 1) }}" min="1" required>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="max_attempts" class="col-md-3 col-form-label text-md-end">{{ __('Tentatives max') }}</label>
                            <div class="col-md-8">
                                <input id="max_attempts" type="number" class="form-control @error('max_attempts') is-invalid @enderror" name="max_attempts" value="{{ old('max_attempts', 3) }}" min="1" required>
                            </div>
                        </div>

                        <div class="mb-3 row mb-0">
                            <div class="col-md-8 offset-md-3">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Enregistrer') }}
                                </button>
                                <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
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
@endsection
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Détails du produit</h2>
        <div>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left"></i> Retour à la liste
            </a>
            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Modifier
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
    @endif

    @if(session('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i> {{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <!-- Informations de base -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Informations du produit</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Nom</h6>
                            <p class="text-primary fw-bold fs-5">{{ $product->name }}</p>
                            
                            <h6>Prix</h6>
                            <p class="fs-5">{{ $product->formatted_price }}</p>
                            
                            <h6>Catégorie</h6>
                            <p>{{ $product->category_name }}</p>
                            
                            <h6>SKU</h6>
                            <p>{{ $product->sku ?: 'Non défini' }}</p>
                        </div>
                        
                        <div class="col-md-6">
                            <h6>Stock</h6>
                            <p class="d-flex align-items-center">
                                <span class="me-3 fs-5">{{ $product->display_stock }}</span>
                                @if($product->is_infinite_stock)
                                    <span class="badge bg-primary">Infini</span>
                                @elseif($product->stock > 10)
                                    <span class="badge bg-success">En stock</span>
                                @elseif($product->stock > 0)
                                    <span class="badge bg-warning text-dark">Stock limité</span>
                                @else
                                    <span class="badge bg-danger">Épuisé</span>
                                @endif
                            </p>
                            
                            <h6>Statut</h6>
                            <p>
                                @if($product->active)
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-secondary">Inactif</span>
                                @endif
                            </p>
                            
                            <h6>ID externe</h6>
                            <p>{{ $product->external_id ?: 'Aucun' }}</p>
                            
                            <h6>Créé le</h6>
                            <p>{{ $product->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    
                    @if($product->description)
                        <h6>Description</h6>
                        <div class="p-3 bg-light rounded mb-3">
                            {{ $product->description }}
                        </div>
                    @endif
                    
                    <!-- Ajout rapide de stock -->
                    @if(!$product->is_infinite_stock)
                    <h6>Gestion du stock</h6>
                    <form action="{{ route('admin.products.update-stock', $product) }}" method="POST" class="mt-2">
                        @csrf
                        @method('PATCH')
                        <div class="row g-2 align-items-center">
                            <div class="col-auto">
                                <select class="form-select" name="operation" required>
                                    <option value="add">Ajouter</option>
                                    <option value="subtract">Retirer</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <input type="number" class="form-control" name="quantity" value="1" min="1" required>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary">Mettre à jour</button>
                            </div>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
            
            <!-- Dimensions -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-box me-2 text-primary"></i>Dimensions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center mb-3">
                            <div class="p-3 bg-light rounded">
                                <h6>Poids</h6>
                                <p class="fs-5 mb-0">{{ isset($product->dimensions['weight']) && $product->dimensions['weight'] ? $product->dimensions['weight'] . ' kg' : '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="p-3 bg-light rounded">
                                <h6>Longueur</h6>
                                <p class="fs-5 mb-0">{{ isset($product->dimensions['dimensions']['length']) && $product->dimensions['dimensions']['length'] ? $product->dimensions['dimensions']['length'] . ' cm' : '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="p-3 bg-light rounded">
                                <h6>Largeur</h6>
                                <p class="fs-5 mb-0">{{ isset($product->dimensions['dimensions']['width']) && $product->dimensions['dimensions']['width'] ? $product->dimensions['dimensions']['width'] . ' cm' : '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="p-3 bg-light rounded">
                                <h6>Hauteur</h6>
                                <p class="fs-5 mb-0">{{ isset($product->dimensions['dimensions']['height']) && $product->dimensions['dimensions']['height'] ? $product->dimensions['dimensions']['height'] . ' cm' : '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Attributs -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-tag me-2 text-primary"></i>Attributs</h5>
                </div>
                <div class="card-body">
                    @if($product->attributes && count((array)$product->attributes) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Valeur</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($product->attributes as $key => $value)
                                        <tr>
                                            <td>{{ $key }}</td>
                                            <td>{{ $value }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            Aucun attribut défini pour ce produit.
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Image du produit -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-image me-2 text-primary"></i>Image</h5>
                </div>
                <div class="card-body text-center">
                    @if($product->image_path)
                        <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" 
                            class="img-fluid rounded mb-3" style="max-height: 300px;">
                    @else
                        <div class="text-center p-5 bg-light rounded mb-3">
                            <i class="bi bi-image text-muted" style="font-size: 5rem;"></i>
                            <p class="text-muted mt-3">Aucune image disponible</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Actions -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-gear me-2 text-primary"></i>Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary">
                            <i class="bi bi-pencil me-2"></i> Modifier le produit
                        </a>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="bi bi-trash me-2"></i> Supprimer le produit
                        </button>
                    </div>
                </div>
            </div>

            <!-- Statistiques du produit -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-graph-up me-2 text-primary"></i>Statistiques</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Commandes confirmées</span>
                            <span class="badge bg-primary rounded-pill">{{ $product->orders()->where('status', 'confirmed')->count() }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Commandes en attente</span>
                            <span class="badge bg-warning text-dark rounded-pill">{{ $product->orders()->whereIn('status', ['new', 'scheduled'])->count() }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Commandes annulées</span>
                            <span class="badge bg-danger rounded-pill">{{ $product->orders()->where('status', 'cancelled')->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmation de suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer ce produit ?</p>
                <p class="text-danger">Cette action est irréversible.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form action="{{ route('admin.products.destroy', $product) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
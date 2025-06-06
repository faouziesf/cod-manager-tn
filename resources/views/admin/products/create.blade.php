@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Ajouter un nouveau produit</h2>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
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

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Informations du produit</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Informations de base -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nom du produit <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="sku" class="form-label">SKU</label>
                            <input type="text" class="form-control @error('sku') is-invalid @enderror" 
                                id="sku" name="sku" value="{{ old('sku') }}">
                            @error('sku')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="price" class="form-label">Prix <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" 
                                    id="price" name="price" value="{{ old('price') }}" required>
                                <span class="input-group-text">TND</span>
                            </div>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="category" class="form-label">Catégorie</label>
                            <input type="text" class="form-control @error('category') is-invalid @enderror" 
                                id="category" name="category" value="{{ old('category') }}" 
                                list="category-list">
                            <datalist id="category-list">
                                @foreach($categories as $category)
                                    <option value="{{ $category }}">
                                @endforeach
                            </datalist>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Informations avancées -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="stock" class="form-label">Stock</label>
                            <input type="number" class="form-control @error('stock') is-invalid @enderror" 
                                id="stock" name="stock" value="{{ old('stock') }}" min="0" placeholder="Laissez vide pour un stock infini">
                            <div class="form-text">Laissez vide pour un stock infini</div>
                            @error('stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="active" class="form-label d-block">Statut</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="active" name="active" 
                                    {{ old('active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="active">Produit actif</label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Image du produit</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                id="image" name="image" accept="image/*">
                            <div class="form-text">JPG, PNG, GIF. Max 2Mo.</div>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mt-3" id="image-preview-container" style="display: none;">
                            <label class="form-label">Aperçu de l'image</label>
                            <div class="text-center p-3 bg-light rounded">
                                <img id="image-preview" src="#" alt="Aperçu de l'image" style="max-height: 200px; max-width: 100%;" class="img-fluid">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Description -->
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                        id="description" name="description" rows="4">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        <!-- Dimensions -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-box me-2 text-primary"></i>Dimensions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="dimensions_weight" class="form-label">Poids (kg)</label>
                            <input type="number" step="0.01" class="form-control @error('dimensions.weight') is-invalid @enderror" 
                                id="dimensions_weight" name="dimensions[weight]" value="{{ old('dimensions.weight') }}">
                            @error('dimensions.weight')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="dimensions_length" class="form-label">Longueur (cm)</label>
                            <input type="number" step="0.01" class="form-control @error('dimensions.length') is-invalid @enderror" 
                                id="dimensions_length" name="dimensions[length]" value="{{ old('dimensions.length') }}">
                            @error('dimensions.length')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="dimensions_width" class="form-label">Largeur (cm)</label>
                            <input type="number" step="0.01" class="form-control @error('dimensions.width') is-invalid @enderror" 
                                id="dimensions_width" name="dimensions[width]" value="{{ old('dimensions.width') }}">
                            @error('dimensions.width')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="dimensions_height" class="form-label">Hauteur (cm)</label>
                            <input type="number" step="0.01" class="form-control @error('dimensions.height') is-invalid @enderror" 
                                id="dimensions_height" name="dimensions[height]" value="{{ old('dimensions.height') }}">
                            @error('dimensions.height')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Attributs dynamiques -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-tag me-2 text-primary"></i>Attributs</h5>
                <button type="button" class="btn btn-sm btn-primary" id="addAttributeBtn">
                    <i class="bi bi-plus"></i> Ajouter un attribut
                </button>
            </div>
            <div class="card-body">
                <div id="attributesContainer">
                    @if(old('attributes.keys'))
                        @foreach(old('attributes.keys') as $index => $key)
                        <div class="row mb-2 attribute-row">
                            <div class="col-md-5">
                                <input type="text" class="form-control" name="attributes[keys][]" value="{{ $key }}" placeholder="Nom">
                            </div>
                            <div class="col-md-5">
                                <input type="text" class="form-control" name="attributes[values][]" 
                                    value="{{ old('attributes.values.'.$index) }}" placeholder="Valeur">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger remove-attribute"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>
                
                <div class="text-center text-muted py-3" id="noAttributesMessage" 
                    {{ old('attributes.keys') && count(old('attributes.keys')) > 0 ? 'style="display: none;"' : '' }}>
                    Aucun attribut défini. Utilisez le bouton "Ajouter un attribut" pour en créer.
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-end mb-4">
            <button type="reset" class="btn btn-outline-secondary me-2">
                <i class="bi bi-x-circle"></i> Réinitialiser
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Enregistrer le produit
            </button>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Prévisualisation de l'image
        const imageInput = document.getElementById('image');
        const imagePreview = document.getElementById('image-preview');
        const imagePreviewContainer = document.getElementById('image-preview-container');
        
        imageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreviewContainer.style.display = 'block';
                }
                
                reader.readAsDataURL(this.files[0]);
            } else {
                imagePreviewContainer.style.display = 'none';
            }
        });
        
        // Gestion des attributs dynamiques
        const attributesContainer = document.getElementById('attributesContainer');
        const noAttributesMessage = document.getElementById('noAttributesMessage');
        const addAttributeBtn = document.getElementById('addAttributeBtn');
        
        // Ajouter un nouvel attribut
        addAttributeBtn.addEventListener('click', function() {
            // Cacher le message "aucun attribut"
            if (noAttributesMessage) {
                noAttributesMessage.style.display = 'none';
            }
            
            // Créer une nouvelle ligne d'attribut
            const newRow = document.createElement('div');
            newRow.className = 'row mb-2 attribute-row';
            newRow.innerHTML = `
                <div class="col-md-5">
                    <input type="text" class="form-control" name="attributes[keys][]" placeholder="Nom">
                </div>
                <div class="col-md-5">
                    <input type="text" class="form-control" name="attributes[values][]" placeholder="Valeur">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger remove-attribute"><i class="bi bi-trash"></i></button>
                </div>
            `;
            
            attributesContainer.appendChild(newRow);
            
            // Ajouter l'écouteur d'événement au bouton de suppression
            newRow.querySelector('.remove-attribute').addEventListener('click', function() {
                newRow.remove();
                
                // Afficher le message "aucun attribut" si plus aucun attribut
                if (attributesContainer.querySelectorAll('.attribute-row').length === 0 && noAttributesMessage) {
                    noAttributesMessage.style.display = 'block';
                }
            });
        });
        
        // Configurer les boutons de suppression existants
        document.querySelectorAll('.remove-attribute').forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.attribute-row').remove();
                
                // Afficher le message "aucun attribut" si plus aucun attribut
                if (attributesContainer.querySelectorAll('.attribute-row').length === 0 && noAttributesMessage) {
                    noAttributesMessage.style.display = 'block';
                }
            });
        });
    });
</script>
@endsection
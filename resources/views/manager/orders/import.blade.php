@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Importation de commandes</h5>
                    <a href="{{ route('manager.orders.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Retour aux commandes
                    </a>
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
                    
                    <div class="alert alert-info">
                        <h6><i class="bi bi-info-circle"></i> Instructions</h6>
                        <p>Vous pouvez importer des commandes en masse à partir d'un fichier CSV.</p>
                        <p>Le fichier CSV doit contenir les colonnes suivantes dans l'ordre :</p>
                        <ol>
                            <li>Nom du client</li>
                            <li>Téléphone 1</li>
                            <li>Téléphone 2</li>
                            <li>Adresse de livraison</li>
                            <li>Région</li>
                            <li>Ville</li>
                            <li>Produits (séparés par des virgules)</li>
                            <li>Quantités (séparées par des virgules, dans le même ordre que les produits)</li>
                            <li>Prix total</li>
                        </ol>
                    </div>
                    
                    <form action="{{ route('manager.orders.import-csv') }}" method="POST" enctype="multipart/form-data" class="mb-4">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="csv_file" class="form-label">Fichier CSV</label>
                            <input type="file" class="form-control @error('csv_file') is-invalid @enderror" id="csv_file" name="csv_file" required>
                            @error('csv_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="has_header" name="has_header" value="1">
                            <label class="form-check-label" for="has_header">Le fichier contient une ligne d'en-tête</label>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-upload"></i> Importer les commandes
                            </button>
                        </div>
                    </form>
                    
                    <div class="mt-4">
                        <h6 class="border-bottom pb-2 mb-3">Exemple de format CSV</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nom</th>
                                        <th>Tel1</th>
                                        <th>Tel2</th>
                                        <th>Adresse</th>
                                        <th>Région</th>
                                        <th>Ville</th>
                                        <th>Produits</th>
                                        <th>Quantités</th>
                                        <th>Prix</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Ahmed Ali</td>
                                        <td>55123456</td>
                                        <td>56789012</td>
                                        <td>Rue principale 123</td>
                                        <td>Tunis</td>
                                        <td>Le Bardo</td>
                                        <td>Produit A,Produit B</td>
                                        <td>1,2</td>
                                        <td>150.000</td>
                                    </tr>
                                    <tr>
                                        <td>Sami Ben Salah</td>
                                        <td>22334455</td>
                                        <td></td>
                                        <td>Avenue République 45</td>
                                        <td>Sfax</td>
                                        <td>Sfax Ville</td>
                                        <td>Produit C</td>
                                        <td>3</td>
                                        <td>85.500</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-grid gap-2 col-md-6 mx-auto mt-3">
                            <a href="{{ asset('exemples/modele_import_commandes.csv') }}" class="btn btn-outline-primary">
                                <i class="bi bi-download"></i> Télécharger un modèle CSV
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
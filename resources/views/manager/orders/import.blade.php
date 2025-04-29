@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Importation de commandes</h2>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour aux commandes
        </a>
    </div>
    
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Importer depuis un fichier CSV</h5>
                </div>
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('admin.orders.import-csv') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="csv_file" class="form-label">Fichier CSV</label>
                            <input class="form-control @error('csv_file') is-invalid @enderror" type="file" id="csv_file" name="csv_file" accept=".csv" required>
                            @error('csv_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Le fichier CSV doit contenir les colonnes suivantes : nom, telephone1, telephone2, adresse, region, ville, produits, quantites, prix_total
                            </div>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="has_header" name="has_header" checked>
                            <label class="form-check-label" for="has_header">
                                Le fichier contient une ligne d'en-tête
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload"></i> Importer
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Format attendu du CSV</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Téléphone 1</th>
                                    <th>Téléphone 2</th>
                                    <th>Adresse</th>
                                    <th>Région</th>
                                    <th>Ville</th>
                                    <th>Produits</th>
                                    <th>Quantités</th>
                                    <th>Prix Total</th>
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
                                    <td>150</td>
                                </tr>
                                <tr>
                                    <td>Leila Ben Salem</td>
                                    <td>22334455</td>
                                    <td></td>
                                    <td>Avenue de la République 45</td>
                                    <td>Sfax</td>
                                    <td>Sfax Ville</td>
                                    <td>Produit C</td>
                                    <td>3</td>
                                    <td>85.5</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3">
                        <h6><i class="bi bi-info-circle"></i> Notes importantes:</h6>
                        <ul class="mb-0">
                            <li>Séparez les noms des produits par des virgules dans la colonne "Produits"</li>
                            <li>Séparez les quantités correspondantes par des virgules dans la colonne "Quantités"</li>
                            <li>Assurez-vous que l'ordre des produits et des quantités correspond</li>
                            <li>Si un produit n'existe pas encore, il sera créé automatiquement</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
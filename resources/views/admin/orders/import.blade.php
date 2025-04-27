@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Importation de commandes</h2>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Importer depuis un fichier CSV</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.orders.import-csv') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="csv_file" class="form-label">Fichier CSV</label>
                            <input class="form-control" type="file" id="csv_file" name="csv_file" accept=".csv" required>
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
        </div>
        
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Importer depuis WooCommerce</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.import-woocommerce') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="woo_status" class="form-label">Statut des commandes</label>
                            <select class="form-select" id="woo_status" name="woo_status">
                                <option value="processing">En traitement</option>
                                <option value="pending">En attente</option>
                                <option value="on-hold">En attente de paiement</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="woo_date_from" class="form-label">Date de début</label>
                            <input type="date" class="form-control" id="woo_date_from" name="woo_date_from" value="{{ date('Y-m-d', strtotime('-7 days')) }}">
                        </div>
                        
                        <div class="mb-3">
                            <label for="woo_date_to" class="form-label">Date de fin</label>
                            <input type="date" class="form-control" id="woo_date_to" name="woo_date_to" value="{{ date('Y-m-d') }}">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-cloud-download"></i> Importer depuis WooCommerce
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Importer depuis Google Sheet</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.import-google-sheet') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="sheet_id" class="form-label">ID de la feuille Google</label>
                            <input type="text" class="form-control" id="sheet_id" name="sheet_id" value="{{ getSetting('google_sheet_id', '') }}">
                        </div>
                        
                        <div class="mb-3">
                            <label for="sheet_name" class="form-label">Nom de l'onglet</label>
                            <input type="text" class="form-control" id="sheet_name" name="sheet_name" value="Commandes">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-cloud-download"></i> Importer depuis Google Sheet
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
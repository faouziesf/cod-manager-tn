@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Paramètres du système</h2>
    </div>
    
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2 mb-3">Paramètres des commandes standard</h5>
                        
                        <div class="mb-3">
                            <label for="standard_max_daily_attempts" class="form-label">Nombre max. de tentatives par jour</label>
                            <input type="number" class="form-control" id="standard_max_daily_attempts" name="standard_max_daily_attempts" value="{{ $settings['standard_max_daily_attempts'] }}" min="1" required>
                            <div class="form-text">Nombre maximum de tentatives autorisées par jour pour une commande standard.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="standard_max_attempts" class="form-label">Nombre max. de tentatives total</label>
                            <input type="number" class="form-control" id="standard_max_attempts" name="standard_max_attempts" value="{{ $settings['standard_max_attempts'] }}" min="1" required>
                            <div class="form-text">Nombre maximum total de tentatives avant que la commande ne devienne ancienne.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="standard_attempt_interval" class="form-label">Délai entre les tentatives (heures)</label>
                            <input type="number" step="0.1" class="form-control" id="standard_attempt_interval" name="standard_attempt_interval" value="{{ $settings['standard_attempt_interval'] }}" min="0.5" required>
                            <div class="form-text">Délai minimum entre deux tentatives de contact pour une commande standard.</div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2 mb-3">Paramètres des commandes datées</h5>
                        
                        <div class="mb-3">
                            <label for="scheduled_max_daily_attempts" class="form-label">Nombre max. de tentatives par jour</label>
                            <input type="number" class="form-control" id="scheduled_max_daily_attempts" name="scheduled_max_daily_attempts" value="{{ $settings['scheduled_max_daily_attempts'] }}" min="1" required>
                            <div class="form-text">Nombre maximum de tentatives autorisées par jour pour une commande datée.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="scheduled_max_attempts" class="form-label">Nombre max. de tentatives total</label>
                            <input type="number" class="form-control" id="scheduled_max_attempts" name="scheduled_max_attempts" value="{{ $settings['scheduled_max_attempts'] }}" min="1" required>
                            <div class="form-text">Nombre maximum total de tentatives avant que la commande datée ne devienne ancienne.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="scheduled_attempt_interval" class="form-label">Délai entre les tentatives (heures)</label>
                            <input type="number" step="0.1" class="form-control" id="scheduled_attempt_interval" name="scheduled_attempt_interval" value="{{ $settings['scheduled_attempt_interval'] }}" min="0.5" required>
                            <div class="form-text">Délai minimum entre deux tentatives de contact pour une commande datée.</div>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2 mb-3">Paramètres des commandes anciennes</h5>
                        
                        <div class="mb-3">
                            <label for="old_attempt_interval" class="form-label">Délai entre les tentatives (heures)</label>
                            <input type="number" step="0.1" class="form-control" id="old_attempt_interval" name="old_attempt_interval" value="{{ $settings['old_attempt_interval'] }}" min="0.5" required>
                            <div class="form-text">Délai minimum entre deux tentatives de contact pour une commande ancienne.</div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2 mb-3">Intégrations</h5>
                        
                        <div class="mb-3">
                            <label for="woocommerce_api_key" class="form-label">WooCommerce API Key</label>
                            <input type="text" class="form-control" id="woocommerce_api_key" name="woocommerce_api_key" value="{{ $settings['woocommerce_api_key'] }}">
                        </div>
                        
                        <div class="mb-3">
                            <label for="woocommerce_api_secret" class="form-label">WooCommerce API Secret</label>
                            <input type="password" class="form-control" id="woocommerce_api_secret" name="woocommerce_api_secret" value="{{ $settings['woocommerce_api_secret'] }}">
                        </div>
                        
                        <div class="mb-3">
                            <label for="woocommerce_status_to_import" class="form-label">Statut des commandes à importer</label>
                            <select class="form-select" id="woocommerce_status_to_import" name="woocommerce_status_to_import">
                                <option value="processing" {{ $settings['woocommerce_status_to_import'] == 'processing' ? 'selected' : '' }}>En traitement</option>
                                <option value="pending" {{ $settings['woocommerce_status_to_import'] == 'pending' ? 'selected' : '' }}>En attente</option>
                                <option value="on-hold" {{ $settings['woocommerce_status_to_import'] == 'on-hold' ? 'selected' : '' }}>En attente de paiement</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="google_sheet_id" class="form-label">ID Google Sheet</label>
                            <input type="text" class="form-control" id="google_sheet_id" name="google_sheet_id" value="{{ $settings['google_sheet_id'] }}">
                            <div class="form-text">L'identifiant de la feuille Google Sheet à utiliser pour l'importation.</div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between">
                    <div>
                        <button type="button" class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#importWoocommerceModal">
                            <i class="bi bi-cloud-download"></i> Importer depuis WooCommerce
                        </button>
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importGoogleSheetModal">
                            <i class="bi bi-cloud-download"></i> Importer depuis Google Sheet
                        </button>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Enregistrer les paramètres
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Importation WooCommerce -->
<div class="modal fade" id="importWoocommerceModal" tabindex="-1" aria-labelledby="importWoocommerceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.settings.import-woocommerce') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="importWoocommerceModalLabel">Importer depuis WooCommerce</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p>Vous êtes sur le point d'importer les commandes depuis WooCommerce avec le statut <strong>{{ $settings['woocommerce_status_to_import'] }}</strong>.</p>
                    <p>Assurez-vous que les clés API sont correctement configurées.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Importer maintenant</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Importation Google Sheet -->
<div class="modal fade" id="importGoogleSheetModal" tabindex="-1" aria-labelledby="importGoogleSheetModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.settings.import-google-sheet') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="importGoogleSheetModalLabel">Importer depuis Google Sheet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p>Vous êtes sur le point d'importer les commandes depuis Google Sheet avec l'ID <strong>{{ $settings['google_sheet_id'] ?: 'Non configuré' }}</strong>.</p>
                    <p>Assurez-vous que l'ID de la feuille est correctement configuré et que le format est compatible.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Importer maintenant</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
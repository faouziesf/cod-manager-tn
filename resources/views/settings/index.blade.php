@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-gear-fill text-primary me-2"></i>Paramètres du système</h2>
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
    
    <div class="card shadow">
        <div class="card-header bg-white py-3">
            <ul class="nav nav-tabs card-header-tabs" id="settingsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="standard-tab" data-bs-toggle="tab" data-bs-target="#standard" type="button" role="tab" aria-controls="standard" aria-selected="true">
                        <i class="bi bi-clipboard-data me-1"></i> Commandes standard
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="scheduled-tab" data-bs-toggle="tab" data-bs-target="#scheduled" type="button" role="tab" aria-controls="scheduled" aria-selected="false">
                        <i class="bi bi-calendar-event me-1"></i> Commandes datées
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="old-tab" data-bs-toggle="tab" data-bs-target="#old" type="button" role="tab" aria-controls="old" aria-selected="false">
                        <i class="bi bi-archive me-1"></i> Commandes anciennes
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="integrations-tab" data-bs-toggle="tab" data-bs-target="#integrations" type="button" role="tab" aria-controls="integrations" aria-selected="false">
                        <i class="bi bi-link-45deg me-1"></i> Intégrations
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="tab-content" id="settingsTabContent">
                    <!-- Onglet Commandes Standard -->
                    <div class="tab-pane fade show active" id="standard" role="tabpanel" aria-labelledby="standard-tab">
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <h5 class="border-bottom pb-2 mb-4 text-primary">Configuration des commandes standard</h5>
                            </div>
                            
                            <div class="col-md-4 mb-4">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="text-center mb-3">
                                            <i class="bi bi-calendar-day text-primary" style="font-size: 2rem;"></i>
                                            <h6 class="mt-2">Tentatives journalières</h6>
                                        </div>
                                        <div class="mb-3">
                                            <label for="standard_max_daily_attempts" class="form-label">Nombre max. par jour</label>
                                            <input type="number" class="form-control" id="standard_max_daily_attempts" name="standard_max_daily_attempts" value="{{ $settings['standard_max_daily_attempts'] }}" min="1" required>
                                            <div class="form-text">Limite quotidienne de tentatives par commande</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-4">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="text-center mb-3">
                                            <i class="bi bi-reception-4 text-primary" style="font-size: 2rem;"></i>
                                            <h6 class="mt-2">Tentatives totales</h6>
                                        </div>
                                        <div class="mb-3">
                                            <label for="standard_max_attempts" class="form-label">Nombre max. total</label>
                                            <input type="number" class="form-control" id="standard_max_attempts" name="standard_max_attempts" value="{{ $settings['standard_max_attempts'] }}" min="1" required>
                                            <div class="form-text">Limite totale avant passage en ancienne</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-4">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="text-center mb-3">
                                            <i class="bi bi-hourglass-split text-primary" style="font-size: 2rem;"></i>
                                            <h6 class="mt-2">Intervalle</h6>
                                        </div>
                                        <div class="mb-3">
                                            <label for="standard_attempt_interval" class="form-label">Délai entre tentatives (heures)</label>
                                            <input type="number" step="0.1" class="form-control" id="standard_attempt_interval" name="standard_attempt_interval" value="{{ $settings['standard_attempt_interval'] }}" min="0.5" required>
                                            <div class="form-text">Ex: 2.5 pour 2h30 d'intervalle</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Onglet Commandes Datées -->
                    <div class="tab-pane fade" id="scheduled" role="tabpanel" aria-labelledby="scheduled-tab">
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <h5 class="border-bottom pb-2 mb-4 text-primary">Configuration des commandes datées</h5>
                            </div>
                            
                            <div class="col-md-4 mb-4">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="text-center mb-3">
                                            <i class="bi bi-calendar-day text-primary" style="font-size: 2rem;"></i>
                                            <h6 class="mt-2">Tentatives journalières</h6>
                                        </div>
                                        <div class="mb-3">
                                            <label for="scheduled_max_daily_attempts" class="form-label">Nombre max. par jour</label>
                                            <input type="number" class="form-control" id="scheduled_max_daily_attempts" name="scheduled_max_daily_attempts" value="{{ $settings['scheduled_max_daily_attempts'] }}" min="1" required>
                                            <div class="form-text">Limite quotidienne pour commandes datées</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-4">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="text-center mb-3">
                                            <i class="bi bi-reception-4 text-primary" style="font-size: 2rem;"></i>
                                            <h6 class="mt-2">Tentatives totales</h6>
                                        </div>
                                        <div class="mb-3">
                                            <label for="scheduled_max_attempts" class="form-label">Nombre max. total</label>
                                            <input type="number" class="form-control" id="scheduled_max_attempts" name="scheduled_max_attempts" value="{{ $settings['scheduled_max_attempts'] }}" min="1" required>
                                            <div class="form-text">Limite totale avant passage en ancienne</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-4">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="text-center mb-3">
                                            <i class="bi bi-hourglass-split text-primary" style="font-size: 2rem;"></i>
                                            <h6 class="mt-2">Intervalle</h6>
                                        </div>
                                        <div class="mb-3">
                                            <label for="scheduled_attempt_interval" class="form-label">Délai entre tentatives (heures)</label>
                                            <input type="number" step="0.1" class="form-control" id="scheduled_attempt_interval" name="scheduled_attempt_interval" value="{{ $settings['scheduled_attempt_interval'] }}" min="0.5" required>
                                            <div class="form-text">Ex: 3.5 pour 3h30 d'intervalle</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Onglet Commandes Anciennes -->
                    <div class="tab-pane fade" id="old" role="tabpanel" aria-labelledby="old-tab">
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <h5 class="border-bottom pb-2 mb-4 text-primary">Configuration des commandes anciennes</h5>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="text-center mb-3">
                                            <i class="bi bi-calendar-day text-primary" style="font-size: 2rem;"></i>
                                            <h6 class="mt-2">Tentatives journalières</h6>
                                        </div>
                                        <div class="mb-3">
                                            <label for="old_max_daily_attempts" class="form-label">Nombre max. par jour</label>
                                            <input type="number" class="form-control" id="old_max_daily_attempts" name="old_max_daily_attempts" value="{{ $settings['old_max_daily_attempts'] ?? 3 }}" min="1" required>
                                            <div class="form-text">Limite quotidienne pour commandes anciennes</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="text-center mb-3">
                                            <i class="bi bi-hourglass-split text-primary" style="font-size: 2rem;"></i>
                                            <h6 class="mt-2">Intervalle</h6>
                                        </div>
                                        <div class="mb-3">
                                            <label for="old_attempt_interval" class="form-label">Délai entre tentatives (heures)</label>
                                            <input type="number" step="0.1" class="form-control" id="old_attempt_interval" name="old_attempt_interval" value="{{ $settings['old_attempt_interval'] }}" min="0.5" required>
                                            <div class="form-text">Ex: 3.5 pour 3h30 d'intervalle</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Onglet Intégrations -->
                    <div class="tab-pane fade" id="integrations" role="tabpanel" aria-labelledby="integrations-tab">
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <h5 class="border-bottom pb-2 mb-4 text-primary">Configuration des intégrations</h5>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-white">
                                        <div class="d-flex align-items-center">
                                            <img src="https://cdn.jsdelivr.net/gh/woocommerce/woocommerce-admin@0.1.0/images/logo.svg" alt="WooCommerce" height="30">
                                            <h6 class="mb-0 ms-2">WooCommerce</h6>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="woocommerce_api_key" class="form-label">Clé API</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-key"></i></span>
                                                <input type="text" class="form-control" id="woocommerce_api_key" name="woocommerce_api_key" value="{{ $settings['woocommerce_api_key'] }}">
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="woocommerce_api_secret" class="form-label">Secret API</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                                <input type="password" class="form-control" id="woocommerce_api_secret" name="woocommerce_api_secret" value="{{ $settings['woocommerce_api_secret'] }}">
                                                <button class="btn btn-outline-secondary" type="button" id="togglePasswordBtn" onclick="togglePassword()">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="woocommerce_status_to_import" class="form-label">Statut des commandes à importer</label>
                                            <select class="form-select" id="woocommerce_status_to_import" name="woocommerce_status_to_import">
                                                <option value="processing" {{ $settings['woocommerce_status_to_import'] == 'processing' ? 'selected' : '' }}>En traitement</option>
                                                <option value="pending" {{ $settings['woocommerce_status_to_import'] == 'pending' ? 'selected' : '' }}>En attente</option>
                                                <option value="on-hold" {{ $settings['woocommerce_status_to_import'] == 'on-hold' ? 'selected' : '' }}>En attente de paiement</option>
                                            </select>
                                        </div>
                                        
                                        <div class="text-center mt-4">
                                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importWoocommerceModal">
                                                <i class="bi bi-cloud-download"></i> Importer depuis WooCommerce
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-white">
                                        <div class="d-flex align-items-center">
                                            <img src="https://cdn.jsdelivr.net/gh/google/material-design-icons@master/png/file/drive_file_move/materialicons/24dp/2x/baseline_drive_file_move_black_24dp.png" alt="Google Sheets" height="30">
                                            <h6 class="mb-0 ms-2">Google Sheets</h6>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="google_sheet_id" class="form-label">ID Google Sheet</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-file-earmark-spreadsheet"></i></span>
                                                <input type="text" class="form-control" id="google_sheet_id" name="google_sheet_id" value="{{ $settings['google_sheet_id'] }}">
                                            </div>
                                            <div class="form-text">
                                                <i class="bi bi-info-circle me-1"></i> L'ID se trouve dans l'URL de votre feuille Google (Ex: https://docs.google.com/spreadsheets/d/<strong>ID_ICI</strong>/edit)
                                            </div>
                                        </div>
                                        
                                        <div class="text-center mt-4">
                                            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#importGoogleSheetModal">
                                                <i class="bi bi-cloud-download"></i> Importer depuis Google Sheet
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i> Enregistrer les paramètres
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Importation WooCommerce -->
<!-- Section WooCommerce dans le fichier settings/index.blade.php -->
<div class="col-md-6 mb-4">
    <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white">
            <div class="d-flex align-items-center">
                <img src="https://cdn.jsdelivr.net/gh/woocommerce/woocommerce-admin@0.1.0/images/logo.svg" alt="WooCommerce" height="30">
                <h6 class="mb-0 ms-2">WooCommerce</h6>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="woocommerce_api_url" class="form-label">URL de la boutique WooCommerce</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-globe"></i></span>
                    <input type="url" class="form-control" id="woocommerce_api_url" name="woocommerce_api_url" value="{{ $settings['woocommerce_api_url'] }}" placeholder="https://votre-boutique.com">
                </div>
                <div class="form-text">URL complète de votre boutique WooCommerce (ex: https://votre-boutique.com)</div>
            </div>

            <div class="mb-3">
                <label for="woocommerce_api_key" class="form-label">Clé API</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                    <input type="text" class="form-control" id="woocommerce_api_key" name="woocommerce_api_key" value="{{ $settings['woocommerce_api_key'] }}">
                </div>
            </div>
            
            <div class="mb-3">
                <label for="woocommerce_api_secret" class="form-label">Secret API</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="woocommerce_api_secret" name="woocommerce_api_secret" value="{{ $settings['woocommerce_api_secret'] }}">
                    <button class="btn btn-outline-secondary" type="button" id="togglePasswordBtn" onclick="togglePassword()">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="woocommerce_status_to_import" class="form-label">Statut des commandes à importer</label>
                <select class="form-select" id="woocommerce_status_to_import" name="woocommerce_status_to_import">
                    <option value="processing" {{ $settings['woocommerce_status_to_import'] == 'processing' ? 'selected' : '' }}>En traitement</option>
                    <option value="pending" {{ $settings['woocommerce_status_to_import'] == 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="on-hold" {{ $settings['woocommerce_status_to_import'] == 'on-hold' ? 'selected' : '' }}>En attente de paiement</option>
                </select>
            </div>
            
            <div class="text-center mt-4">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importWoocommerceModal">
                    <i class="bi bi-cloud-download"></i> Importer depuis WooCommerce
                </button>
            </div>
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
                    <h5 class="modal-title" id="importGoogleSheetModalLabel">
                        <i class="bi bi-cloud-download me-2 text-success"></i>Importer depuis Google Sheet
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <span>Vous êtes sur le point d'importer les commandes depuis Google Sheet avec l'ID <strong>{{ $settings['google_sheet_id'] ?: 'Non configuré' }}</strong>.</span>
                    </div>
                    <p>Assurez-vous que l'ID de la feuille est correctement configuré et que le format des données est compatible avec le système.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-cloud-download me-1"></i> Importer maintenant
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('woocommerce_api_secret');
    const toggleBtn = document.getElementById('togglePasswordBtn');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleBtn.innerHTML = '<i class="bi bi-eye-slash"></i>';
    } else {
        passwordInput.type = 'password';
        toggleBtn.innerHTML = '<i class="bi bi-eye"></i>';
    }
}
</script>
@endsection
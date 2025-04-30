@extends('layouts.admin')

@section('title', 'Importer des commandes')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Importer des commandes</h1>
        <a href="{{ route('admin.orders.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Retour aux commandes
        </a>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- CSV Import Card -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-file-csv mr-1"></i> Import CSV
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-3">
                        Téléchargez un fichier CSV contenant vos commandes. Le fichier doit contenir les colonnes suivantes:
                    </p>
                    
                    <div class="alert alert-info mb-3">
                        <strong>Colonnes requises :</strong> customer_name, customer_phone1, delivery_address, region, city
                        <br>
                        <strong>Colonnes facultatives :</strong> customer_phone2, total_price, external_id, external_source, product_name, product_quantity, product_price
                    </div>
                    
                    <form action="{{ route('admin.orders.import-csv') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="csv_file" class="form-label">Fichier CSV</label>
                            <input type="file" class="form-control @error('csv_file') is-invalid @enderror" id="csv_file" name="csv_file" accept=".csv,.txt" required>
                            @error('csv_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload mr-1"></i> Importer
                        </button>
                    </form>
                    
                    <hr>
                    
                    <div class="mt-3">
                        <h6 class="font-weight-bold">Modèle CSV</h6>
                        <p>Téléchargez notre modèle CSV pour vous assurer que votre fichier est correctement formaté.</p>
                        <a href="{{ route('admin.orders.index') }}?template=csv" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-download mr-1"></i> Télécharger le modèle
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- WooCommerce Import Card -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fab fa-wordpress mr-1"></i> Import WooCommerce
                    </h6>
                </div>
                <div class="card-body">
                    @if(empty($wooCommerceSettings['api_url']) || empty($wooCommerceSettings['api_key']) || empty($wooCommerceSettings['api_secret']))
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Configuration WooCommerce incomplète. Veuillez configurer les paramètres WooCommerce dans la section Paramètres.
                        </div>
                        <a href="{{ route('admin.settings.index') }}" class="btn btn-warning">
                            <i class="fas fa-cog mr-1"></i> Configurer WooCommerce
                        </a>
                    @else
                        <p>Importer des commandes depuis votre boutique WooCommerce avec la configuration suivante:</p>
                        
                        <div class="table-responsive mb-3">
                            <table class="table table-bordered">
                                <tr>
                                    <th>URL de l'API</th>
                                    <td>{{ $wooCommerceSettings['api_url'] }}</td>
                                </tr>
                                <tr>
                                    <th>Statut à importer</th>
                                    <td>{{ $wooCommerceSettings['status_to_import'] }}</td>
                                </tr>
                            </table>
                        </div>
                        
                        <a href="{{ route('admin.import.woocommerce') }}" class="btn btn-primary">
                            <i class="fas fa-sync mr-1"></i> Synchroniser maintenant
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Imports Row -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Dernières commandes importées</h6>
                </div>
                <div class="card-body">
                    @if(count($lastImportedOrders) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Client</th>
                                        <th>Téléphone</th>
                                        <th>Adresse</th>
                                        <th>Source</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lastImportedOrders as $order)
                                    <tr>
                                        <td>{{ $order->id }}</td>
                                        <td>{{ $order->customer_name }}</td>
                                        <td>{{ $order->customer_phone1 }}</td>
                                        <td>{{ $order->delivery_address }}</td>
                                        <td>
                                            @if($order->external_source)
                                                <span class="badge bg-info text-white">{{ $order->external_source }}</span>
                                            @else
                                                <span class="badge bg-secondary text-white">CSV</span>
                                            @endif
                                        </td>
                                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            <p>
                                <span class="font-weight-bold">{{ $recentImports }}</span> commandes importées au cours des 7 derniers jours.
                            </p>
                        </div>
                    @else
                        <div class="alert alert-info">
                            Aucune commande n'a été importée récemment.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    // Rien pour l'instant
</script>
@endsection
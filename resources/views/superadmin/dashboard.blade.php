@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Tableau de bord Super Admin</h2>
    
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Admins Total</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalAdmins ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Admins Actifs</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeAdmins ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-check-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Admins Inactifs</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $inactiveAdmins ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-x-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Nouveaux Admins (30j)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $newAdmins ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-plus-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Nouveaux comptes par période</h6>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-primary period-btn active" data-period="7">7 jours</button>
                        <button type="button" class="btn btn-sm btn-outline-primary period-btn" data-period="30">30 jours</button>
                        <button type="button" class="btn btn-sm btn-outline-primary period-btn" data-period="90">90 jours</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="adminsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Utilisation du système</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="usageChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistiques des comptes Admin</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Admin</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Date d'expiration</th>
                                    <th>Utilisateurs</th>
                                    <th>Commandes</th>
                                    <th>Produits</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($admins ?? [] as $admin)
                                <tr>
                                    <td>{{ $admin->name }}</td>
                                    <td>{{ $admin->email }}</td>
                                    <td>
                                        @if($admin->active)
                                            <span class="badge bg-success">Actif</span>
                                        @else
                                            <span class="badge bg-danger">Inactif</span>
                                        @endif
                                    </td>
                                    <td>{{ $admin->expiry_date ? $admin->expiry_date->format('d/m/Y') : 'Illimité' }}</td>
                                    <td>{{ $admin->users_count ?? 0 }}</td>
                                    <td>{{ $admin->orders_count ?? 0 }}</td>
                                    <td>{{ $admin->products_count ?? 0 }}</td>
                                    <td>
                                        <a href="{{ route('superadmin.admins.show', $admin) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-center mt-4">
                        {{ $admins->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Données pour les graphiques
const chartData = {
    '7': {
        labels: {!! json_encode($chartDates7 ?? []) !!},
        data: {!! json_encode($chartAdmins7 ?? []) !!}
    },
    '30': {
        labels: {!! json_encode($chartDates30 ?? []) !!},
        data: {!! json_encode($chartAdmins30 ?? []) !!}
    },
    '90': {
        labels: {!! json_encode($chartDates90 ?? []) !!},
        data: {!! json_encode($chartAdmins90 ?? []) !!}
    }
};

// Graphique des admins
let currentPeriod = '7';
const ctx = document.getElementById('adminsChart').getContext('2d');
let adminsChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: chartData[currentPeriod].labels,
        datasets: [{
            label: 'Nouveaux comptes admin',
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            borderWidth: 2,
            data: chartData[currentPeriod].data,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});

// Changer la période du graphique
document.querySelectorAll('.period-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const period = this.dataset.period;
        
        // Mettre à jour le bouton actif
        document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        // Mettre à jour le graphique
        currentPeriod = period;
        adminsChart.data.labels = chartData[period].labels;
        adminsChart.data.datasets[0].data = chartData[period].data;
        adminsChart.update();
    });
});

// Graphique d'utilisation
const usageCtx = document.getElementById('usageChart').getContext('2d');
const usageChart = new Chart(usageCtx, {
    type: 'doughnut',
    data: {
        labels: ['Commandes', 'Produits', 'Utilisateurs'],
        datasets: [{
            data: [
                {{ $totalOrders ?? 0 }},
                {{ $totalProducts ?? 0 }},
                {{ $totalUsers ?? 0 }}
            ],
            backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc'],
            hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf'],
            hoverBorderColor: "rgba(234, 236, 244, 1)",
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>
@endpush
@endsection
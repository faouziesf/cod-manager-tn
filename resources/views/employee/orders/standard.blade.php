@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Commandes Standard</h2>
    </div>
    
    @if($needsMoreOrders)
        <div class="text-center my-5 py-5">
            <div class="mb-4">
                <i class="bi bi-cup-hot" style="font-size: 3rem;"></i>
                <h3 class="mt-3">Pause café! 😊</h3>
                <p class="text-muted">Vous avez traité toutes vos commandes standard.</p>
            </div>
            
            <form action="{{ route('employee.orders.request-more') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Je veux plus de commandes
                </button>
            </form>
        </div>
    @elseif($order)
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Commande #{{ $order->id }}</h5>
                    <span class="badge bg-primary">{{ $order->attempt_count }} tentative(s)</span>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('employee.orders.process', $order) }}" method="POST">
                    @csrf
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">Informations client</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tr>
                                        <th width="30%">Nom du client:</th>
                                        <td>{{ $order->customer_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Téléphone 1:</th>
                                        <td>{{ $order->customer_phone1 }}</td>
                                    </tr>
                                    @if($order->customer_phone2)
                                    <tr>
                                        <th>Téléphone 2:</th>
                                        <td>{{ $order->customer_phone2 }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <th>Adresse:</th>
                                        <td>{{ $order->delivery_address }}</td>
                                    </tr>
                                    <tr>
                                        <th>Région:</th>
                                        <td>{{ $order->region }}</td>
                                    </tr>
                                    <tr>
                                        <th>Ville:</th>
                                        <td>{{ $order->city }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">Produits</h6>
                            <div class="table-responsive">
                                <table class="table table-sm" id="products-table">
                                    <thead>
                                        <tr>
                                            <th>Produit</th>
                                            <th>Quantité</th>
                                            <th>Prix</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->products as $product)
                                            <tr>
                                                <td>{{ $product->name }}</td>
                                                <td>{{ $product->pivot->quantity }}</td>
                                                <td>{{ number_format($product->price, 3) }} TND</td>
                                                <td>{{ number_format($product->pivot->quantity * $product->price, 3) }} TND</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" class="text-end">Total:</th>
                                            <th>{{ number_format($order->total_price, 3) }} TND</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            
                            <h6 class="border-bottom pb-2 mb-3 mt-4">Détails</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tr>
                                        <th width="40%">Date de création:</th>
                                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Tentatives:</th>
                                        <td>{{ $order->attempt_count }} / {{ $order->max_attempts }}</td>
                                    </tr>
                                    <tr>
                                        <th>Tentatives aujourd'hui:</th>
                                        <td>{{ $order->daily_attempt_count }} / {{ $order->max_daily_attempts }}</td>
                                    </tr>
                                    @if($order->last_attempt_at)
                                    <tr>
                                        <th>Dernière tentative:</th>
                                        <td>{{ $order->last_attempt_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <h6 class="border-bottom pb-2 mb-3">Historique</h6>
                    @if($order->histories->count() > 0)
                        <div class="mb-4">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Utilisateur</th>
                                            <th>Action</th>
                                            <th>Note</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->histories->sortByDesc('created_at') as $history)
                                            <tr>
                                                <td>{{ $history->created_at->format('d/m/Y H:i') }}</td>
                                                <td>{{ $history->user->name }}</td>
                                                <td>
                                                    @if($history->action == 'create')
                                                        <span class="badge bg-info">Création</span>
                                                    @elseif($history->action == 'update')
                                                        <span class="badge bg-secondary">Mise à jour</span>
                                                    @elseif($history->action == 'confirm')
                                                        <span class="badge bg-success">Confirmation</span>
                                                    @elseif($history->action == 'cancel')
                                                        <span class="badge bg-danger">Annulation</span>
                                                    @elseif($history->action == 'schedule')
                                                        <span class="badge bg-primary">Programmation</span>
                                                    @elseif($history->action == 'no_answer')
                                                        <span class="badge bg-warning">Pas de réponse</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ $history->action }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $history->note }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <p class="text-muted">Aucun historique disponible.</p>
                    @endif
                    
                    <hr>
                    
                    <h6 class="border-bottom pb-2 mb-3">Action</h6>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="action" id="action_confirm" value="confirm" required>
                                <label class="form-check-label" for="action_confirm">
                                    <i class="bi bi-check-circle text-success"></i> Confirmer
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="action" id="action_cancel" value="cancel">
                                <label class="form-check-label" for="action_cancel">
                                    <i class="bi bi-x-circle text-danger"></i> Annuler
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="action" id="action_no_answer" value="no_answer">
                                <label class="form-check-label" for="action_no_answer">
                                    <i class="bi bi-telephone-x text-warning"></i> Ne répond pas
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="action" id="action_schedule" value="schedule">
                                <label class="form-check-label" for="action_schedule">
                                    <i class="bi bi-calendar-date text-info"></i> Dater
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div id="confirm_fields" class="action-fields d-none mb-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="confirmed_price" class="form-label">Prix confirmé</label>
                                    <input type="number" step="0.001" class="form-control" id="confirmed_price" name="confirmed_price" value="{{ $order->total_price }}">
                                    <div class="form-text">Laissez vide pour utiliser le prix total par défaut.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_note" class="form-label">Note</label>
                                    <textarea class="form-control" id="confirm_note" name="note" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="cancel_fields" class="action-fields d-none mb-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="cancel_note" class="form-label">Note <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="cancel_note" name="note" rows="2" required></textarea>
                                    <div class="form-text">Veuillez indiquer la raison de l'annulation.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="no_answer_fields" class="action-fields d-none mb-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="no_answer_note" class="form-label">Note <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="no_answer_note" name="note" rows="2" required></textarea>
                                    <div class="form-text">Veuillez indiquer les détails de la tentative.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="schedule_fields" class="action-fields d-none mb-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="scheduled_date" class="form-label">Date programmée <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="scheduled_date" name="scheduled_date" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                </div>
                                <div class="mb-3">
                                    <label for="schedule_note" class="form-label">Note <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="schedule_note" name="note" rows="2" required></textarea>
                                    <div class="form-text">Veuillez indiquer la raison du rappel.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Gestion des champs conditionnels
        $('input[name="action"]').change(function() {
            // Cacher tous les champs
            $('.action-fields').addClass('d-none');
            
            // Afficher les champs correspondants à l'action sélectionnée
            $('#' + $(this).val() + '_fields').removeClass('d-none');
            
            // Réinitialiser les champs required
            $('.action-fields textarea, .action-fields input').prop('required', false);
            
            // Définir les champs required selon l'action
            if ($(this).val() === 'confirm') {
                // Rien à faire
            } else if ($(this).val() === 'cancel') {
                $('#cancel_note').prop('required', true);
            } else if ($(this).val() === 'no_answer') {
                $('#no_answer_note').prop('required', true);
            } else if ($(this).val() === 'schedule') {
                $('#scheduled_date, #schedule_note').prop('required', true);
            }
        });
    });
</script>
@endpush
@endsection
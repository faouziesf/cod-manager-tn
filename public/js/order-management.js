$(document).ready(function() {
    // Gestion des produits dans les commandes
    if ($('#products-table').length > 0) {
        // Ajouter un produit au panier
        $('#add-product-btn').click(function() {
            var productId = $('#product-select').val();
            var productName = $('#product-select option:selected').text();
            var quantity = parseInt($('#product-quantity').val()) || 1;
            var price = parseFloat($('#product-select option:selected').data('price')) || 0;
            
            if (productId && quantity > 0) {
                // Vérifier si le produit existe déjà dans le panier
                var existingRow = $('#products-table tbody tr[data-product-id="' + productId + '"]');
                if (existingRow.length > 0) {
                    // Mettre à jour la quantité
                    var currentQty = parseInt(existingRow.find('.quantity-input').val());
                    existingRow.find('.quantity-input').val(currentQty + quantity);
                } else {
                    // Ajouter le produit à la table
                    var row = `
                        <tr data-product-id="${productId}">
                            <td>${productName}</td>
                            <td>
                                <input type="number" class="form-control quantity-input" name="products[${productId}][quantity]" value="${quantity}" min="1">
                                <input type="hidden" name="products[${productId}][id]" value="${productId}">
                            </td>
                            <td>${price.toFixed(3)} TND</td>
                            <td class="row-total">${(price * quantity).toFixed(3)} TND</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-danger remove-product">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    
                    $('#products-table tbody').append(row);
                }
                
                // Réinitialiser les champs
                $('#product-quantity').val(1);
                
                // Recalculer le total
                calculateTotal();
            }
        });
        
        // Supprimer un produit du panier
        $(document).on('click', '.remove-product', function() {
            $(this).closest('tr').remove();
            calculateTotal();
        });
        
        // Recalculer le total lorsque la quantité change
        $(document).on('change', '.quantity-input', function() {
            var row = $(this).closest('tr');
            var price = parseFloat(row.find('td:eq(2)').text());
            var quantity = parseInt($(this).val());
            var rowTotal = price * quantity;
            
            row.find('.row-total').text(rowTotal.toFixed(3) + ' TND');
            calculateTotal();
        });
        
        // Fonction pour calculer le total
        function calculateTotal() {
            var total = 0;
            $('#products-table tbody tr').each(function() {
                var priceText = $(this).find('td:eq(2)').text();
                var price = parseFloat(priceText);
                var quantity = parseInt($(this).find('.quantity-input').val());
                total += price * quantity;
            });
            
            $('#total-price').val(total.toFixed(3));
        }
    }
    
    // Gestion des actions sur les commandes
    if ($('input[name="action"]').length > 0) {
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
    }
});
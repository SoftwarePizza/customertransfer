{if $available_shops|count > 1}
<div id="customer-transfer-panel" class="panel">
    <div class="panel-heading">
        <i class="icon-exchange"></i>
        Przenoszenie klienta między sklepami
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-lg-6">
                <div class="customer-info">
                    <p><strong>Klient:</strong> {$customer_name}</p>
                    <p><strong>Aktualny sklep:</strong> 
                        {foreach $available_shops as $shop}
                            {if $shop.id_shop == $current_shop}{$shop.name}{/if}
                        {/foreach}
                    </p>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-group">
                    <label for="target_shop">Docelowy sklep:</label>
                    <select id="target_shop" class="form-control">
                        <option value="">-- Wybierz sklep --</option>
                        {foreach $available_shops as $shop}
                            {if $shop.id_shop != $current_shop}
                                <option value="{$shop.id_shop}">{$shop.name}</option>
                            {/if}
                        {/foreach}
                    </select>
                </div>
                <button id="transfer-customer-btn" class="btn btn-warning" disabled>
                    <i class="icon-exchange"></i>
                    Przenieś klienta
                </button>
            </div>
        </div>
    </div>
</div>

<style>
#customer-transfer-panel {
    margin: 10px auto;
    border: 1px solid #ddd;
    border-radius: 12px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    width: 50%;
    overflow: hidden;
    transition: all 0.3s ease;
}

#customer-transfer-panel:hover {
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

#customer-transfer-panel .panel-heading {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
    border-radius: 12px 12px 0 0;
    padding: 15px 20px;
    border: none;
    font-weight: 600;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

#customer-transfer-panel .panel-heading i {
    margin-right: 8px;
    font-size: 16px;
}

#customer-transfer-panel .panel-body {
    padding: 25px;
    background: rgba(255, 255, 255, 0.9);
}

#customer-transfer-panel .form-group label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 8px;
}

#customer-transfer-panel .form-control {
    border-radius: 8px;
    border: 1px solid #ced4da;
    padding: 10px 15px;
    transition: all 0.3s ease;
}

#customer-transfer-panel .form-control:focus {
    border-color: #f39c12;
    box-shadow: 0 0 0 0.2rem rgba(243, 156, 18, 0.25);
}

#transfer-customer-btn {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    border: none;
    border-radius: 8px;
    padding: 12px 24px;
    font-weight: 600;
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(243, 156, 18, 0.3);
}

#transfer-customer-btn:hover:not(:disabled) {
    background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(243, 156, 18, 0.4);
}

#transfer-customer-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

#transfer-customer-btn i {
    margin-right: 8px;
}

.customer-info {
    background: rgba(255, 255, 255, 0.7);
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
    border-left: 4px solid #f39c12;
}

.customer-info p {
    margin: 5px 0;
    color: #495057;
}

.customer-info strong {
    color: #343a40;
}

.transfer-loading {
    display: none;
}

/* Responsywność */
@media (max-width: 768px) {
    #customer-transfer-panel {
        width: 90%;
        margin: 15px auto;
    }
    
    #customer-transfer-panel .panel-body {
        padding: 20px 15px;
    }
    
    .customer-info {
        padding: 12px;
    }
}

/* Animacje alertów */
.alert {
    border-radius: 8px;
    border: none;
    margin-bottom: 15px;
    animation: slideInDown 0.3s ease;
}

@keyframes slideInDown {
    from {
        transform: translateY(-20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.alert-success {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    color: #155724;
    border-left: 4px solid #28a745;
}

.alert-danger {
    background: linear-gradient(135deg, #f8d7da 0%, #f1b0b7 100%);
    color: #721c24;
    border-left: 4px solid #dc3545;
}
</style>

<script type="text/javascript">
$(document).ready(function() {
    var customerId = {$customer_id};
    var ajaxurl = '{$ajax_url}';
    var adminToken = '{$admin_token}';
    
    // Aktywuj przycisk gdy wybrano sklep
    $('#target_shop').on('change', function() {
        var selectedShop = $(this).val();
        if (selectedShop) {
            $('#transfer-customer-btn').prop('disabled', false);
        } else {
            $('#transfer-customer-btn').prop('disabled', true);
        }
    });
    
    // Obsługa kliknięcia przycisku przenoszenia
    $('#transfer-customer-btn').on('click', function(e) {
        e.preventDefault();
        
        var targetShop = $('#target_shop').val();
        var targetShopName = $('#target_shop option:selected').text();
        
        if (!targetShop) {
            alert('Proszę wybrać sklep docelowy');
            return;
        }
        
        if (!confirm('Czy na pewno chcesz przenieść tego klienta do sklepu "' + targetShopName + '"?')) {
            return;
        }
        
        var $btn = $(this);
        var originalText = $btn.html();
        
        // Pokaż loading
        $btn.prop('disabled', true).html('<i class="icon-spinner icon-spin"></i> Przenoszenie...');
        
        console.log('Wysyłam AJAX:', {
            url: ajaxurl,
            customerId: customerId,
            targetShop: targetShop,
            adminToken: adminToken
        });
        
        // Wykonaj żądanie AJAX bezpośrednio do funkcji modułu
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                ajax: 1,
                action: 'transfer_customer',
                id_customer: customerId,
                id_shop_new: targetShop,
                token: adminToken,
                configure: 'customertransfer'
            },
            dataType: 'json',
            success: function(response) {
                console.log('AJAX sukces:', response);
                if (response.success) {
                    // Pokaż komunikat sukcesu
                    showSuccessMessage(response.message);
                    
                    // Odśwież stronę po 2 sekundach
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                } else {
                    showErrorMessage(response.message);
                    $btn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX błąd:', xhr, status, error);
                showErrorMessage('Wystąpił błąd podczas przenoszenia klienta: ' + error);
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    function showSuccessMessage(message) {
        var alertHtml = '<div class="alert alert-success alert-dismissible" role="alert">' +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
            '<span aria-hidden="true">&times;</span>' +
            '</button>' +
            '<strong>Sukces!</strong> ' + message +
            '</div>';
        
        $('#customer-transfer-panel').prepend(alertHtml);
    }
    
    function showErrorMessage(message) {
        var alertHtml = '<div class="alert alert-danger alert-dismissible" role="alert">' +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
            '<span aria-hidden="true">&times;</span>' +
            '</button>' +
            '<strong>Błąd!</strong> ' + message +
            '</div>';
        
        $('#customer-transfer-panel').prepend(alertHtml);
    }
});
</script>
{/if}

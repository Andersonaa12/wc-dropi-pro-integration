jQuery(document).ready(function() {
    jQuery('.select2').select2();
    jQuery('.select2-multi').select2();

    jQuery('#select-all').on('click', function() {
        jQuery('.product-checkbox').prop('checked', this.checked);
        jQuery('.pack-checkbox').prop('checked', this.checked);
    });

    jQuery('#add-to-woocommerce').on('click', function() {
        var selectedProducts = jQuery('.product-checkbox:checked').map(function() {
            return this.value;
        }).get();
        var selectedPacks = jQuery('.pack-checkbox:checked').map(function() {
            return this.value;
        }).get();


        if (selectedProducts.length === 0 && selectedPacks.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: wcDropiIntegration.noProductsSelectedTitle,
                text: wcDropiIntegration.noProductsSelectedText,
                confirmButtonText: wcDropiIntegration.acceptText
            });
            return;
        }

        Swal.fire({
            title: wcDropiIntegration.processingTitle,
            text: wcDropiIntegration.processingText,
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            showConfirmButton: false,
            didOpen: function() {
                Swal.showLoading();
            }
        });

        jQuery.ajax({
            url: wcDropiIntegration.ajaxurl,
            method: 'POST',
            data: {
                action: 'add_products_to_woocommerce',
                products: selectedProducts,
                packs: selectedPacks,
            },
            success: function(response, status, xhr) {
                Swal.close(); 
        
                if (xhr.status === 200 && response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: wcDropiIntegration.successTitle,
                        text: wcDropiIntegration.successText,
                        confirmButtonText: wcDropiIntegration.acceptText
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: wcDropiIntegration.errorTitle,
                        text: response.data ? response.data.message : wcDropiIntegration.errorText,
                        confirmButtonText: wcDropiIntegration.acceptText
                    });
                }
            },
            error: function(xhr) {
                Swal.close();
        
                Swal.fire({
                    icon: 'error',
                    title: wcDropiIntegration.errorTitle,
                    text: xhr.status === 200 ? wcDropiIntegration.errorText : wcDropiIntegration.errorText,
                    confirmButtonText: wcDropiIntegration.acceptText
                });
            }
        });        
    });
});

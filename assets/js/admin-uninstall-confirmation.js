jQuery(document).ready(function ($) {
    $('a[href*="plugins.php?action=delete-selected&checked[]=wc-dropi-pro-integration"]').click(function (event) {
        event.preventDefault(); 

        Swal.fire({
            title: '¿Estás seguro?',
            text: '¡Se eliminarán todos los datos relacionados con este plugin!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = $(this).attr('href');
            }
        });
    });
});

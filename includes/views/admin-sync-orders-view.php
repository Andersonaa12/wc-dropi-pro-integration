<div class="container">
    <h4 class="my-4"><?php esc_html_e('Todos los Pedidos de WooCommerce', 'wc-dropi-pro-integration'); ?></h4>
    <div class="row">
        <div>
            <table class="table table-hover text-sm">
                <thead>
                    <tr>
                        <th><?php esc_html_e('ID WooCommerce', 'wc-dropi-pro-integration'); ?></th>
                        <th>
                            <?php 
                                echo esc_html__('ID ', 'wc-dropi-pro-integration') . 
                                esc_html(!empty($data[0]->code_soft_platform) ? $data[0]->code_soft_platform : 'Dropi Pro'); 
                            ?>
                        </th>
                        <th><?php esc_html_e('Estado del Pedido', 'wc-dropi-pro-integration'); ?></th>
                        <th><?php esc_html_e('Nombre del Cliente', 'wc-dropi-pro-integration'); ?></th>
                        <th><?php esc_html_e('Total', 'wc-dropi-pro-integration'); ?></th>
                        <th><?php esc_html_e('Fecha del Pedido', 'wc-dropi-pro-integration'); ?></th>
                        <th><?php esc_html_e('Estado de la sincronización', 'wc-dropi-pro-integration'); ?></th>
                        
                        <th><?php esc_html_e('Acciones', 'wc-dropi-pro-integration'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)) : ?>
                        <?php foreach ($orders as $order) : 
                            $wc_order = wc_get_order($order->order_id);
                            
                            if (!$wc_order) {
                                continue;
                            }
                            if ($wc_order->get_status() === 'trash') {
                                continue;
                            }
                        ?>
                            <tr>
                                <td><?php echo esc_html($order->order_id); ?></td>
                                <td><?php echo !empty($order->dropi_order_id) && $order->dropi_order_id != 0 ? esc_html($order->dropi_order_id) : 'N/A'; ?></td>

                                <td><?php echo esc_html(wc_get_order_status_name($wc_order->get_status())); ?></td>
                                <td><?php echo esc_html($wc_order->get_billing_first_name() . ' ' . $wc_order->get_billing_last_name()); ?></td>
                                <td><?php echo wp_kses_post(wc_price($wc_order->get_total())); ?></td>
                                <td><?php echo esc_html($order->order_date); ?></td>
                                <td>
                                    <?php if (!empty($order->dropi_order_id)) : ?>
                                        <span class="dashicons dashicons-yes" style="color: green;"></span>
                                        <?php esc_html_e('Sincronizado', 'wc-dropi-pro-integration'); ?>
                                    <?php elseif ($order->dropi_order_id === '0') : ?>
                                        <span class="dashicons dashicons-warning" style="color: orange;"></span>
                                        <?php esc_html_e('No sincronizado', 'wc-dropi-pro-integration'); ?>
                                    <?php else : ?>
                                        <span class="dashicons dashicons-no" style="color: red;"></span>
                                        <?php 
                                            echo esc_html__('Sin productos de  ', 'wc-dropi-pro-integration') . 
                                            esc_html(!empty($data[0]->code_soft_platform) ? $data[0]->code_soft_platform : 'Dropi Pro'); 
                                        ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex">
                                        <a href="<?php echo esc_url(admin_url('post.php?post=' . $order->order_id . '&action=edit')); ?>" class="btn btn-success me-2">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <?php if ($order->dropi_order_id === '0') : ?>
                                            <a href="#" data-order-id="<?php echo esc_attr($order->order_id); ?>" class="btn btn-warning sync-order-btn">
                                                <?php esc_html_e('Sincronizar', 'wc-dropi-pro-integration'); ?> <i class="fa-solid fa-arrows-rotate"></i>
                                            </a>

                                        <?php endif; ?>
                                    </div>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="7"><?php esc_html_e('No se encontraron pedidos.', 'wc-dropi-pro-integration'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

        <nav aria-label="<?php esc_attr_e('Navegación de página', 'wc-dropi-pro-integration'); ?>">
            <ul class="pagination justify-content-center">
                <?php if ($pagination['prev_page_url']) : ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo esc_url($pagination['prev_page_url']); ?>" aria-label="<?php esc_attr_e('Previous', 'wc-dropi-pro-integration'); ?>">
                            <span aria-hidden="true">&laquo;</span>
                            <span class="sr-only"><?php esc_html_e('Previous', 'wc-dropi-pro-integration'); ?></span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++) : ?>
                    <li class="page-item <?php echo esc_attr($i == $pagination['current_page'] ? 'active' : ''); ?>">
                        <a class="page-link" href="<?php echo esc_url(add_query_arg('paged', $i)); ?>"><?php echo esc_html($i); ?></a>
                    </li>
                <?php endfor; ?>
                <?php if ($pagination['next_page_url']) : ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo esc_url($pagination['next_page_url']); ?>" aria-label="<?php esc_attr_e('Next', 'wc-dropi-pro-integration'); ?>">
                            <span aria-hidden="true">&raquo;</span>
                            <span class="sr-only"><?php esc_html_e('Next', 'wc-dropi-pro-integration'); ?></span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
</div>
<script>
jQuery(document).ready(function($) {
    $('.sync-order-btn').on('click', function(e) {
        e.preventDefault();
        var orderId = $(this).data('order-id');
        
        Swal.fire({
            title: wcDropiIntegration.processingTitle,
            text: 'Por favor, espera mientras se sincroniza el pedido.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            showConfirmButton: false,
            didOpen: function() {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: wcDropiIntegration.ajaxurl,
            method: 'POST',
            data: {
                action: 'sync_dropi_order',
                order_id: orderId
            },
            statusCode: {
                200: function(response) {
                    Swal.close();

                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Pedido Sincronizado Correctamente',
                            text: 'El pedido se ha sincronizado con Dropi',
                            confirmButtonText: wcDropiIntegration.acceptText
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: wcDropiIntegration.errorTitle,
                            text: response.data.message,
                            confirmButtonText: wcDropiIntegration.acceptText
                        });
                    }
                },
                400: function() {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: wcDropiIntegration.errorTitle,
                        text: 'Solicitud incorrecta (400).',
                        confirmButtonText: wcDropiIntegration.acceptText
                    });
                },
                500: function() {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: wcDropiIntegration.errorTitle,
                        text: 'Error del servidor (500).',
                        confirmButtonText: wcDropiIntegration.acceptText
                    });
                },
                404: function() {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: wcDropiIntegration.errorTitle,
                        text: 'Recurso no encontrado (404).',
                        confirmButtonText: wcDropiIntegration.acceptText
                    });
                }
                // Puedes agregar más códigos de estado si es necesario
            },
            error: function(xhr) {
                Swal.close();

                Swal.fire({
                    icon: 'error',
                    title: wcDropiIntegration.errorTitle,
                    text: xhr.status === 200 ? wcDropiIntegration.errorText : 'Se ha producido un error.',
                    confirmButtonText: wcDropiIntegration.acceptText
                });
            }
        });
    });
});

</script>
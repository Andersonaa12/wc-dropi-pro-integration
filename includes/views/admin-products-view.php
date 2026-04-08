<?php if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="container">
    <h4 class="my-4">
        <?php 
            echo esc_html__('Productos ', 'wc-dropi-pro-integration') . 
            esc_html(!empty($data[0]->code_soft_platform) ? $data[0]->code_soft_platform : 'Dropi Pro'); 
        ?>
    </h4>
    <div class="row">
        <form method="GET" action="<?php echo esc_url(admin_url('admin.php')); ?>" id="filter-form" class="product-type-form">
            <div class="col-md-12">
                <div class="mb-4 d-flex align-items-center">
                    <input type="hidden" name="page" value="wc-dropi-pro-integration-products">
                    <input type="text" name="q" class="form-control form-control-sm me-2" value="<?php echo isset($_GET['q']) ? esc_attr($_GET['q']) : ''; ?>" placeholder="<?php esc_attr_e('Buscar por nombre, SKU o descripción...', 'wc-dropi-pro-integration'); ?>">
                    <div class="me-2">
                        <select name="c[]" class="form-select form-select-sm me-2 select2-multi" multiple>
                            <option value="" disabled><?php esc_html_e('Ordenar por categoría', 'wc-dropi-pro-integration'); ?></option>
                            <?php foreach ($categories as $category) : ?>
                                <option value="<?php echo esc_attr($category['id']); ?>" <?php echo in_array($category['id'], (array)$_GET['c']) ? 'selected' : ''; ?>>
                                    <?php echo esc_html($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="me-2">
                        <select name="sort" class="form-select form-select-sm me-2 select2">
                            <option value=""><?php esc_html_e('Ordenar por...', 'wc-dropi-pro-integration'); ?></option>
                            <option value="1" <?php selected(isset($_GET['sort']) && $_GET['sort'] == 1); ?>><?php esc_html_e('Aleatorio', 'wc-dropi-pro-integration'); ?></option>
                            <option value="2" <?php selected(isset($_GET['sort']) && $_GET['sort'] == 2); ?>><?php esc_html_e('Más reciente', 'wc-dropi-pro-integration'); ?></option>
                            <option value="3" <?php selected(isset($_GET['sort']) && $_GET['sort'] == 3); ?>><?php esc_html_e('Stock - mayor a menor', 'wc-dropi-pro-integration'); ?></option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm"><?php esc_html_e('Buscar', 'wc-dropi-pro-integration'); ?></button>
                </div>
            </div>
            <div class="col-md-12">
                <div class="mb-4 d-flex align-items-center bg-check-products">
                    <input type="hidden" name="page" value="wc-dropi-pro-integration-products">
                    <div class="me-2">
                        <label class="form-check-label" for="generales"><?php esc_html_e('Generales', 'wc-dropi-pro-integration'); ?></label>
                        <input class="form-check-input bg-white" type="checkbox" name="list-products[]" value="General" id="generales" <?php echo in_array('General', $filters['list-products']) ? 'checked' : ''; ?>>
                    </div>
                    <div class="me-2">
                        <label class="form-check-label" for="privados"><?php esc_html_e('Privados', 'wc-dropi-pro-integration'); ?></label>
                        <input class="form-check-input bg-white" type="checkbox" name="list-products[]" value="Privado" id="privados" <?php echo in_array('Privado', $filters['list-products']) ? 'checked' : ''; ?>>
                    </div>
                    <div class="me-2">
                        <label class="form-check-label" for="packs"><?php esc_html_e('Packs', 'wc-dropi-pro-integration'); ?></label>
                        <input class="form-check-input bg-white" type="checkbox" name="list-products[]" value="Paquete" id="packs" <?php echo in_array('Paquete', $filters['list-products']) ? 'checked' : ''; ?>>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="row mb-4">
        <div class="col-md-12 ">
            <button type="button" id="add-to-woocommerce" class="btn btn-primary btn-sm"><?php esc_html_e('Importar a WooCommerce', 'wc-dropi-pro-integration'); ?> <i class="fa-regular fa-circle-down"></i></button>
            <button type="button" id="update-all-products" class="btn btn-warning btn-sm">
                <?php esc_html_e('Actualizar Todos los Productos', 'wc-dropi-pro-integration'); ?> 
                <i class="fa-solid fa-arrows-rotate"></i>
            </button>

        </div>
    </div>

    <?php if (!empty($products)) : ?>
        <form id="products-form">
            <table class="table table-hover text-sm">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all" /></th>
                        <th>
                            <a href="<?php echo esc_url(add_query_arg(['orderby' => 'id', 'order' => isset($_GET['order']) && $_GET['order'] == 'asc' ? 'desc' : 'asc'])); ?>">
                                <?php esc_html_e('ID', 'wc-dropi-pro-integration'); ?>
                                <span class="sort-icon-container">
                                    <i class="fas fa-sort-up sort-icon <?php echo (isset($_GET['orderby']) && $_GET['orderby'] == 'id' && $_GET['order'] == 'asc') ? 'active' : ''; ?>"></i>
                                    <i class="fas fa-sort-down sort-icon <?php echo (isset($_GET['orderby']) && $_GET['orderby'] == 'id' && $_GET['order'] == 'desc') ? 'active' : ''; ?>"></i>
                                </span>
                            </a>
                        </th>
                        <th><?php esc_html_e('SKU', 'wc-dropi-pro-integration'); ?></th>
                        <th><?php esc_html_e('Imagen', 'wc-dropi-pro-integration'); ?></th>
                        <th><?php esc_html_e('Nombre del Producto', 'wc-dropi-pro-integration'); ?></th>
                        <th>
                            <a href="<?php echo esc_url(add_query_arg(['orderby' => 'suggested_price', 'order' => isset($_GET['order']) && $_GET['order'] == 'asc' ? 'desc' : 'asc'])); ?>">
                                <?php esc_html_e('Precio Sugerido', 'wc-dropi-pro-integration'); ?>
                                <span class="sort-icon-container">
                                    <i class="fas fa-sort-up sort-icon <?php echo (isset($_GET['orderby']) && $_GET['orderby'] == 'suggested_price' && $_GET['order'] == 'asc') ? 'active' : ''; ?>"></i>
                                    <i class="fas fa-sort-down sort-icon <?php echo (isset($_GET['orderby']) && $_GET['orderby'] == 'suggested_price' && $_GET['order'] == 'desc') ? 'active' : ''; ?>"></i>
                                </span>
                            </a>
                        </th>
                        <th>
                            <a href="<?php echo esc_url(add_query_arg(['orderby' => 'price', 'order' => isset($_GET['order']) && $_GET['order'] == 'asc' ? 'desc' : 'asc'])); ?>">
                                <?php esc_html_e('Precio', 'wc-dropi-pro-integration'); ?>
                                <span class="sort-icon-container">
                                    <i class="fas fa-sort-up sort-icon <?php echo (isset($_GET['orderby']) && $_GET['orderby'] == 'price' && $_GET['order'] == 'asc') ? 'active' : ''; ?>"></i>
                                    <i class="fas fa-sort-down sort-icon <?php echo (isset($_GET['orderby']) && $_GET['orderby'] == 'price' && $_GET['order'] == 'desc') ? 'active' : ''; ?>"></i>
                                </span>
                            </a>
                        </th>
                        <th><?php esc_html_e('Categoría', 'wc-dropi-pro-integration'); ?></th>
                        <th><?php esc_html_e('Tipo', 'wc-dropi-pro-integration'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $hasRenderedPackHeader = false;
                    foreach ($products as $product) : ?>
                        <?php if ($product['type'] == 'Paquete') : ?>
                            <?php if (!$hasRenderedPackHeader) : ?>
                                <tr>
                                    <th colspan="10" class="table-secondary"><?php esc_html_e('Paquetes de Productos', 'wc-dropi-pro-integration'); ?></th>
                                </tr>
                                <tr>
                                    <th></th>
                                    <th><?php esc_html_e('ID', 'wc-dropi-pro-integration'); ?></th>
                                    <th><?php esc_html_e('SKU', 'wc-dropi-pro-integration'); ?></th>
                                    <th><?php esc_html_e('Nombre del Paquete', 'wc-dropi-pro-integration'); ?></th>
                                    <th><?php esc_html_e('Descripción', 'wc-dropi-pro-integration'); ?></th>
                                    <th><?php esc_html_e('Tipo', 'wc-dropi-pro-integration'); ?></th>
                                    <th><?php esc_html_e('Acciones', 'wc-dropi-pro-integration'); ?></th>
                                    <th colspan="10"></th>
                                </tr>
                                <?php $hasRenderedPackHeader = true; ?>
                            <?php endif; ?>
                            <tr>
                                <td><input type="checkbox" name="packs[]" value="<?php echo esc_attr($product['id']); ?>" class="pack-checkbox" /></td>
                                <td><?php echo esc_html($product['id']); ?></td>
                                <td><?php echo esc_html($product['sku']); ?></td>
                                <td><?php echo esc_html($product['name']); ?></td>
                                <td><?php echo esc_html($product['description']); ?></td>
                                <td><?php echo esc_html($product['type']); ?></td>
                                <td>
                                    <button type="button" class="btn btn-primary btn-sm view-pack-products" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#packModal" 
                                        data-pack-products='<?php echo esc_attr(wp_json_encode($product['products'])); ?>'>
                                        <?php esc_html_e('Ver productos', 'wc-dropi-pro-integration'); ?>
                                    </button>
                                </td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        <?php else : ?>
                            <tr>
                                <td><input type="checkbox" name="products[]" value="<?php echo esc_attr($product['id']); ?>" class="product-checkbox" /></td>
                                <td><?php echo esc_html($product['id']); ?></td>
                                <td><?php echo esc_html($product['sku']); ?></td>
                                <td><img src="<?php echo esc_url($product['image_url'] ?? 'N/A'); ?>" alt="Product Image" class="img-fluid rounded-circle" style="width: 50px; height: 50px;"></td>
                                <td><?php echo esc_html($product['name']); ?></td>
                                <td><?php echo esc_html($product['suggested_price'] ?? 'N/A'); ?></td>
                                <td><?php echo esc_html($product['price'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php 
                                    if (!empty($product['categories'])) {
                                        foreach ($product['categories'] as $category) {
                                            echo '<span class="badge bg-info text-white me-1">' . esc_html($category['name']) . '</span>';
                                        }
                                    } else {
                                        esc_html_e('Sin Categorías', 'wc-dropi-pro-integration');
                                    }
                                    ?>
                                </td>


                                <td><?php echo esc_html($product['type']); ?></td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </form>

        <!-- Modal para mostrar los productos del pack -->
        <div class="modal fade" id="packModal" tabindex="-1" aria-labelledby="packModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="packModalLabel"><?php esc_html_e('Paquetes de Productos', 'wc-dropi-pro-integration'); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-hover text-sm">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('ID', 'wc-dropi-pro-integration'); ?></th>
                                    <th><?php esc_html_e('SKU', 'wc-dropi-pro-integration'); ?></th>
                                    <th><?php esc_html_e('Nombre del Producto', 'wc-dropi-pro-integration'); ?></th>
                                    <th><?php esc_html_e('Precio', 'wc-dropi-pro-integration'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="packProductsBody">
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php esc_html_e('Cerrar', 'wc-dropi-pro-integration'); ?></button>
                    </div>
                </div>
            </div>
        </div>

        <nav aria-label="<?php esc_html_e('Navegación de página', 'wc-dropi-pro-integration'); ?>">
            <ul class="pagination justify-content-center">
                <?php 
                $max_links = 5; 
                $current_page = $pagination['current_page']; 
                $last_page = $pagination['last_page']; 
                $start_page = max(1, $current_page - floor($max_links / 2));
                $end_page = min($last_page, $start_page + $max_links - 1);
                
                if ($start_page > 1) {
                    echo '<li class="page-item"><a class="page-link" href="' . esc_url(add_query_arg(['paged' => 1])) . '">1</a></li>';
                    if ($start_page > 2) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                }
                for ($i = $start_page; $i <= $end_page; $i++) : ?>
                    <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo esc_url(add_query_arg(['paged' => $i])); ?>"><?php echo esc_html($i); ?></a>
                    </li>
                <?php endfor;

                if ($end_page < $last_page) {
                    if ($end_page < $last_page - 1) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                    echo '<li class="page-item"><a class="page-link" href="' . esc_url(add_query_arg(['paged' => $last_page])) . '">' . esc_html($last_page) . '</a></li>';
                }
                ?>

                <?php if ($pagination['prev_page_url']) : ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo esc_url($pagination['prev_page_url']); ?>" aria-label="<?php esc_html_e('Previous', 'wc-dropi-pro-integration'); ?>">
                            <span aria-hidden="true">&laquo;</span>
                            <span class="sr-only"><?php esc_html_e('Previous', 'wc-dropi-pro-integration'); ?></span>
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php if ($pagination['next_page_url']) : ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo esc_url($pagination['next_page_url']); ?>" aria-label="<?php esc_html_e('Next', 'wc-dropi-pro-integration'); ?>">
                            <span aria-hidden="true">&raquo;</span>
                            <span class="sr-only"><?php esc_html_e('Next', 'wc-dropi-pro-integration'); ?></span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>


    <?php else : ?>
        <div class="alert alert-warning" role="alert">
            <?php esc_html_e('No hay productos disponibles.', 'wc-dropi-pro-integration'); ?>
        </div>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    $('#update-products-general').on('click', function() {
        updateProducts('General');
    });

    $('#update-products-private').on('click', function() {
        updateProducts('Privado');
    });

    $('#update-products-packs').on('click', function() {
        updateProducts('Paquete');
    });
    jQuery(document).ready(function ($) {
        $('#update-all-products').on('click', function () {
            Swal.fire({
                title: 'Actualizando Todos los Productos',
                text: 'Por favor, espera mientras se actualizan todos los productos.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: wcDropiIntegration.ajaxurl,
                method: 'POST',
                data: {
                    action: 'update_dropi_products',
                    type: ['General', 'Privado', 'Paquete']
                },
                success: function (response) {
                    Swal.close();
                    if (response.success) {
                        Swal.fire({
                            title: 'Productos Actualizados',
                            text: 'Todos los productos han sido actualizados exitosamente.',
                            icon: 'success',
                            confirmButtonText: 'Aceptar'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: response.data && response.data.message
                                ? response.data.message
                                : 'Hubo un problema al actualizar los productos.',
                            icon: 'error',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                },
                error: function (jqXHR) {
                    Swal.close();

                    let errorMessage = 'Ocurrió un error desconocido.';
                    let errorDetail = 'No se proporcionaron detalles adicionales.';
                    let errorTrace = 'No se proporcionó la traza del error.';

                    if (jqXHR.responseJSON) {
                        if (jqXHR.responseJSON.message) {
                            errorMessage = jqXHR.responseJSON.message;
                        }
                        if (jqXHR.responseJSON.error_detail) {
                            errorDetail = jqXHR.responseJSON.error_detail;
                        }
                        if (jqXHR.responseJSON.trace) {
                            errorTrace = jqXHR.responseJSON.trace;
                        }
                    }

                    let fullMessage = `
                        <p><strong>Error:</strong> ${errorMessage}</p>
                        <p><strong>Detalle del error:</strong> ${errorDetail}</p>
                        <details style="margin-top: 10px;">
                            <summary style="cursor: pointer; color: blue;">Ver traza completa</summary>
                            <pre style="background: #f4f4f4; padding: 10px; border-radius: 5px;">${errorTrace}</pre>
                        </details>
                    `;
                    Swal.fire({
                        title: 'Error',
                        html: fullMessage,
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }
            });
        });
    });

    function updateProducts(type) {
        Swal.fire({
            title: 'Actualizando Productos',
            text: 'Por favor, espera mientras se actualiza la lista de productos.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: wcDropiIntegration.ajaxurl,
            method: 'POST',
            data: {
                action: 'update_dropi_products',
                type: type
            },
            success: function(response) {
                Swal.close();
                if (response.success) {
                    Swal.fire({
                        title: 'Productos Actualizados',
                        text: 'Listado de Productos Actualizado',
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.data && response.data.message ? response.data.message : 'Hubo un problema al actualizar la lista de productos.',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }
            },
            error: function(jqXHR) {
                Swal.close();

                let errorMessage = 'Ocurrió un error desconocido.';
                let errorDetail = 'No se proporcionaron detalles adicionales.';
                let errorTrace = 'No se proporcionó la traza del error.';

                if (jqXHR.responseJSON) {
                    if (jqXHR.responseJSON.message) {
                        errorMessage = jqXHR.responseJSON.message;
                    }
                    if (jqXHR.responseJSON.error_detail) {
                        errorDetail = jqXHR.responseJSON.error_detail;
                    }
                    if (jqXHR.responseJSON.trace) {
                        errorTrace = jqXHR.responseJSON.trace;
                    }
                }

                let fullMessage = `
                    <p><strong>Error:</strong> ${errorMessage}</p>
                    <p><strong>Detalle del error:</strong> ${errorDetail}</p>
                    <details style="margin-top: 10px;">
                        <summary style="cursor: pointer; color: blue;">Ver traza completa</summary>
                        <pre style="background: #f4f4f4; padding: 10px; border-radius: 5px;">${errorTrace}</pre>
                    </details>
                `;
                Swal.fire({
                    title: 'Error',
                    html: fullMessage,
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            }



        });
    }

    $('#clear-filter').on('click', function() {
        Swal.fire({
            title: 'Limpiando Filtros',
            text: 'Por favor, espera mientras se limpian los filtros.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        window.location.href = window.location.pathname + '?page=wc-dropi-pro-integration-products';
    });

    const productTypeForm = $('#filter-form');
    const checkboxes = $('.bg-check-products input[type="checkbox"]');

    checkboxes.each(function() {
        $(this).on('change', function() {
            productTypeForm.submit();
        });
    });

    $('.view-pack-products').each(function() {
        $(this).on('click', function() {
            const products = $(this).data('pack-products');
            const modalBody = $('#packProductsBody');
            modalBody.empty();

            products.forEach(function(product) {
                const row = `<tr>
                    <td>${product.id}</td>
                    <td>${product.sku}</td>
                    <td>${product.name}</td>
                    <td>${product.price}</td>
                </tr>`;
                modalBody.append(row);
            });
        });
    });
});
</script>

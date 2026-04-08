<?php
class WC_Dropi_Pro_Integration_Products_Controller {
    private $cache_dir;
    private $cache_files;

    public function __construct() {
        $this->cache_dir = plugin_dir_path(__DIR__) . 'cache/';
        $this->cache_files = [
            'General' => $this->cache_dir . 'products_general_cache.json',
            'Privado' => $this->cache_dir . 'products_private_cache.json',
            'Paquete' => $this->cache_dir . 'products_packs_cache.json',
        ];
        add_action('woocommerce_product_options_general_product_data', [$this, 'dropi_product_id_custom_field']);
        add_filter('manage_edit-product_columns', [$this, 'add_dropi_column'], 20);
        add_action('manage_product_posts_custom_column', [$this, 'display_dropi_column'], 10, 2);
        add_action('wp_ajax_update_dropi_products', [$this, 'update_dropi_products']);
        add_action('wp_ajax_add_products_to_woocommerce', [$this, 'add_products_to_woocommerce_handler']);
    }

    public function dropi_product_id_custom_field() {
        global $woocommerce, $post;

        echo '<div class="options_group">';
        woocommerce_wp_text_input([
            'id' => '_dropi_product_id',
            'label' => __('ID del Producto Dropi', 'woocommerce'),
            'description' => __('Este es el ID del producto en Dropi.', 'woocommerce'),
            'type' => 'text',
            'value' => get_post_meta($post->ID, '_dropi_product_id', true),
            'readonly' => 'readonly',
        ]);
        echo '</div>';
    }

    public function add_dropi_column($columns) {
        $columns['dropi_product'] = __('Dropi', 'wc-dropi-pro-integration');
        return $columns;
    }

    public function display_dropi_column($column, $post_id) {
        if ('dropi_product' === $column) {
            $dropi_product_id = get_post_meta($post_id, '_dropi_product_id', true);
            if ($dropi_product_id) {
                echo '<span class="dashicons dashicons-yes" style="color: green;"></span> ' . esc_html__('Sí', 'wc-dropi-pro-integration');
            } else {
                echo '<span class="dashicons dashicons-no" style="color: red;"></span> ' . esc_html__('No', 'wc-dropi-pro-integration');
            }
        }
    }

    public function display_products_page() {
        $this->ensure_cache_files_exist();

        $current_page = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
        $filters = [
            'q' => isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '',
            'c' => isset($_GET['c']) ? array_map('absint', $_GET['c']) : [],
            'sort' => isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : '',
            'orderby' => isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : '',
            'order' => isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'asc',
            'list-products' => isset($_GET['list-products']) ? (array)$_GET['list-products'] : ['General'],
        ];
        
        $data = $this->get_products_based_on_selection($current_page, $filters);
        $products = $data['products'];
        $pagination = $data['pagination'];
        $categories = $data['categories'];
        include plugin_dir_path(__FILE__) . '../views/admin-products-view.php';
    }

    private function ensure_cache_files_exist() {
        foreach ($this->cache_files as $key => $cache_file) {
            if (!file_exists($cache_file)) {
                $this->update_cache_for_type($key);
            }
        }
    }

    private function update_cache_for_type($type) {
        $response = $this->get_products_from_api($type);
        if ($response['status'] === 200 && isset($response['data']['products']) && !empty($response['data']['products'])) {
            $this->save_cache_file($response['data'], $this->cache_files[$type]);
        }
    }

    private function get_products_based_on_selection($page, $filters) {
        $all_filtered_products = [];
        $all_categories = [];

        foreach ($filters['list-products'] as $products_type) {
            if (file_exists($this->cache_files[$products_type])) {
                $cached_data = json_decode(file_get_contents($this->cache_files[$products_type]), true);
    
                if (!empty($cached_data['products'])) {
                    $filtered_products = $this->filter_products($cached_data['products'], $filters);
                    $all_filtered_products = array_merge($all_filtered_products, $filtered_products);
                }
    
                if (!empty($cached_data['categories'])) {
                    $all_categories = array_merge($all_categories, $cached_data['categories']);
                }
            }
        }
    
        $pagination = $this->generate_pagination(count($all_filtered_products), $page, $filters);
    
        return [
            'products' => array_slice($all_filtered_products, ($page - 1) * $pagination['per_page'], $pagination['per_page']),
            'pagination' => $pagination,
            'categories' => array_unique($all_categories, SORT_REGULAR),
        ];
    }

    private function filter_products($products, $filters) {
        $filtered_products = array_filter($products, function ($product) use ($filters) {
            $match = true;

            if (!empty($filters['q'])) {
                $match = strpos(strtolower($product['name']), strtolower($filters['q'])) !== false ||
                         strpos(strtolower($product['sku']), strtolower($filters['q'])) !== false;
            }

            if ($match && !empty($filters['c'])) {
                $match = false;
                foreach ($filters['c'] as $filter_category_id) {
                    if (array_filter($product['categories'], function ($category) use ($filter_category_id) {
                        return $category['id'] == $filter_category_id;
                    })) {
                        $match = true;
                        break;
                    }
                }
            }

            if ($match && !empty($filters['list-products'])) {
                $match = in_array($product['type'], $filters['list-products']);
            }

            return $match;
        });

        if (!empty($filters['orderby']) && !empty($filters['order'])) {
            usort($filtered_products, function($a, $b) use ($filters) {
                $order = strtolower($filters['order']) == 'asc' ? 1 : -1;

                switch ($filters['orderby']) {
                    case 'id':
                        return $order * ($a['id'] - $b['id']);
                    case 'suggested_price':
                        return $order * (floatval($a['suggested_price']) - floatval($b['suggested_price']));
                    case 'price':
                        return $order * (floatval($a['price']) - floatval($b['price']));
                    default:
                        return 0;
                }
            });
        }

        return $filtered_products;
    }

    private function generate_pagination($total_items, $current_page, $filters) {
        $per_page = 10;
        $total_pages = ceil($total_items / $per_page);
        $query_params = http_build_query($filters);

        return [
            'current_page' => $current_page,
            'last_page' => $total_pages,
            'per_page' => $per_page,
            'total' => $total_items,
            'first_page_url' => admin_url('admin.php?page=wc-dropi-pro-integration-products&paged=1&' . $query_params),
            'last_page_url' => admin_url('admin.php?page=wc-dropi-pro-integration-products&paged=' . $total_pages . '&' . $query_params),
            'next_page_url' => $current_page < $total_pages ? admin_url('admin.php?page=wc-dropi-pro-integration-products&paged=' . ($current_page + 1) . '&' . $query_params) : null,
            'prev_page_url' => $current_page > 1 ? admin_url('admin.php?page=wc-dropi-pro-integration-products&paged=' . ($current_page - 1) . '&' . $query_params) : null,
        ];
    }

    private function save_cache_file($data, $cache_file) {
        $cache_dir = dirname($cache_file);
        if (!file_exists($cache_dir)) {
            mkdir($cache_dir, 0755, true);
        }
        file_put_contents($cache_file, json_encode($data));
    }
    public function update_dropi_products() {
        try {
            set_time_limit(1200);

            $types = isset($_POST['type']) ? (array)$_POST['type'] : ['General', 'Privado', 'Paquete'];

            foreach ($types as $type) {
                $response = $this->get_products_from_api($type);

                if ($response['status'] !== 200) {
                    throw new Exception(sprintf(
                        __('Error del servidor remoto: %s', 'wc-dropi-pro-integration'),
                        $response['error'] ?? __('Respuesta no válida del servidor remoto.', 'wc-dropi-pro-integration')
                    ));
                }

                $this->save_cache_file($response['data'], $this->cache_files[$type]);
            }

            wp_send_json_success(['message' => __('Todos los productos se actualizaron correctamente.', 'wc-dropi-pro-integration')]);
        } catch (Exception $e) {
            error_log("Error en update_dropi_products: " . $e->getMessage());

            wp_send_json_error(
                [
                    'message' => __('Ocurrió un error al actualizar los productos.', 'wc-dropi-pro-integration'),
                    'error_detail' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ],
                500
            );
        } catch (Error $error) {
            error_log("Error crítico en update_dropi_products: " . $error->getMessage());

            wp_send_json_error(
                [
                    'message' => __('Ocurrió un error crítico.', 'wc-dropi-pro-integration'),
                    'error_detail' => $error->getMessage(),
                    'trace' => $error->getTraceAsString()
                ],
                500
            );
        }
    }

    private function get_products_from_api($type) {
        $model = new WC_Dropi_Pro_Integration_Model();
        $data = $model->get_data_from_database();
    
        if (empty($data)) {
            return [
                'status' => 400,
                'data' => ['products' => [], 'categories' => []]
            ];
        }
    
        $email = $data[0]->email;
        $token = $data[0]->token;
        $code_soft_platform = isset($data[0]->code_soft_platform) ? $data[0]->code_soft_platform : '';
    
        $api_url = get_stockago_api_url($code_soft_platform);
        switch ($type) {
            case 'General':
                $api_url .= 'products-list';
                break;
            case 'Privado':
                $api_url .= 'products-private-list';
                break;
            case 'Paquete':
                $api_url .= 'products-packs-list';
                break;
        }
    
        $response = wp_remote_get($api_url, [
            'headers' => [
                'email' => $email,
                'woocommerce-token' => $token,
            ],
            'timeout' => 3000,
        ]);
    
        if (is_wp_error($response)) {
            return [
                'status' => 500,
                'data' => ['products' => [], 'categories' => []],
                'error' => $response->get_error_message(),
            ];
        }
    
        $body = wp_remote_retrieve_body($response);
        $status = wp_remote_retrieve_response_code($response);
        $decoded_body = json_decode($body, true);

        // Check if the response is valid and has the expected structure
        if ($status !== 200 || empty($decoded_body) || !isset($decoded_body['ok']) || $decoded_body['ok'] !== 1) {
            return [
                'status' => $status ?: 500,
                'data' => ['products' => [], 'categories' => []],
                'error' => isset($decoded_body['message']) ? $decoded_body['message'] : 'Error al obtener los datos de la API.'
            ];
        }

        // Extract products and categories from the 'content' key
        $result_data = [
            'products' => isset($decoded_body['content']['products']) ? $decoded_body['content']['products'] : [],
            'categories' => isset($decoded_body['content']['categories']) ? $decoded_body['content']['categories'] : [],
        ];

        return [
            'status' => $status,
            'data' => $result_data,
            'message' => $decoded_body['message'] ?? 'Datos recuperados con éxito.'
        ];
    }

    public function add_products_to_woocommerce_handler() {
        try {
            $this->add_products_to_woocommerce();
        } catch (Exception $e) {
            error_log("Error en add_products_to_woocommerce_handler: " . $e->getMessage());
            wp_send_json_error(['message' => __('Ocurrió un error al agregar los productos a WooCommerce.', 'wc-dropi-pro-integration')]);
        }
        wp_die();
    }
    
    public function add_products_to_woocommerce() {
        try {
            if ((!isset($_POST['products']) || !is_array($_POST['products']) || empty($_POST['products'])) &&
                (!isset($_POST['packs']) || !is_array($_POST['packs']) || empty($_POST['packs']))) {
                wp_send_json_error(['message' => __('Solicitud no válida.', 'wc-dropi-pro-integration')]);
            }
            $selected_cache_files = [];
            if (isset($_POST['products']) && !empty($_POST['products'])) {
                $selected_cache_files[] = $this->cache_files['General'];
                $selected_cache_files[] = $this->cache_files['Privado'];
            }
            if (isset($_POST['packs']) && !empty($_POST['packs'])) {
                $selected_cache_files[] = $this->cache_files['Paquete'];
            }

            foreach ($selected_cache_files as $cache_file) {
                if (file_exists($cache_file)) {
                    $cached_data = json_decode(file_get_contents($cache_file), true);
                    $this->process_woocommerce_products($cached_data, $_POST['products'] ?? [], $_POST['packs'] ?? []);
                }
            }

            wp_send_json_success(['message' => __('Productos agregados correctamente a WooCommerce.', 'wc-dropi-pro-integration')]);
    
        } catch (Exception $e) {
            error_log("Error en add_products_to_woocommerce: " . $e->getMessage());
            wp_send_json_error(['message' => __('Ocurrió un error al agregar los productos a WooCommerce.', 'wc-dropi-pro-integration')]);
        }
    }

    private function process_woocommerce_products($cached_data, $product_ids, $pack_ids) {
        $products = $cached_data['products'] ?? [];
        foreach ($products as $product_data) {
            if (in_array($product_data['id'], $product_ids)) {
                if ($product_data['type'] === 'General' || $product_data['type'] === 'Privado') {
                    $this->create_woocommerce_product($product_data, null);
                }
            }
            if (in_array($product_data['id'], $pack_ids)) {
                if ($product_data['type'] === 'Paquete' && in_array($product_data['id'], $pack_ids)) {
                    $pack_data = $product_data;
                    $this->create_woocommerce_product(null, $pack_data);
                }
            }
        }
    }

    private function create_woocommerce_product($product_data, $pack_data) {
        try {
            $woocommerce_product = new WC_Product();
            
            if (!empty($product_data)) {
                $woocommerce_product->set_name($product_data['name'] ?? '');
                $woocommerce_product->set_sku($product_data['sku']);
                $woocommerce_product->set_regular_price($product_data['price'] ?? 0);
                $woocommerce_product->set_description($product_data['description'] ?? '');
                $woocommerce_product->set_short_description($product_data['description_2'] ?? '');
    
                if (!empty($product_data['image_url'])) {
                    $attachment_id = $this->upload_product_image($product_data['image_url']);
                    if ($attachment_id) {
                        $woocommerce_product->set_image_id($attachment_id);
                    }
                }
                if (!empty($product_data['image_urls']) && is_array($product_data['image_urls'])) {
                    $gallery_image_ids = [];
                    foreach ($product_data['image_urls'] as $image_url) {
                        $attachment_id = $this->upload_product_image($image_url);
                        if ($attachment_id) {
                            $gallery_image_ids[] = $attachment_id;
                        }
                    }
                    if (!empty($gallery_image_ids)) {
                        $woocommerce_product->set_gallery_image_ids($gallery_image_ids);
                    }
                }
                $category_ids = [];
                if (!empty($product_data['categories']) && is_array($product_data['categories'])) {
                    foreach ($product_data['categories'] as $category) {
                        $term = get_term_by('name', $category['name'], 'product_cat');
                        if ($term) {
                            $category_ids[] = $term->term_id;
                        } else {
                            $new_category = wp_insert_term($category['name'], 'product_cat');
                            if (!is_wp_error($new_category)) {
                                $category_ids[] = $new_category['term_id'];
                            } else {
                                error_log("Error al crear la categoría: " . $category['name'] . " - " . $new_category->get_error_message());
                            }
                        }
                    }
                    if (!empty($category_ids)) {
                        $woocommerce_product->set_category_ids($category_ids);
                    }
                }
                
                $woocommerce_product->update_meta_data('_dropi_product', 'yes');
                $woocommerce_product->update_meta_data('_dropi_product_id', $product_data['id']);
                $woocommerce_product->update_meta_data('_dropi_product_type', 'product');
            }
    
            if (!empty($pack_data)) {
                $woocommerce_product->set_name($pack_data['name'] ?? '');
                $woocommerce_product->set_sku($pack_data['sku']);
                $woocommerce_product->set_description($pack_data['description'] ?? '');
                
                $category_name = 'Paquete';
                $category_id = $this->get_or_create_category($category_name);
    
                if ($category_id) {
                    $woocommerce_product->set_category_ids([$category_id]);
                }
    
                $woocommerce_product->update_meta_data('_dropi_product', 'yes');
                $woocommerce_product->update_meta_data('_dropi_product_id', $pack_data['id']);
                $woocommerce_product->update_meta_data('_dropi_product_type', 'pack');
            }
    
            $woocommerce_product->save();
    
            return $woocommerce_product->get_id();
    
        } catch (Exception $e) {
            error_log("Error al crear el producto: " . $e->getMessage());
            return false;
        }
    }

    private function get_or_create_category($category_name) {
        $term = get_term_by('name', $category_name, 'product_cat');
    
        if ($term) {
            return $term->term_id;
        } else {
            $new_category = wp_insert_term($category_name, 'product_cat');
    
            if (!is_wp_error($new_category)) {
                return $new_category['term_id'];
            } else {
                error_log("Error al crear la categoría: " . $category_name . " - " . $new_category->get_error_message());
                return false;
            }
        }
    }

    private function upload_product_image($image_url) {
        $default_image_url = plugin_dir_url(__FILE__) . 'assets/images/no-disponible.png';
        if (empty($image_url)) {
            $image_url = $default_image_url;
        }
    
        $temp = download_url($image_url);
    
        if (is_wp_error($temp)) {
            $image_url = $default_image_url;
            $temp = download_url($image_url);
    
            if (is_wp_error($temp)) {
                return false;
            }
        }
    
        $file_array = array(
            'name'     => basename($image_url),
            'tmp_name' => $temp,
        );
    
        $attachment_id = media_handle_sideload($file_array, 0);
    
        if (is_wp_error($attachment_id)) {
            wp_delete_file($file_array['tmp_name']);
            return false;
        }
        return $attachment_id;
    }
}

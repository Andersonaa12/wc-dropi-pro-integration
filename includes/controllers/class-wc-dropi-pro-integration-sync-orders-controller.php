<?php
include_once plugin_dir_path(__FILE__) . '../config.php';

class WC_Dropi_Pro_Integration_Sync_Orders_Controller {
    public function __construct() {
        error_log('WC_Dropi_Pro_Integration_Sync_Orders_Controller constructor ejecutado');
        add_filter('manage_edit-shop_order_columns', [$this, 'add_dropi_status_column'],10, 1);
        add_action('manage_shop_order_posts_custom_column', [$this, 'display_dropi_status_column_content'], 5, 2);
        add_action('wp_ajax_sync_dropi_order', [$this, 'handle_sync_dropi_order_ajax']);
    }
    
    public function add_dropi_status_column($columns) {
        error_log('Ejecutando add_dropi_status_column');
        $new_columns = [];
        foreach ($columns as $column_key => $column_label) {
            $new_columns[$column_key] = $column_label;
            if ('order_actions' === $column_key) {
                $new_columns['dropi_status'] = __('Dropi Vinculado', 'wc-dropi-pro-integration');
            }
        }
        return $new_columns;
    }
    
    public function display_dropi_status_column_content($column, $post_id) {
        if ('dropi_status' === $column) {
            error_log('Mostrando contenido para columna Dropi');
            $dropi_order_id = get_post_meta($post_id, '_dropi_order_id', true);
    
            if (!empty($dropi_order_id)) {
                echo '<mark class="order-status status-completed tips" title="' . esc_attr__('Vinculado correctamente con Dropi', 'wc-dropi-pro-integration') . '">
                        <span>' . esc_html__('Sí', 'wc-dropi-pro-integration') . '</span>
                      </mark>';
            } else {
                echo '<mark class="order-status status-pending tips" title="' . esc_attr__('No vinculado con Dropi', 'wc-dropi-pro-integration') . '">
                        <span>' . esc_html__('No', 'wc-dropi-pro-integration') . '</span>
                      </mark>';
            }
        }
    }
    
    public function display_sync_orders_page() {
        $model = new WC_Dropi_Pro_Integration_Model();
    
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $limit = 10;
        $offset = ($paged - 1) * $limit;
    
        $orders = $model->get_all_orders_with_dropi_status($limit, $offset);
        $total_orders = $model->get_total_orders_count();
        $total_pages = ceil($total_orders / $limit);
    
        $pagination = [
            'current_page' => $paged,
            'total_pages' => $total_pages,
            'prev_page_url' => $paged > 1 ? add_query_arg('paged', $paged - 1) : null,
            'next_page_url' => $paged < $total_pages ? add_query_arg('paged', $paged + 1) : null,
        ];
        $model = new WC_Dropi_Pro_Integration_Model();
        $data = $model->get_data_from_database();
        include plugin_dir_path(__FILE__) . '../views/admin-sync-orders-view.php';
    }
    

    public function sync_dropi_order($order_id) {
        error_log('sync_dropi_order llamada para la orden: ' . $order_id);
        global $state_code_to_province_map;

        $order = wc_get_order($order_id);
        if (!$order) {
            error_log('No se pudo obtener la orden con ID: ' . $order_id);
            return;
        }
        
        $productos = $order->get_items();

        //Datos de envio
        $payment_method = $order->get_payment_method();
        $customer_ip_address = str_replace('_', '.', $order->get_customer_ip_address());
        $COD = ($payment_method === 'cod') ? 1 : 0;
        
        $billing_city = $order->get_billing_city(); 
        $billing_state = $order->get_billing_state();
        $province_dropi = $billing_state;

        $billing_postcode = $order->get_billing_postcode();
        $billing_address_1 = $order->get_billing_address_1();
        $billing_address_2 = $order->get_billing_address_2();

        //Datos de cliente
        $billing_first_name = $order->get_billing_first_name();
        $billing_last_name = $order->get_billing_last_name();
        $full_name = $billing_first_name . ' ' . $billing_last_name;

        $billing_email = $order->get_billing_email();
        $billing_phone = $order->get_billing_phone();
        $customer_note = $order->get_customer_note();
        $shipping_country = $order->get_shipping_country();
        

        
        
    
        $order_details = [];
        $contains_dropi_products = false;
        $dropi_total_cost = 0;
    
        foreach ($productos as $item_id => $item) {
            $product_id = $item->get_product_id();
            $product = wc_get_product($product_id);
            $sku = $product->get_sku();
            $dropi_product_id = get_post_meta($product_id, '_dropi_product_id', true);
            $dropi_product_type = get_post_meta($product_id, '_dropi_product_type', true);
        
            if ($dropi_product_id) {
                $contains_dropi_products = true; 
                $product_total = $item->get_total();
                $dropi_total_cost += $product_total;
        
                $order_details[] = [
                    'dropi_product_id' => $dropi_product_id,
                    'product_total' => $product_total,
                    'quantity' => $item->get_quantity(),
                    'product_name' => $item->get_name(),
                    'sku' => $sku,
                    'product_type' => $dropi_product_type
                ];
                error_log('Producto Dropi encontrado: ' . $dropi_product_id . ' en la orden: ' . $order_id);
            } else {
                error_log('Producto sin ID de Dropi en la orden: ' . $order_id);
            }
        }
        
        if ($contains_dropi_products) {
            $this->send_order_status_to_dropi(
                $order,
                $customer_ip_address,
                $COD,
                $billing_city,
                $billing_state,
                $province_dropi,
                $billing_postcode,
                $billing_address_1,
                $billing_address_2,
                $full_name,
                $billing_email,
                $billing_phone,
                $customer_note,
                $shipping_country,
                $dropi_total_cost,
                $order_details,
            );
        } else {
            error_log('La orden ' . $order_id . ' no contiene productos de Dropi.');
        }
    }
    
    private function send_order_status_to_dropi($order, $customer_ip_address, $COD, $billing_city, $billing_state, $province_dropi, $billing_postcode, $billing_address_1, $billing_address_2, $full_name, $billing_email, $billing_phone, $customer_note, $shipping_country, $dropi_total_cost, $order_details) {
        $model = new WC_Dropi_Pro_Integration_Model();
    
        $data = $model->get_data_from_database();
        if (!empty($data) && $data[0]->sync_orders) {
            $email = $data[0]->email;
            $token = $data[0]->token;
            $code_soft_platform = $data[0]->code_soft_platform;
            $api_url = get_stockago_api_url($code_soft_platform) . 'orders/create';
            
            $order_data = [
                'order_id' => $order->get_id(),
                'ip' => $customer_ip_address,
                'COD' => $COD,
                'city' => $billing_city,
                'province' => $province_dropi,
                'zip' => $billing_postcode,
                'address' => $billing_address_1,
                'address_2' => $billing_address_2,
                'name' => $full_name,
                'email' => $billing_email,
                'phone' => $billing_phone,
                'carrier_observations' => $customer_note,
                'country_code' => $shipping_country,
                'dropi_total_cost' => $dropi_total_cost,
                'order_details' => $order_details,
                //'confirm_order' => 1,
            ];
    
            error_log('Datos enviados a Dropi para la orden: ' . $order->get_id() . ' - ' . print_r($order_data, true));
    
            $response = wp_remote_post($api_url, [
                'headers' => [
                    'email' => $email, 
                    'woocommerce-token' => $token,
                ],
                'body' => wp_json_encode($order_data),
                'timeout' => 3000,
            ]);
    
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                error_log('Error al sincronizar el estado del pedido con Dropi: ' . $error_message);
                update_post_meta($order->get_id(), '_dropi_order_id', 0);
                wp_send_json_error(['message' => 'Error de conexión al servidor de Dropi: ' . $error_message], 500);
            } else {
                $body = wp_remote_retrieve_body($response);
                $result = json_decode($body, true);

                // Check if JSON decoding was successful
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log('Error al decodificar la respuesta JSON de Dropi: ' . json_last_error_msg());
                    update_post_meta($order->get_id(), '_dropi_order_id', 0);
                    wp_send_json_error(['message' => 'Error al procesar la respuesta de Dropi'], 500);
                }

                // Check for successful response and order ID in 'content' field
                if (isset($result['ok']) && $result['ok'] === 1 && isset($result['code']) && $result['code'] === 200 && isset($result['content'])) {
                    $dropi_order_id = sanitize_text_field($result['content']);
                    error_log('Dropi ID recibido: ' . $dropi_order_id);
                    update_post_meta($order->get_id(), '_dropi_order_id', $dropi_order_id);
                    error_log('ID de pedido de Dropi guardado en WooCommerce: ' . $dropi_order_id);
                } else {
                    update_post_meta($order->get_id(), '_dropi_order_id', 0);
                    error_log('Error en la respuesta de Dropi: ' . $body);
                    wp_send_json_error(['message' => 'Error en la respuesta de Dropi: ' . ($result['message'] ?? 'Respuesta inválida')], 400);
                }

                error_log('Respuesta de Dropi para la orden ' . $order->get_id() . ': ' . $body);
            }
        } else {
            error_log('Sincronización de órdenes con Dropi está desactivada o no se encontró configuración válida.');
        }
    }

    public function maybe_sync_order($order_id) {
        error_log('Iniciando sincronización para la orden ID: ' . $order_id);
        $model = new WC_Dropi_Pro_Integration_Model();
        $deleted = $model->delete_dropi_meta_record($order_id);

        if ($deleted) {
            error_log('Meta record eliminado, procediendo con la sincronización para la orden ID: ' . $order_id);
            $this->sync_dropi_order($order_id);
            return true;
        } else {
            error_log('No se encontró el meta record o fallo al eliminar para la orden ID: ' . $order_id);
            return false;
        }
    }

    public function handle_sync_dropi_order_ajax() {
        if (!isset($_POST['order_id'])) {
            wp_send_json_error(['message' => __('Falta el ID de la orden', 'wc-dropi-pro-integration')]);
        }

        $order_id = intval($_POST['order_id']);
        $success = $this->maybe_sync_order($order_id);

        if ($success) {
            wp_send_json_success(['message' => __('Orden sincronizada correctamente', 'wc-dropi-pro-integration')]);
        } else {
            wp_send_json_error(['message' => __('Error al sincronizar la orden', 'wc-dropi-pro-integration')]);
        }
    }

        
    /* 
    public function sync_dropi_stock($product) {
        $product_id = $product->get_id();
        $dropi_product_id = get_post_meta($product_id, '_dropi_product_id', true);

        if ($dropi_product_id) {
            error_log('Sincronizando stock para el producto Dropi con ID: ' . $dropi_product_id);
            $this->send_stock_update_to_dropi($product, $dropi_product_id);
        } else {
            error_log('Producto sin ID de Dropi: ' . $product_id);
        }
    }

    private function send_stock_update_to_dropi($product, $dropi_product_id) {
        $model = new WC_Dropi_Pro_Integration_Model();

        $data = $model->get_data_from_database();
        if (!empty($data) && $data[0]->sync_stock) {
            $email = $data[0]->email;
            $token = $data[0]->token;
            $code_soft_platform = $data[0]->code_soft_platform;

            $api_url = get_stockago_api_url($code_soft_platform) . 'sync-stock';
            
            $stock_data = [
                'product_id' => $dropi_product_id,
                'stock_quantity' => $product->get_stock_quantity(),
            ];

            error_log('Datos de stock enviados a Dropi para el producto: ' . $dropi_product_id . ' - ' . print_r($stock_data, true));

            $response = wp_remote_post($api_url, [
                'headers' => [
                    'email' => $email,
                    'woocommerce-token' => $token,
                ],
                'body' => wp_json_encode($stock_data),
            ]);

            if (is_wp_error($response)) {
                error_log('Error al sincronizar el stock con Dropi: ' . $response->get_error_message());
            } else {
                error_log('Respuesta de Dropi para el stock del producto ' . $dropi_product_id . ': ' . wp_remote_retrieve_body($response));
            }
        } else {
            error_log('Sincronización de stock con Dropi está desactivada o no se encontró configuración válida.');
        }
    } */
}

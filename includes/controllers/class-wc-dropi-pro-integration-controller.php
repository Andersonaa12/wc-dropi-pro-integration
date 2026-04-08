<?php

class WC_Dropi_Pro_Integration_Controller {

    public function add_admin_menu() {
        add_menu_page(
            'Integración Dropi Pro',           
            'Dropi Pro',                       
            'manage_options',                     
            'wc-dropi-pro-integration',           
            array($this, 'display_login_or_settings'),
            plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/images/icon.png', 
            6                                    
        );

        $model = new WC_Dropi_Pro_Integration_Model();
        $data = $model->get_data_from_database();

        if (!empty($data)) {
            add_submenu_page(
                'wc-dropi-pro-integration',
                'Productos',
                'Productos',
                'manage_options',
                'wc-dropi-pro-integration-products',
                array($this, 'delegate_to_products_controller')
            );
        }
        if (!empty($data)) {
            add_submenu_page(
                'wc-dropi-pro-integration',
                'Sincronización de pedidos',
                'Sincronización de pedidos',
                'manage_options',
                'wc-dropi-pro-integration-sync-orders',
                array($this, 'delegate_to_sync_orders_controller')
            );
        }
    }

    public function delegate_to_products_controller() {
        $products_controller = new WC_Dropi_Pro_Integration_Products_Controller();
        $products_controller->display_products_page();
    }

    public function delegate_to_sync_orders_controller() {
        $sync_orders_controller = new WC_Dropi_Pro_Integration_Sync_Orders_Controller();
        $sync_orders_controller->display_sync_orders_page();
    }

    public function display_login_or_settings() {
        if (!is_user_logged_in()) {
            wp_die(esc_html__('Por favor, inicia sesión para acceder a las configuraciones de Dropi Pro.', 'wc-dropi-pro-integration'));
        }

        if (isset($_POST['logout'])) {
            $this->handle_logout();
        }

        $model = new WC_Dropi_Pro_Integration_Model();
        $data = $model->get_data_from_database();

        if (empty($data)) {
            echo '<div class="notice notice-warning"><p>' . esc_html__('Para poder usar el plugin Dropi Pro, debes ingresar tu correo electrónico y el token de la tienda.', 'wc-dropi-pro-integration') . '</p></div>';
            include plugin_dir_path(__FILE__) . '../views/admin-settings-login-view.php';

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $this->process_login_form();
            }
        } else {
            include plugin_dir_path(__FILE__) . '../views/admin-settings-profile-view.php';
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $this->save_profile_settings();
            }
        }
    }

    public function handle_logout() {
        $model = new WC_Dropi_Pro_Integration_Model();
        
        $delete_result = $model->delete_dropi_data();
        
        if ($delete_result) {
            wp_redirect(add_query_arg('message', 'logout_success', admin_url('admin.php?page=wc-dropi-pro-integration')));
        } else {
            wp_redirect(add_query_arg('message', 'error_logout', admin_url('admin.php?page=wc-dropi-pro-integration')));
        }
        
        exit;
    }

    public function process_login_form() {
        $email = sanitize_email($_POST['store_email']);
        $token = sanitize_text_field($_POST['store_token']);
        $code_soft_platform = isset($_POST['platform_code']) ? sanitize_text_field($_POST['platform_code']) : '';
        $woocommerce_store_url = get_site_url();

        if (!empty($email) && !empty($token)) {
            $api_url = get_stockago_api_url($code_soft_platform);

            $response = wp_remote_get($api_url . 'auth-details', [
                'headers' => [
                    'email' => $email,
                    'woocommerce-token' => $token,
                    'woocommerce_store_url' => $woocommerce_store_url,
                ],
            ]);

            if (is_wp_error($response)) {
                wp_redirect(add_query_arg('message', 'error_connection', admin_url('admin.php?page=wc-dropi-pro-integration')));
                exit;
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (isset($data['ok']) && $data['ok'] === 1 && isset($data['content'])) {
                $model = new WC_Dropi_Pro_Integration_Model();
                
                $store = isset($data['content']['store']) ? sanitize_text_field($data['content']['store']) : '';

                $model->save_data_to_database([
                    'email' => $email,
                    'token' => $token,
                    'code_soft_platform' => $code_soft_platform,
                    'store' => $store,
                ]);

                wp_redirect(add_query_arg('message', 'success-api', admin_url('admin.php?page=wc-dropi-pro-integration')));
                exit;
            } else {
                // Redirigir con mensaje de credenciales inválidas
                wp_redirect(add_query_arg('message', 'invalid_credentials', admin_url('admin.php?page=wc-dropi-pro-integration')));
                exit;
            }

        } else {
            wp_redirect(add_query_arg('message', 'missing_fields', admin_url('admin.php?page=wc-dropi-pro-integration')));
            exit;
        }
    }

    public function save_profile_settings() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : '';
            $email = sanitize_email($_POST['store_email']);
            $token = sanitize_text_field($_POST['store_token']);
            $store = sanitize_text_field($_POST['store']);
            $sync_orders = 1;
            $sync_stock = 1;
            $code_soft_platform = sanitize_text_field($_POST['code_soft_platform']);

            if (!empty($email) && !empty($token)) {
                $model = new WC_Dropi_Pro_Integration_Model();
                $model->update_data_in_database([
                    'id' => $id,
                    'store' => $store,
                    'email' => $email,
                    'token' => $token,
                    'sync_orders' => $sync_orders,
                    'sync_stock' => $sync_stock,
                    'code_soft_platform' => $code_soft_platform,
                ]);

                add_settings_error(
                    'wc_dropi_pro_integration_messages',
                    'wc_dropi_pro_integration_message',
                    __('Los datos se han guardado correctamente.', 'wc-dropi-pro-integration'),
                    'updated'
                );

                wp_redirect(admin_url('admin.php?page=wc-dropi-pro-integration&message=success'));
                exit;
            } else {
                add_settings_error(
                    'wc_dropi_pro_integration_messages',
                    'wc_dropi_pro_integration_error',
                    __('Por favor, completa todos los campos requeridos.', 'wc-dropi-pro-integration'),
                    'error'
                );
                wp_redirect(admin_url('admin.php?page=wc-dropi-pro-integration&message=error'));
            }
        }
    }

    public function display_products_page() {
        include plugin_dir_path(__FILE__) . '../views/admin-products-view.php';
    }
}

<?php
class WC_Dropi_Pro_Integration {

    public function __construct() {
        try {
            $this->load_dependencies();
            $this->define_hooks();

            // Engancha tu método al hook del admin.
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
            
        } catch (Exception $e) {
            error_log('Error en la inicialización del plugin WC Dropi Pro: ' . esc_html($e->getMessage()));
            wp_die(
                esc_html__('Se produjo un error durante la inicialización del plugin. Revisa los logs.', 'wc-dropi-pro-integration')
            );
        }
    }

    private function load_dependencies() {
        try {
            //class-general
            require_once plugin_dir_path(__FILE__) . 'class-wc-dropi-pro-integration-activator.php';
            require_once plugin_dir_path(__FILE__) . 'class-wc-dropi-pro-integration-deactivator.php';
            require_once plugin_dir_path(__FILE__) . 'class-wc-dropi-pro-integration-token-handler.php';
            require_once plugin_dir_path(__FILE__) . 'class-wc-dropi-pro-integration-updater.php';

            //Controller
            require_once plugin_dir_path(__FILE__) . 'controllers/class-wc-dropi-pro-integration-controller.php';
            require_once plugin_dir_path(__FILE__) . 'controllers/class-wc-dropi-pro-integration-products-controller.php';
            require_once plugin_dir_path(__FILE__) . 'controllers/class-wc-dropi-pro-integration-sync-orders-controller.php';

            //Models
            require_once plugin_dir_path(__FILE__) . 'models/class-wc-dropi-pro-integration-model.php';
            
        } catch (Exception $e) {
            throw new Exception(
                esc_html__('Error al cargar las dependencias: ', 'wc-dropi-pro-integration') .
                esc_html($e->getMessage())
            );
        }
    }

    private function define_hooks() {
        try {
            $controller              = new WC_Dropi_Pro_Integration_Controller();
            $products_controller     = new WC_Dropi_Pro_Integration_Products_Controller();
            $sync_orders_controller  = new WC_Dropi_Pro_Integration_Sync_Orders_Controller();
            
            add_action('admin_menu', array($controller, 'add_admin_menu'));
            add_action('wp_ajax_add_products_to_woocommerce', array($products_controller, 'add_products_to_woocommerce_handler'));

            //add_action('woocommerce_product_set_stock', [$sync_orders_controller, 'sync_dropi_stock'], 10, 1);
            add_action('woocommerce_thankyou', function($order_id) {
                error_log('Hook woocommerce_thankyou ejecutado para la orden: ' . esc_html($order_id));
            }, 10, 1);
            add_action('woocommerce_thankyou', [$sync_orders_controller, 'sync_dropi_order'], 10, 1);
            
        } catch (Exception $e) {
            throw new Exception(
                esc_html__('Error al definir los hooks: ', 'wc-dropi-pro-integration') .
                esc_html($e->getMessage())
            );
        }
    }

    /**
     * Encola scripts SOLO en la pantalla de tu plugin en el Admin.
     *
     * @param string $hook Nombre del hook de la página actual.
     */
    public function enqueue_admin_scripts($hook) {
        // Si la cadena 'wc-dropi-pro-integration' NO aparece en $hook
        // devolvemos y no encolamos.
        if (strpos($hook, 'wc-dropi-pro-integration') === false) {
            return;
        }
    
        wp_enqueue_script('jquery');
        wp_enqueue_style('bootstrap-css', plugin_dir_url(__FILE__) . '../assets/bootstrap/css/bootstrap.min.css');
        wp_enqueue_script('bootstrap-js', plugin_dir_url(__FILE__) . '../assets/bootstrap/js/bootstrap.bundle.min.js', array('jquery'), null, true);
    
        wp_enqueue_style('sweetalert2-css', plugin_dir_url(__FILE__) . '../assets/sweetalert2/css/sweetalert2.min.css');
        wp_enqueue_script('sweetalert2-js', plugin_dir_url(__FILE__) . '../assets/sweetalert2/js/sweetalert2.js', array('jquery'), null, true);
    
        wp_enqueue_style('select2-css', plugin_dir_url(__FILE__) . '../assets/select2/css/select2.min.css');
        wp_enqueue_script('select2-js', plugin_dir_url(__FILE__) . '../assets/select2/js/select2.min.js', array('jquery'), null, true);
    
        wp_enqueue_style('font-awesome', plugin_dir_url(__FILE__) . '../assets/font-awesome/all.min.css');
        wp_enqueue_script('font-awesome-kit', 'https://kit.fontawesome.com/bcd54ae57f.js', array(), null, true);
    
        wp_enqueue_style('custom-style', plugin_dir_url(__FILE__) . '../assets/css/style.css');
        wp_enqueue_script('custom-script', plugin_dir_url(__FILE__) . '../assets/js/products-custom-script.js', array('jquery'), null, true);
    
        // Localizar variables
        wp_localize_script('custom-script', 'wcDropiIntegration', array(
            'ajaxurl'                => admin_url('admin-ajax.php'),
            'noProductsSelectedTitle'=> __('Ningún producto seleccionado', 'wc-dropi-pro-integration'),
            'noProductsSelectedText' => __('Por favor, selecciona al menos un producto.', 'wc-dropi-pro-integration'),
            'processingTitle'        => __('Procesando...', 'wc-dropi-pro-integration'),
            'processingText'         => __('Por favor, espera mientras se agregan los productos.', 'wc-dropi-pro-integration'),
            'successTitle'           => __('Productos agregados', 'wc-dropi-pro-integration'),
            'successText'            => __('Productos agregados a WooCommerce.', 'wc-dropi-pro-integration'),
            'errorTitle'             => __('Error', 'wc-dropi-pro-integration'),
            'errorText'              => __('Hubo un problema al agregar los productos.', 'wc-dropi-pro-integration'),
            'acceptText'             => __('Aceptar', 'wc-dropi-pro-integration'),
        ));
    }

    public function run() {
        // Tu lógica adicional, si la hubiera.
    }
}

<?php

/**
 * Plugin Name:  Dropify PRO
 * Description: Este plugin permite a los usuarios mostrar e importar productos de Dropi Pro en WooCommerce.
 * Plugin URI:  https://dropipro.com/
 * Version: 0.0.4
 * Author: Anderson Camilo Serna Estrada
 * Text Domain: wc-dropi-pro-integration
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

try {
    require_once plugin_dir_path(__FILE__) . 'includes/config.php';
    require_once plugin_dir_path(__FILE__) . 'includes/class-wc-dropi-pro-integration.php';
    require_once plugin_dir_path(__FILE__) . 'includes/class-wc-dropi-pro-integration-activator.php';
    require_once plugin_dir_path(__FILE__) . 'includes/class-wc-dropi-pro-integration-deactivator.php';

    register_activation_hook(__FILE__, array('WC_Dropi_Pro_Integration_Activator', 'activate'));
    register_deactivation_hook(__FILE__, array('WC_Dropi_Pro_Integration_Deactivator', 'deactivate'));

    add_action('admin_enqueue_scripts', 'wc_dropi_pro_uninstall_confirm_script');
    function wc_dropi_pro_uninstall_confirm_script($hook) {
        if ($hook === 'plugins.php') {
            wp_enqueue_style('sweetalert2-css', plugin_dir_url(__FILE__) . 'assets/sweetalert2/css/sweetalert2.min.css');
            wp_enqueue_script('sweetalert2-js', plugin_dir_url(__FILE__) . 'assets/sweetalert2/js/sweetalert2.js', array('jquery'), null, true);
            
            wp_enqueue_script('wc-dropi-pro-uninstall-confirmation', plugin_dir_url(__FILE__) . 'assets/js/admin-uninstall-confirmation.js', array('jquery', 'sweetalert2-js'), '1.0.0', true);
        }
    }


    add_action('plugins_loaded', 'check_woocommerce_dependency');
    function check_woocommerce_dependency() {
        if (!class_exists('WooCommerce')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                esc_html__('Este plugin requiere WooCommerce para funcionar. Por favor, instala y activa WooCommerce.', 'wc-dropi-pro-integration'),
                esc_html__('Plugin dependiente de WooCommerce', 'wc-dropi-pro-integration'),
                array('back_link' => true)
            );
            
        }
    }

    function run_wc_dropi_pro_integration() {
        $plugin = new WC_Dropi_Pro_Integration();
        $plugin->run();
    }

    run_wc_dropi_pro_integration();
} catch (Exception $e) {
    error_log('Error en la ejecución del plugin WC Dropi Pro: ' . $e->getMessage());
    wp_die('Se produjo un error durante la carga del plugin. Por favor, revisa los logs para más detalles.');
}

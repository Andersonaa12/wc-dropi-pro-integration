<?php

class WC_Dropi_Pro_Integration_Activator {

    public static function activate() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dropi_pro_tokens';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            store tinytext NOT NULL,
            email VARCHAR(255) NOT NULL,
            token VARCHAR(1000) NOT NULL,
            sync_orders BOOLEAN,
            sync_stock BOOLEAN,
            code_soft_platform VARCHAR(100),
            PRIMARY KEY  (id),
            KEY token (token)
        ) $charset_collate;";

        try {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            add_option('wc_dropi_pro_integration_options', [
                'sync_orders' => 0,
                'sync_stock' => 0,
                'code_soft_platform' => 0
            ]);
        } catch (Exception $e) {
            error_log('Error durante la activación del plugin WC Dropi Pro: ' . $e->getMessage());
            wp_die('Ocurrió un error durante la activación del plugin. Por favor, revisa los logs para más detalles.');
        }
    }
}

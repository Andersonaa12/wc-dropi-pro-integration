<?php

class WC_Dropi_Pro_Integration_Deactivator {

    public static function deactivate() {
        try {
            delete_option('wc_dropi_pro_integration_options');
        } catch (Exception $e) {
            error_log('Error en la desactivación del plugin WC Dropi Pro: ' . $e->getMessage());
        }
    }
}


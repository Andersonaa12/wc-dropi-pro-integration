<?php

class WC_Dropi_Pro_Integration_Token_Handler {

    public function store_token($token_data) {
        global $wpdb;
        $wpdb->insert(
            "{$wpdb->prefix}dropi_pro_tokens",
            array(
                'store' => $token_data['store'],
                'token' => $token_data['token'],
                'sync_orders' => $token_data['sync_orders'],
                'sync_stock' => $token_data['sync_stock'],
            ),
            array(
                '%s',
                '%s',
                '%d',
                '%d',
            )
        );
    }
    public function is_token_valid($token) {
        global $wpdb;
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dropi_pro_tokens WHERE token = %s AND DATE_ADD(created_at, INTERVAL 1 HOUR) > NOW()",
            $token
        ));

        return $result ? true : false;
    }
}


<?php

class WC_Dropi_Pro_Integration_Model {

    public function get_data_from_database() {
        global $wpdb;
        $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}dropi_pro_tokens");
        return $results;
    }

    public function save_data_to_database($data) {
        global $wpdb;
        $wpdb->insert(
            "{$wpdb->prefix}dropi_pro_tokens",
            array(
                'store' => isset($data['store']) ? sanitize_text_field($data['store']) : '',
                'email' => sanitize_email($data['email']),
                'token' => sanitize_text_field($data['token']),
                'sync_stock' => 1,
                'sync_orders' => 1,
                'code_soft_platform' => sanitize_text_field($data['code_soft_platform'])
            ),
            array(
                '%s',
                '%s',
                '%s',
                '%d',
                '%d',
                '%s'
            )
        );
    }

    public function update_data_in_database($data) {
        global $wpdb;
        try {
            $id = isset($data['id']) ? (int)$data['id'] : 0;

            $result = $wpdb->update(
                "{$wpdb->prefix}dropi_pro_tokens",
                array(
                    'store' => sanitize_text_field($data['store']),
                    'email' => sanitize_email($data['email']),
                    'token' => sanitize_text_field($data['token']),
                    'sync_orders' => isset($data['sync_orders']) ? (int)$data['sync_orders'] : 0,
                    'sync_stock' => isset($data['sync_stock']) ? (int)$data['sync_stock'] : 0,
                    'code_soft_platform' => sanitize_text_field($data['code_soft_platform'])
                ),
                array('id' => $id),
                array(
                    '%s',
                    '%s',
                    '%s',
                    '%d',
                    '%d',
                    '%s'
                ),
                array('%d')
            );

            if ($wpdb->last_error) {
                throw new Exception($wpdb->last_error);
            }

            return $result;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }
    // Obtener órdenes vinculadas con Dropi (continuación)
    public function get_dropi_linked_orders() {
        global $wpdb;

        $query = "
            SELECT p.ID as order_id, pm.meta_value as dropi_order_id
            FROM {$wpdb->prefix}posts p
            INNER JOIN {$wpdb->prefix}postmeta pm
            ON p.ID = pm.post_id
            WHERE pm.meta_key = %s
            AND pm.meta_value IS NOT NULL
            AND pm.meta_value != ''
            ORDER BY p.ID DESC
        ";

        $prepared_query = $wpdb->prepare($query, '_dropi_order_id');
        return $wpdb->get_results($prepared_query);
    }

    // Obtener todas las órdenes con el estado de Dropi
    public function get_all_orders_with_dropi_status($limit = 10, $offset = 0) {
        global $wpdb;

        $query = "
            SELECT p.ID as order_id, 
                   MAX(CASE WHEN pm.meta_key = '_dropi_order_id' THEN pm.meta_value ELSE NULL END) as dropi_order_id, 
                   p.post_date as order_date, 
                   p.post_status as order_status,
                   MAX(CASE WHEN pm_billing.meta_key = '_billing_first_name' THEN pm_billing.meta_value ELSE NULL END) as billing_name,
                   MAX(CASE WHEN pm_total.meta_key = '_order_total' THEN pm_total.meta_value ELSE NULL END) as order_total
            FROM {$wpdb->prefix}posts p
            LEFT JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id
            LEFT JOIN {$wpdb->prefix}postmeta pm_billing ON p.ID = pm_billing.post_id AND pm_billing.meta_key = '_billing_first_name'
            LEFT JOIN {$wpdb->prefix}postmeta pm_total ON p.ID = pm_total.post_id AND pm_total.meta_key = '_order_total'
            WHERE p.post_type = %s AND p.post_status != %s
            GROUP BY p.ID
            ORDER BY p.ID DESC
            LIMIT %d OFFSET %d
        ";

        $prepared_query = $wpdb->prepare($query, 'shop_order_placehold', 'trash', $limit, $offset);
        return $wpdb->get_results($prepared_query);
    }

    // Obtener el conteo total de órdenes
    public function get_total_orders_count() {
        global $wpdb;

        $query = "
            SELECT COUNT(*) 
            FROM {$wpdb->prefix}posts 
            WHERE post_type = %s AND post_status != %s
        ";

        return $wpdb->get_var($wpdb->prepare($query, 'shop_order_placehold', 'trash'));
    }

    public function delete_dropi_meta_record($order_id, $meta_key = '_dropi_order_id', $meta_value = '0') {
        global $wpdb;

        $meta_id = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_id FROM {$wpdb->postmeta} 
             WHERE post_id = %d AND meta_key = %s AND meta_value = %s 
             LIMIT 1",
            $order_id,
            $meta_key,
            $meta_value
        ));

        if ($meta_id) {
            $deleted = $wpdb->delete(
                $wpdb->postmeta,
                ['meta_id' => $meta_id],
                ['%d']
            );

            if ($deleted) {
                error_log('Se eliminó correctamente el registro meta con meta_id: ' . $meta_id);
                return true;
            } else {
                error_log('No se pudo eliminar el registro meta con meta_id: ' . $meta_id);
            }
        } else {
            error_log('No se encontró un registro meta correspondiente para order_id: ' . $order_id);
        }

        return false;
    }

    public function delete_dropi_data() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'dropi_pro_tokens';
        $result_table_delete = $wpdb->query("DELETE FROM {$table_name}");
        
        if ($result_table_delete === false) {
            error_log('Error al eliminar los datos de la tabla dropi_pro_tokens: ' . $wpdb->last_error);
            return false;
        } else {
            error_log('Datos eliminados correctamente de la tabla dropi_pro_tokens.');
        }
    
        $meta_key = '_dropi_order_id';
        $result_meta_delete = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s",
                $meta_key
            )
        );
    
        if ($result_meta_delete === false) {
            error_log('Error al eliminar los metadatos de Dropi: ' . $wpdb->last_error);
            return false;
        } else {
            error_log('Metadatos eliminados correctamente de la tabla postmeta.');
        }
    
        return true;
    }
    
    
}

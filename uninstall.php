<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

$table_name = $wpdb->prefix . 'dropi_pro_tokens';
$wpdb->query("DROP TABLE IF EXISTS {$table_name}");

$meta_key = '_dropi_order_id';
$wpdb->query($wpdb->prepare(
    "DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s",
    $meta_key
));

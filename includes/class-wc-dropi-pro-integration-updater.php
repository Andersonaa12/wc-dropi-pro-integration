<?php

class WC_Dropi_Pro_Integration_Updater {

    private $current_version;
    private $remote_version;

    public function __construct($current_version) {
        $this->current_version = $current_version;
    }

    // Nueva versión disponible
    public function check_for_update() {
        $this->remote_version = $this->get_remote_version();
        
        if (version_compare($this->current_version, $this->remote_version, '<')) {
            return true;
        }

        return false;
    }

    private function get_remote_version() {
        $response = wp_remote_get('http://localhost/wc-dropi-pro-integration/version.json');
        if (is_wp_error($response)) {
            return $this->current_version;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);

        return isset($data->version) ? $data->version : $this->current_version;
    }

    public function update_plugin() {
        if ($this->check_for_update()) {
        }
    }
}

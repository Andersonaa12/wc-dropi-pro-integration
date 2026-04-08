<?php

define('STOCKAGO_API_URL_DEFAULT', 'https://dropipro.com/api/woocommerce/');
define('STOCKAGO_API_URL_TEST', 'https://test-dev.stockago.com/api/woocommerce/');
define('STOCKAGO_API_URL_PLATFORM_1', 'https://almacen.coinnecta.es/api/woocommerce/');
define('STOCKAGO_API_URL_PLATFORM_2', 'https://dropstack.co/api/woocommerce/');
define('STOCKAGO_API_URL_PLATFORM_3', 'https://es.droplatam.com.br/api/woocommerce/');
define('STOCKAGO_API_URL_PLATFORM_4', 'https://almacenjustecompro.com/api/woocommerce/');
define('STOCKAGO_API_URL_PLATFORM_5', 'https://almacen.ecomuniversidad.com/api/woocommerce/');
define('STOCKAGO_API_URL_PLATFORM_6', 'https://mbadrop.com/api/woocommerce/');
define('STOCKAGO_API_URL_PLATFORM_7', 'https://fulfillment.stockers.ai/api/woocommerce/');
function get_stockago_api_url($platform_code) {
    switch ($platform_code) {
        case 'TEST':
            return STOCKAGO_API_URL_TEST;
        case 'COINNECTA':
            return STOCKAGO_API_URL_PLATFORM_1;
        case 'DROPSTACK':
            return STOCKAGO_API_URL_PLATFORM_2;
        case 'DROPLATAM':
            return STOCKAGO_API_URL_PLATFORM_3;
        case 'JUSTECOM':
            return STOCKAGO_API_URL_PLATFORM_4;
        case 'UNIVERSIDADECOM':
            return STOCKAGO_API_URL_PLATFORM_5;
        case 'MBA':
            return STOCKAGO_API_URL_PLATFORM_6;    
        case 'STOCKERS':
            return STOCKAGO_API_URL_PLATFORM_7;   
        default:
            return STOCKAGO_API_URL_DEFAULT;
    }
}

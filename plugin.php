<?php

/**
 * WordPress REST API
 * @version 1.2.0
 */

use WordPressRESTAPI\WPRest;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/classes/WPRest.php';

/**
 * WP Rest
 *
 * Global function for using the WP Rest class
 */
function wprest() {
    return WPRest::get_instance();
}

do_action( 'wprest.init' );

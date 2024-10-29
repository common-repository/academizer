<?php

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}
global $wpdb;
$option_name = 'academizer_options';
delete_option($option_name);
delete_site_option($option_name);
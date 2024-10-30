<?php
/**
 * Uninstall script for coconuttickets plugin.
 * Removes any options created during the lifetime of the plugin.
 * coconuttickets.com
 */

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}


$option_name = 'coconuttickets_notice';
delete_option($option_name);

// for site options in Multisite
delete_site_option($option_name);

?>
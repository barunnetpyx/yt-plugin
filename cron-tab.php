<?php 
// include wordpress functions
define('__ROOT__', dirname(dirname(dirname(dirname(__FILE__))))); 
include( __ROOT__.'/wp-load.php');
require_wp_db();
global $wpdb;

channel_proccessCronReal();
?>
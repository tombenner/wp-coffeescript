<?php
/*
Plugin Name: WP CoffeeScript
Plugin URI: http://wordpress.org/extend/plugins/wp-coffeescript/
Description: Allows developers to easily use CoffeeScript in WordPress. Simply use enqueue_coffeescript(); the compilation is done automatically behind the scenes.
Author: Tom Benner
Version: 1.0
Author URI: 
*/

require_once dirname(__FILE__).'/functions.php';
require_once dirname(__FILE__).'/lib/wp_coffeescript.php';

global $wpcs;
$wpcs = new WpCoffeescript();

register_deactivation_hook(__FILE__, array($wpcs, 'deactivate'));

?>
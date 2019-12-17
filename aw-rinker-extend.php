<?php
/*
Plugin Name: AW Rinker Extend
Plugin URI: https://careru.jp/
Description: Rinker拡張
Author: Alternative Works
Version: 0.0.3
Author URI: https://careru.jp/
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once( dirname( __FILE__ ) . '/update-checker/update-checker.php');
require_once( dirname( __FILE__ ) . '/common/main.php' );
require_once( dirname( __FILE__ ) . '/common/shortcode.php' );
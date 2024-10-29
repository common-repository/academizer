<?php
/*
 * Plugin Name: Academizer
 * Description: Academizer can help you manage Bibtex references. It automatically parses Bibtex notation and renders HTML using user-defined citation styles.
 * Author: Adalberto L. Simeone
 * Version: 1.1
 * License: GPL3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Author URI: http://www.adalsimeone.me
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
define( 'ACADEMIZER_PLUGIN_PATH', plugin_dir_url( __FILE__ ) );
define( 'ACADEMIZER_VERSION', "1.1");
include_once('includes/academizer-options.php');
include_once('includes/academizer-post-type.php');
include_once('includes/academizer-post-meta-boxes.php');
include_once('includes/academizer-references.php');

add_action( 'wp_enqueue_scripts', 'academizer_styles');

function academizer_styles() {
    wp_enqueue_style('bootstrap', ACADEMIZER_PLUGIN_PATH . 'css/bootstrap.min.css');
    wp_register_style('academizer', ACADEMIZER_PLUGIN_PATH . 'css/academizer.css');
    wp_register_script('popper', ACADEMIZER_PLUGIN_PATH . 'js/popper.min.js');
    wp_register_script( 'bootstrap-js', ACADEMIZER_PLUGIN_PATH . 'js/bootstrap.min.js', array('jquery'), '4.0', true );
    wp_register_script('academizer_bibtexParser', ACADEMIZER_PLUGIN_PATH . 'js/bibtexParse.js');
    wp_register_script('academizer_clientReferences', ACADEMIZER_PLUGIN_PATH . 'js/clientReferences.js');
}
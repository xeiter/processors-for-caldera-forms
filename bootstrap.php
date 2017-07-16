<?php

/**
 * Plugin Name: Processors for Caldera Forms
 * Plugin URI:
 * Description: Extra processors for Caldera Forms (such as Zoho CRM etc.)
 * Version: 0.1.2
 * Author: Anton Zaroutski
 */

use Xeiter_Code\WP_Plugins\Processors_For_Caldera_Forms as PFCF;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Load once Caldera Forms is loaded, if it is loaded
 */
add_action( 'caldera_forms_includes_complete', function() {

	/**
	 * Class autoloader implementation
	 *
	 * @param string $class
	 */
	function xc_pfcf_autoload( $class ) {

		$prefix = 'Xeiter_Code\\WP_Plugins\\Processors_For_Caldera_Forms\\';
		$base_dir = __DIR__ . '/src/';

		$len = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			return;
		}

		$relative_class = substr( $class, $len );
		$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

		if ( file_exists( $file ) ) {
			require_once $file;
		}

	}

	// Register autoloader
	spl_autoload_register('xc_pfcf_autoload');

	// Define plugin constants
	define( 'XC_PFCF_CORE_PATH', plugin_dir_path(__FILE__) );
	define( 'XC_PFCF_URL', plugin_dir_url(__FILE__)) ;
	define( 'XC_PFCF_VER', '0.1.2');
	define( 'XC_PFCF_BASENAME', plugin_basename( __FILE__ ) );

	// Initialise the global that will store list of already processed processors
	// This is used for the case where the same Zoho CRM controller is added to the form more than once
	global $xc_pfcf_processors;
	$xc_pfcf_processors = [];

	if ( class_exists('\\Xeiter_Code\\WP_Plugins\\Processors_For_Caldera_Forms\\Controller') ) {
		new PFCF\Controller();
	}

});

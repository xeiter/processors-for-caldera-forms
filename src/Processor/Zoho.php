<?php

namespace Xeiter_Code\WP_Plugins\Processors_For_Caldera_Forms\Processor;
use Xeiter_Code\WP_Plugins\Processors_For_Caldera_Forms\Adapter as Adapter;

class Zoho extends \Caldera_Forms_Processor_Processor {

	/**
	 *  Pre-process callback
	 *
	 * @param array $config Processor config
	 * @param array $form Form config
	 * @param string $proccesid Process ID
	 *
	 * @return array|null
	 */
	public function pre_processor( array $config, array $form, $proccesid ) {
	}

	/**
	 * Process callback
	 *
	 * @param array $config Processor config
	 * @param array $form Form config
	 * @param string $procces_id Process ID
	 * @return array
	 * @access public
	 */
	public function processor(array $config, array $form, $procces_id ){

		// Build a data array of submitted data
		$data = array();

		// Raw data is an array with field_id as the key
		$raw_data = \Caldera_Forms::get_submission_data( $form );

		// create a new array using the slug as the key
		foreach( $raw_data as $field_id => $field_value ){

			// Don't add buttons or html fields to data array as they are not capture values
			if( !isset( $form[ 'fields' ][ $field_id ] ) || in_array( $form[ 'fields' ][ $field_id ][ 'type' ], array( 'button', 'html' ) ) )
				continue;

			// Get the field slug for the key instead
			$data[ $form[ 'fields' ][ $field_id ][ 'slug' ] ] = $field_value;

		}

		$auth_token = $config['auth_token'];

		if ( $auth_token ) {
			$adapter = new \Xeiter_Code\WP_Plugins\Processors_For_Caldera_Forms\Adapter\Zoho( $auth_token );
			// $adapter->set_test_mode(true);
			$response = $adapter->create_lead( $data, $config );
		}

	}

}
<?php

/**
 * Class Mock_Adapter
 *
 * Acts as a provider of mocked responses from Zoho API
 */

namespace Xeiter_Code\WP_Plugins\Processors_For_Caldera_Forms\Adapter\Mock;

class Zoho {

	/**
	 * Get test lead fields
	 *
	 * @return array
	 * @access public
	 */
	public function get_lead_fields($processor_id) {

		$entry = new \stdClass();

		$entry->FL = new \stdClass();
		$entry->FL->dv = 'ref1';
		$entry->FL->label = 'Nice label';

		$entry_no_fl = new \stdClass();
		$entry_no_fl->dv = 'ref2';
		$entry_no_fl->label = 'Nice label 2';


		$entry_no_fl_2 = new \stdClass();
		$entry_no_fl_2->dv = 'ref3';
		$entry_no_fl_2->label = 'Nice label 3';

		$entry_array = new \stdClass();
		if ( $processor_id == 'fp_77586315' ) {
			$entry_array->FL = array($entry_no_fl, $entry_no_fl_2);
		} else {
			$entry_array->FL = array($entry_no_fl);
		}

		$test_response = array(
			$entry_array,
			$entry
		);

		return $test_response;

	}

	/**
	 * Get dummy API response after create lead call
	 * @return stdClass;
	 */
	public function insert_lead() {

		$result = new stdClass();

		return $result;

	}

}
<?php

namespace Xeiter_Code\WP_Plugins\Processors_For_Caldera_Forms\Adapter;

class Zoho {

	/**
	 * Zoho CRM endpoint URL
	 *
	 * @var string
	 * @access protected
	 */
	protected $api_endpoint = 'https://crm.zoho.com/crm/private';

	/**
	 * Caldera Form ID
	 * @var string
	 * @access protected
	 */
	protected $form_id = null;

	/**
	 * Caldera Form processor ID
	 * @var string
	 * @access protected
	 */
	protected $processor_id = null;

	/**
	 * URL suffix for geting lead's fields API call
	 *
	 * @var string
	 * @access protected
	 */
	protected $url_suffix_get_lead_fields = '/json/Leads/getFields';

	/**
	 * URL suffix for insert lead API call
	 *
	 * @var string
	 * @access protected
	 */
	protected $url_suffix_insert_lead = '/xml/Leads/insertRecords?';

	/**
	 * Authentication token used by API calls
	 *
	 * @var null|string
	 * @access protected
	 */
	protected $auth_token = null;

	/**
	 * Specifies whether we are running in a test most
	 * @var bool
	 * @access protected
	 */
	protected $test_mode = false;

	/**
	 * Mock adapter used instead when test mode is enabled
	 *
	 * @var Mock_Adapter|null
	 * @access protected
	 */
	protected $mock_adapter = null;

	/**
	 * Prefix of lead payload template
	 * @var string
	 * @access private
	 */
	private $lead_payload_prefix = <<<MULTI
<Leads>
	<row no="1">
		<FL val="Lead Source">Web Download</FL>
MULTI;

	/**
	 * Lead payload row template
	 * @var string
	 * @access private
	 */
	private $lead_payload_row_template = "		<FL val=\"{FIELDNAME}\">{FIELDVALUE}</FL>\n";

	/**
	 * Suffix of lead payload template
	 * @var string
	 * @access private
	 */
	private $lead_payload_suffix = <<<MULTI
	</row>
</Leads>
MULTI;

	public function __construct( $token, $form_id = null, $processor_id = null ) {

		$this->set_auth_token( $token );
		$this->set_form_id( $form_id );
		$this->set_processor_id( $processor_id );
		$this->mock_adapter = new Mock\Zoho();

	}

	/**
	 * Get URL for the API call to fetch fields associated with a lead in Zoho CRM
	 *
	 * @return string
	 * @access protected
	 */
	protected function get_url_for_lead_fields() {

		$parameters = [
			'authtoken=' . $this->auth_token,
			'scope=crmapi'
		];

		return $this->api_endpoint . $this->url_suffix_get_lead_fields . '?' . implode( '&', $parameters );
	}

	/**
	 * Get URL for create lead API call
	 * @return string
	 * @access protected
	 */
	protected function get_url_for_create_lead() {
		return $this->api_endpoint . $this->url_suffix_insert_lead;
	}

	/**
	 * Set Zoho CRM token
	 *
	 * @param string $auth_token
	 * @access protected
	 */
	protected function set_auth_token( $auth_token ) {
		$this->auth_token = $auth_token;
	}

	/**
	 * Set Caldera form ID
	 *
	 * @param string $form_id
	 * @access protected
	 */
	protected function set_form_id( $form_id ) {
		$this->form_id = $form_id;
	}

	/**
	 * Set Processor ID
	 *
	 * @param string $processor_id
	 * @access protected
	 */
	protected function set_processor_id( $processor_id ) {
		$this->processor_id = $processor_id;
	}

	/**
	 * Get all fields associated with a lead in Zoho CRM
	 *
	 * @return array|bool
	 * @access public
	 */
	public function get_lead_fields() {

		if ( $this->test_mode ) {
			$mock_data = $this->mock_adapter->get_lead_fields($this->processor_id);
			$this->set_lead_fields_transient($mock_data);
			return $mock_data;
		}

		// Get cached value of lead fields and return if it is available
		$cached_value = $this->get_lead_fields_transient();

		if ( $cached_value ) {
			return $cached_value;
		}

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => $this->get_url_for_lead_fields(),
		));
		$resp = json_decode( curl_exec( $curl ) );
		curl_close($curl);

		if ( $resp && isset( $resp->Leads ) && isset($resp->Leads->section ) ) {
			$this->set_lead_fields_transient($resp->Leads->section);
			return $resp->Leads->section;
		}

		return false;

	}

	/**
	 * Create a new lead in Zoho CRM
	 *
	 * @param array $data
	 * @param array $config
	 * @return bool|stdClass
	 * @access public
	 */
	public function create_lead( $data, $config ) {

		$zoho_url = $this->get_url_for_create_lead();
		if ( $this->test_mode ) {
			return $this->mock_adapter->insert_lead();
		}

		$lead_data = $this->lead_payload_prefix;

		foreach ( $data as $key => $value ) {
			$lead_data .= $this->get_lead_payload_row( $key, $data, $config );
		}

		$lead_data .= $this->lead_payload_suffix;

		$parameters = array(
			'new_format=1',
			'authtoken=' . $this->auth_token,
			'scope=crmapi',
			'wfTrigger=true',
			'xmlData=' . $lead_data
		);

		$parameters_string = implode( '&', $parameters );

		$ch = curl_init($zoho_url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_exec($ch);
		curl_close($ch);

		return false;

	}

	/**
	 * Enable test mode
	 * If enabled, the real API calls are not made and mock data is returned instead
	 *
	 * @param bool $value
	 * @access public
	 */
	public function set_test_mode($value) {
		$this->test_mode = $value;
	}

	/**
	 * Get a string row for lead payload
	 *
	 * @param string $key
	 * @param array $data
	 * @param array $fields_config
	 * @return mixed|string
	 * @access private
	 */
	private function get_lead_payload_row( $key, $data, $fields_config ) {

		if ( isset( $fields_config[ $key . '_mapping' ]) && isset( $data[$key] ) ) {

			$row = $this->lead_payload_row_template;
			$row = str_replace( '{FIELDNAME}', $fields_config[ $key . '_mapping' ], $row );
			$row = str_replace( '{FIELDVALUE}', $data[ $key ], $row );

			return $row;
		}

		return false;

	}

	/**
	 * Get cache data for the lead fields API call
	 *
	 * @return stdClass
	 * @access public
	 */
	public function get_lead_fields_transient() {
		return get_transient( 'xc_pfcf_lead_fields_' . $this->form_id . '_' . $this->processor_id );
	}

	/**
	 * Set cached data for the lead fields API call
	 *
	 * @param stdClass $value
	 * @return bool
	 * @access public
	 */
	public function set_lead_fields_transient( $value ) {
		return set_transient(   'xc_pfcf_lead_fields_' . $this->form_id . '_' . $this->processor_id, $value, 86400 );
	}

}
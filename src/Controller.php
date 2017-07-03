<?php

namespace Xeiter_Code\WP_Plugins\Processors_For_Caldera_Forms;

class Controller
{

	public function __construct()
	{
		$this->bootstrap();
	}

	/**
	 * Bootstrap the processor by registering it with Caldera forms and setting up its config
	 *
	 * @access protected
	 */
	protected function bootstrap()
	{
		// Register the processor with Caldera Forms
		add_action('caldera_forms_pre_load_processors', function () {
			new Processor\Zoho($this->get_config(), $this->get_fields(), 'zoho-crm-processor');
		});
	}

	/**
	 * Set up processor's config
	 *
	 * @return array
	 * @access public
	 */
	public function get_config()
	{
		return array(
			'name' => __('Zoho CRM Integration', 'az'),
			'description' => __('Submit leads to Zoho CRM', 'az'),
			'template' => dirname(__FILE__) . '/Processor/ZohoUI.php',
			'author' => 'Anton Zaroutski',
			'author_url' => 'http://zaroutski.com',
			'cf_ver' => '1.5.0',
			'fields' => $this->get_fields()
		);
	}

	/**
	 * Setup fields to pass to Caldera_Forms_Processor_UI::config_fields() in config
	 *
	 * @return array
	 * @access public
	 */
	public function get_fields()
	{

		// @todo Unfortunately we rely on global variables for setting values to Zoho CRM lead fields on the config screen
		global $xc_pfcf_processors;

		$auth_token = null;
		$processor_id = null;
		$config_fields = [];
		$select_options = [];
		$form_id = isset($_REQUEST['edit']) ? $_REQUEST['edit'] : $_REQUEST['_cf_frm_id'];
		$form_fields = \Caldera_Forms::get_form($form_id)['fields'];

		$form_fields_filtered = [];
		foreach ($form_fields as $field_id => $field_options) {
			if (!in_array($field_options['type'], ['button', 'html'])) {
				$form_fields_filtered[] = $field_options;
			}
		}

		$form_options = get_option($form_id);

		foreach ($form_options['processors'] as $processor) {
			if ($processor['type'] == 'zoho-crm-processor' && !in_array($processor['ID'], array_keys($xc_pfcf_processors))) {
				$auth_token = $processor['config']['auth_token'];
				$processor_id = $processor['ID'];
				$xc_pfcf_processors[$processor['ID']] = [];
				break;
			}
		}

		if ($processor_id) {

			// Instantiate Zoho CRM adapter
			$za = new Adapter\Zoho($auth_token, $form_id, $processor_id);
			// $za->set_test_mode(true);
			$zoho_fields_sections = $za->get_lead_fields();

			if (!empty($zoho_fields_sections)) {

				// Extract the fields names from ZOHO response
				$fields = null;
				foreach ($zoho_fields_sections as $section) {
					if (is_array($section->FL)) {
						foreach ($section->FL as $section_details) {
							$fields[$section_details->dv] = $section_details->label;
						}
					} else {
						$fields[$section->FL->dv] = $section->FL->label;
					}
				}

				$fields = array_filter($fields);
				asort($fields);

				$select_options = $this->generate_zoho_fields_dropdown_options($fields);

				foreach ($form_fields_filtered as $field_options) {
					$config_fields[] = [
						'id' => $field_options['slug'] . '_mapping',
						'label' => 'Map "' . $field_options['label'] . '" to ...',
						'type' => 'dropdown',
						'class' => 'yeahyeah',
						'required' => false,
						'magic' => true,
						'options' => $select_options
					];
				}

			}

			$xc_pfcf_processors[$processor['ID']] = $select_options;

		}

		$auth_token_field = array(array(
			'id' => 'auth_token',
			'label' => 'Zoho CRM authentication token <br/><a href="https://www.zoho.com/crm/help/api/using-authentication-token.html" target="_target">How to get it?</a>',
			'type' => 'text',
			'required' => true,
			'magic' => true,
		));

		$config_fields = array_merge($auth_token_field, $config_fields);

		return $config_fields;

	}

	/**
	 * Select dropdown options for the select field that holds all available Leads fields in Zoho
	 *
	 * @param array $fields
	 * @return array
	 */
	protected function generate_zoho_fields_dropdown_options($fields)
	{

		$select_options = [];
		$select_options[''] = '-- Map existing field --';

		foreach ($fields as $field_name => $field_label) {
			$select_options[$field_name] = $field_label;
		}

		return $select_options;

	}

}
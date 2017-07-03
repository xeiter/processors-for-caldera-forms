<?php echo Caldera_Forms_Processor_UI::config_fields( $config['fields'] ); ?>
<?php global $xc_pfcf_processors; ?>

<script type="text/javascript">

	jQuery(document).ready(function() {

		// Get fields data object
		const fields = '<?= str_replace(["\n", "\r"], '', strip_tags(json_encode($form['fields']))); ?>';
		const fields_no_linebreaks = fields.replace(/(?:\r\n|\r|\n)/g, "");
		form_fields = jQuery.parseJSON(fields_no_linebreaks);

		// Set the default value of each field if required
		for ( processor_id in form_fields ) {

			options = "";
			field_name = form_fields[processor_id]['slug'];

			// Work out the name of the field so its default value can be set
			select_name = "config[processors][" + processor_id + "][config][" + field_name + "_mapping]";

			for (j in fields[processor_id]) {
				if ( jQuery("[name^='" + select_name + "'] option[value='" + j + "']").is(":selected") ) {
					options += '<option selected value="'+ j + '">' + fields[processor_id][j] + '</option>';
				} else {
					options += '<option value="'+ j + '">' + fields[processor_id][j] + '</option>';
				}
			}

			// Replace the options mark up for each of the dropdown with Zoho CRM lead fields selection
			jQuery("[name^='" + select_name + "']").html(options);

		}

	});

</script>

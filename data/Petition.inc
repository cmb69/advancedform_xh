<?php

function advfrm_custom_valid_field($form_name, $field_name, $value) {
    $unique_field = 'EMail'; // the name of the field that must be unique
    $error = 'You have already submitted the petition!';

    if ($field_name == $unique_field) {
	$data = Advancedform_readCsv($form_name);
	$emails = array_map(create_function('$elt', 'return $elt[\''.$unique_field.'\'];'), $data);
	$found = array_search($value, $emails) !== FALSE;
	if ($found) {
	    return $error;
	}
    }
    return TRUE;
}

?>

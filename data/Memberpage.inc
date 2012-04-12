<?php

function advfrm_custom_field_default($form_name, $field_name, $opt, $is_resent) {
    switch ($field_name) {
	case 'Name':
	    return isset($_SESSION['Name']) ? $_SESSION['Name']
		    : (isset($_SESSION['fullname']) ? $_SESSION['fullname'] : NULL);
	case 'E_Mail':
	    return isset($_SESSION['email']) ? $_SESSION['email'] : NULL;
	default:
	    return NULL;
    }
}

?>

<?php

// utf-8-marker: äöüß


function advfrm_custom_mail($form_name, &$mail, $is_confirmation) {

    // Sender receives a copy of the filled out form but from a noreply@ address
    // without being able to find out to which address the mail has been delivered
    if ($is_confirmation) {
	$mail->set('From', 'noreply@example.com');	// change address according to your needs
	$mail->set('FromName', 'Unknown');		// change name (can be empty)
    }
    
    // Sender can fill in a subject which then overrules the standard subject
    // and appears as the subject of the e-mail
    $field_name = 'Subject'; // change to the name your subject field
    $mail->set('Subject', stsl($_POST['advfrm-'.$field_name]));
    
}

?>

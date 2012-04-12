<?php

function advfrm_custom_mail($form_name, &$mail, $is_confirmation) {
    $f = advfrm_fields();
    if ($is_confirmation) {

	/*
	 * Adjust the following text between HTML and HTML to your needs.
	 * To insert the values of fields, use the notation $f[Fieldname]
	 */
	$html = <<<HTML
<p>Dear $f[Title] $f[Name],</p>

<p>You have agreed on my website to my terms of use.</p>

<p>Kind Regards,<br>
The Webmaster</p>

HTML;

	/*
	 * Adjust the following text between TEXT and TEXT to your needs.
	 * To insert the values of fields, use the notation $f[Fieldname]
	 */
	$text = <<<TEXT
Dear $f[Title] $f[Name],

You have agreed on my website to my terms of use.

Kind Regards,
The Webmaster


TEXT;

	// enter the labels of the fields that should not be printed in the mail
	$filter_labels = array('Confirmation', 'E-Mail');

	$body = $mail->Body;
	foreach ($filter_labels as $filter_label) {
	    $body = preg_replace('/<tr><td class="label">'.$filter_label.'<\/td>.*?<\/tr>/isu', '', $body);
	}
	$body = str_replace('<div class="advfrm-mailform">', '<div class="advfrm-mailform">'.$html, $body);
	$mail->set('Body', $body);

	$body = $mail->AltBody;
	foreach ($filter_labels as $filter_label) {
	    $body = preg_replace('/'.$filter_label.'\n.*?\n/su', '', $body);
	}
	$body = $text.$body;
	$mail->set('AltBody', $body);
    }
}

?>

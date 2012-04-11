<?php

function advfrm_custom_thanks_page($form_name, $fields) {
    switch ($fields['Payment_type']) { // the field name of the radiobuttons
        case 'Cheque': // the label of radiobutton1
            return 'Thank_you1'; // the thanks page for cheque payment
            break;
        case 'Pay-Pal': // the label of radiobutton2
            return 'Thank_you2'; // the thanks page for pay-pal payment
            break;
        default:
            return ''; // redirects to the default Thanks page
    }
}

?>

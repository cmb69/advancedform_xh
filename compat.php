<?php

function advfrm_focus_field($form_id, $name)
{
    return Advancedform_focusField($form_id, $name);
}

function advfrm_read_csv($id)
{
    return Advancedform_readCsv($id);
}

function advfrm_fields()
{
    return Advancedform_fields();
}

function advfrm_display_field($form_id, $field)
{
    return Advancedform_displayField($form_id, $field);
}

?>

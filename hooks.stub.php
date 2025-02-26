<?php

/**
 * @param string $form_name
 * @param string $field_name
 * @param string|null $opt
 * @param bool $is_recent
 * @return bool|null
 */
function advfrm_custom_field_default($form_name, $field_name, $opt, $is_resent) {}

/**
 * @param string $form_name
 * @param string $field_name
 * @param string $value
 * @return bool|string
 */
function advfrm_custom_valid_field($form_name, $field_name, $value) {}

/**
 * @param string $form_name
 * @param Advancedform\PHPMailer\PHPMailer $mail
 * @param bool $is_confirmation
 * @return bool
 */
function advfrm_custom_mail($form_name, $mail, $is_confirmation) {}

/**
 * @param string $form_name
 * @param array $fields
 * @return string
 */
function advfrm_custom_thanks_page($form_name, $fields) {}

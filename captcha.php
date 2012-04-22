<?php

/**
 * Minimal built-in captcha of Advancedform_XH.
 *
 * Copyright (c) 2011-2012 Christoph M. Becker (see license.txt)
 */


if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}


/**
 * Returns the captcha code.
 *
 * @return string
 */
function advfrm_captcha_code() {
    $res = '';
    for ($i = 0; $i < 5; $i++) {
	$res .= rand(0, 9);
    }
    return $res;
}


/**
 * Returns the (x)html block element displaying the captcha,
 * the input field for the captcha code and all other elements,
 * that are related directly to the captcha,
 * such as an reload and an audio button.
 *
 * @return string
 */
function advancedform_captcha_display() {
    global $plugin_tx;

    $code = advfrm_captcha_code();
    $_SESSION['advfrm_captcha_id'] = isset($_SESSION['advfrm_captcha_id'])
	    ? $_SESSION['advfrm_captcha_id'] + 1 : 1;
    $_SESSION['advfrm_captcha'][$_SESSION['advfrm_captcha_id']] = $code;
    return '<div class="captcha">'
	    .'<span class="captcha-explanation">'.$plugin_tx['advancedform']['captcha_explanation'].'</span>'
	    .'<span class="captcha">'.$code.'</span>'
	    .tag('input type="text" name="advancedform-captcha"')
	    .tag('input type="hidden" name="advancedform-captcha_id" value="'.$_SESSION['advfrm_captcha_id'].'"')
	    .'</div>'."\n";
}


/**
 * Returns whether the correct captcha code was entered
 * after the form containing the captcha was posted.
 *
 * @return bool
 */
function advancedform_captcha_check() {
    $ok = stsl($_POST['advancedform-captcha']) == $_SESSION['advfrm_captcha'][$_POST['advancedform-captcha_id']];
    unset($_SESSION['advfrm_captcha'][$_POST['advancedform-captcha_id']]);
    return $ok;
}


if (session_id() == '') {session_start();}

?>

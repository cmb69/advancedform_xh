<?php

/**
 * Minimal built-in captcha of Advancedform_XH.
 *
 * PHP versions 4 and 5
 *
 * @category   CMSimple_XH
 * @package    Advancedform
 * @subpackage Captcha
 * @author     Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright  2005-2010 Jan Kanters
 * @copyright  2011-2013 Christoph M. Becker <http://3-magi.net>
 * @license    http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version    SVN: $Id$
 * @link       http://3-magi.net/?CMSimple_XH/Advancedform_XH
 */

/**
 * Returns the captcha code.
 *
 * @return string
 */
function Advancedform_Captcha_code()
{
    $res = '';
    for ($i = 0; $i < 5; $i++) {
        $res .= rand(0, 9);
    }
    return $res;
}


/**
 * Returns the block element displaying the captcha,
 * the input field for the captcha code and all other elements,
 * that are related directly to the captcha,
 * such as an reload and an audio button.
 *
 * @return string (X)HTML.
 *
 * @global array The localization of the plugins.
 */
function Advancedform_Captcha_display()
{
    global $plugin_tx;

    $code = Advancedform_Captcha_code();
    $_SESSION['advfrm_captcha_id'] = isset($_SESSION['advfrm_captcha_id'])
        ? $_SESSION['advfrm_captcha_id'] + 1
        : 1;
    $_SESSION['advfrm_captcha'][$_SESSION['advfrm_captcha_id']] = $code;
    return '<div class="captcha">'
        . '<span class="captcha-explanation">'
        . $plugin_tx['advancedform']['captcha_explanation'] . '</span>'
        . '<span class="captcha">' . $code . '</span>'
        . tag('input type="text" name="advancedform-captcha"')
        . tag(
            'input type="hidden" name="advancedform-captcha_id"'
            . ' value="'.$_SESSION['advfrm_captcha_id'].'"'
        )
        . '</div>' . PHP_EOL;
}

/**
 * Returns whether the correct captcha code was entered
 * after the form containing the captcha was posted.
 *
 * @return bool
 */
function Advancedform_Captcha_check()
{
    $ok = isset($_SESSION['advfrm_captcha'][$_POST['advancedform-captcha_id']])
        && stsl($_POST['advancedform-captcha'])
        == $_SESSION['advfrm_captcha'][$_POST['advancedform-captcha_id']];
    unset($_SESSION['advfrm_captcha'][$_POST['advancedform-captcha_id']]);
    return $ok;
}

if (session_id() == '') {
    session_start();
}

?>

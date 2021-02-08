<?php

/**
 * Copyright 2005-2010 Jan Kanters
 * Copyright 2011-2021 Christoph M. Becker
 *
 * This file is part of Advancedform_XH.
 *
 * Advancedform_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Advancedform_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Advancedform_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Returns the captcha code.
 *
 * @return string
 */
function Advancedform_Captcha_code()
{
    do {
        $num = unpack('V', random_bytes(3) . "\0")[1];
    } while ($num > 16777209);
    $res = '';
    for ($i = 0; $i < 5; $i++) {
        $res .= $num % 10;
        $num = (int) ($num / 10);
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
 */
function Advancedform_Captcha_display()
{
    global $plugin_cf, $plugin_tx;

    $code = Advancedform_Captcha_code();
    $hmac = hash_hmac('sha256', $code, $plugin_cf['advancedform']['captcha_key']);
    return '<div class="captcha">'
        . '<span class="captcha-explanation">'
        . $plugin_tx['advancedform']['captcha_explanation'] . '</span>'
        . '<span class="captcha">' . $code . '</span>'
        . '<input type="text" name="advancedform-captcha">'
        . '<input type="hidden" name="advancedform-mac" value="' . $hmac . '">'
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
    global $plugin_cf;

    $code = $_POST['advancedform-captcha'];
    $hmac = hash_hmac('sha256', $code, $plugin_cf['advancedform']['captcha_key']);
    return $hmac === $_POST['advancedform-mac'];
}

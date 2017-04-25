<?php

/**
 * Front-end stubs of Advancedform_XH.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   Advancedform
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2005-2010 Jan Kanters
 * @copyright 2011-2017 Christoph M. Becker <http://3-magi.net>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://3-magi.net/?CMSimple_XH/Advancedform_XH
 */

/*
 * Prevent direct access.
 */
if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

/**
 * The version number.
 */
define('ADVANCEDFORM_VERSION', '@ADVANCEDFORM_VERSION@');

/**
 * Main plugin call.
 *
 * @param string $id A form ID.
 *
 * @return string (X)HTML.
 */
function advancedform($id)
{
    extract($GLOBALS);
    include_once $pth['folder']['plugins'] . 'advancedform/compat.php';
    include_once $pth['folder']['plugins'] . 'advancedform/advfrm.php';
    return Advancedform_main($id);
}

/**
 * Returns a link to a page, if it exists. Otherwise returns ''.
 *
 * Useful as replacement for mailformlink() in the template.
 *
 * @param string $page A page URL.
 *
 * @return string  (X)HTML.
 *
 * @global string The script name.
 * @global array  The localization of the core.
 * @global array  The page URLs.
 */
function advancedformlink($page)
{
    global $sn, $tx, $u;

    return in_array($page, $u)
        ? '<a href="' . $sn . '?' . $page . '">' . $tx['menu']['mailform'] . '</a>'
        : '';
}

/*
 * Handle the replacement of the built-in mailform.
 */
if ($f == 'mailform' && !empty($plugin_tx['advancedform']['contact_form'])) {
    $o .= '<h1>' . $tx['title']['mailform'] . '</h1>' . PHP_EOL
        . advancedform($plugin_tx['advancedform']['contact_form']);
    $f = '';
}

?>

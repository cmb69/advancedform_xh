<?php

/**
 * Front-end stubs of Advancedform_XH.
 * Copyright (c) 2005-2010 Jan Kanters
 * Copyright (c) 2011-2012 Christoph M. Becker (see license.txt)
 */


// utf-8-marker: äöüß


if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}


define('ADVFRM_DEBUG', FALSE); // RELEASE-TODO


/**
 * Main plugin call.
 *
 * @param string $id  Form ID.
 * @return string
 */
function advancedform($id) {
    extract($GLOBALS);
    include_once $pth['folder']['plugins'].'advancedform/advfrm.php';
    return advfrm_advancedform($id);
}


/**
 * Returns a link to $page, if it exists.
 * Otherwise returns ''.
 * Useful as replacement for mailformlink() in the template.
 *
 * @param string $page  The query string of the page to link to.
 * @return string	(x)html
 */
function advancedformlink($page) {
    global $sn, $tx, $u;

    return in_array($page, $u)
	    ? '<a href="'.$sn.'?'.$page.'">'.$tx['menu']['mailform'].'</a>'
	    : '';
}

?>

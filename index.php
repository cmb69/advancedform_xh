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
define('ADVANCEDFORM_VERSION', '1.2');

/**
 * The version of the database format.
 */
define('ADVFRM_DB_VERSION', 2);

/**
 * The index of the size property.
 */
define('ADVFRM_PROP_SIZE', 0);

/**
 * The index of the cols property.
 */
define('ADVFRM_PROP_COLS', 0);

/**
 * The index of the maxlen property.
 */
define('ADVFRM_PROP_MAXLEN', 1);

/**
 * The index of the rows property.
 */
define('ADVFRM_PROP_ROWS', 1);

/**
 * The index of the default property.
 */
define('ADVFRM_PROP_DEFAULT', 2);

/**
 * The index of the value property.
 */
define('ADVFRM_PROP_VALUE', 2);

/**
 * The index of the field types property.
 */
define('ADVFRM_PROP_FTYPES', 2);

/**
 * The index of the contstraint property.
 */
define('ADVFRM_PROP_CONSTRAINT', 3);

/**
 * The index of the error message property.
 */
define('ADVFRM_PROP_ERROR_MSG', 4);

/**
 * Main plugin call.
 *
 * @param string $id A form ID.
 *
 * @return string (X)HTML.
 */
function advancedform($id)
{
    return (new Advancedform\MailFormController)->main($id);
}

/**
 * Returns a link to a page, if it exists. Otherwise returns ''.
 *
 * Useful as replacement for mailformlink() in the template.
 *
 * @param string $page A page URL.
 *
 * @return string  (X)HTML.
 */
function advancedformlink($page)
{
    global $sn, $tx, $u;

    return in_array($page, $u)
        ? '<a href="' . $sn . '?' . $page . '">' . $tx['menu']['mailform'] . '</a>'
        : '';
}

function Advancedform_focusField($form_id, $name)
{
    return Advancedform\Functions::focusField($form_id, $name);
}

function Advancedform_readCsv($id)
{
    return Advancedform\Functions::readCsv($id);
}

function Advancedform_fields()
{
    return Advancedform\Functions::fields();
}

/*
 * Handle the replacement of the built-in mailform.
 */
if ($f == 'mailform' && !empty($plugin_tx['advancedform']['contact_form'])) {
    $o .= '<h1>' . $tx['title']['mailform'] . '</h1>' . PHP_EOL
        . advancedform($plugin_tx['advancedform']['contact_form']);
    $f = '';
}

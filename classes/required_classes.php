<?php

/**
 * Copyright 2005-2010 Jan Kanters
 * Copyright 2011-2022 Christoph M. Becker
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

use Advancedform\Dic;
use Advancedform\FieldRenderer;
use Advancedform\MailFormController;
use Advancedform\MailService;
use Advancedform\Plugin;
use Advancedform\View;

/**
 * Main plugin call.
 *
 * @param string $id A form ID.
 *
 * @return string (X)HTML.
 */
function advancedform($id)
{
    global $sn, $pth, $plugin_cf, $plugin_tx;

    $formGateway = Dic::formGateway();
    $controller = new MailFormController(
        $formGateway,
        new FieldRenderer($id),
        $sn,
        $pth['folder']['plugins'],
        $plugin_cf['advancedform'],
        $plugin_tx['advancedform'],
        new MailService($formGateway->dataFolder(), $pth['folder']['plugins'], $plugin_tx['advancedform']),
        new View()
    );
    return $controller->main($id);
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

/**
 * @param string $form_id
 * @param string $name
 * @return void
 */
function Advancedform_focusField($form_id, $name)
{
    Plugin::focusField($form_id, $name);
}

/**
 * @param string $id
 * @return array<string,string>[]|false
 */
function Advancedform_readCsv($id)
{
    return Plugin::readCsv($id);
}

/**
 * @return array<string,(string|array<string>)>
 */
function Advancedform_fields()
{
    return Plugin::fields();
}

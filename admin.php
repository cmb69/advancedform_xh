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

XH_registerStandardPluginMenuItems(true);

/*
 * Handle the plugin administration.
 */
if (XH_wantsPluginAdministration('advancedform')) {
    if (include_once $pth['folder']['plugins'] . 'jquery/jquery.inc.php') {
        include_jQuery();
        include_jQueryUI();
    }
    $hjs .= '<script>ADVFRM_TX = ' . json_encode(Advancedform\Functions::getLangForJs()) . ';</script>';
    $hjs .= '<script src="' . $pth['folder']['plugins']
        . 'advancedform/admin.min.js"></script>' . PHP_EOL;

    $o .= print_plugin_admin('on');
    $temp = new Advancedform\AdminController;
    switch ($admin) {
        case '':
            $o .= $temp->infoAction();
            break;
        case 'plugin_main':
            switch ($action) {
                case 'new':
                    $o .= $temp->createFormAction();
                    break;
                case 'edit':
                    $o .= $temp->editFormAction($_GET['form']);
                    break;
                case 'save':
                    $o .= $temp->saveFormAction($_GET['form']);
                    break;
                case 'delete':
                    $o .= $temp->deleteFormAction($_GET['form']);
                    break;
                case 'copy':
                    $o .= $temp->copyFormAction($_GET['form']);
                    break;
                case 'import':
                    $o .= $temp->importFormAction($_GET['form']);
                    break;
                case 'export':
                    $o .= $temp->exportFormAction($_GET['form']);
                    break;
                case 'template':
                    $o .= $temp->createFormTemplateAction($_GET['form']);
                    break;
                default:
                    $o .= $temp->formsAdministrationAction();
            }
            break;
        default:
            $o .= plugin_admin_common($action, $admin, $plugin);
    }
}

<?php

/**
 * Copyright (c) Christoph M. Becker
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

namespace Advancedform;

use Plib\CsrfProtector;
use Plib\Random;
use Plib\SystemChecker;
use Plib\View;
use XH\Pages;

class Dic
{
    public static function mailFormController(string $id): MailFormController
    {
        global $pth, $plugin_cf, $plugin_tx, $sn;
        return new MailFormController(
            self::formGateway(),
            new FieldRenderer($id),
            $sn,
            $pth["folder"]["plugins"],
            $plugin_cf["advancedform"],
            $plugin_tx["advancedform"],
            new MailService(self::formGateway()->dataFolder(), $pth["folder"]["plugins"], $plugin_tx["advancedform"]),
            self::view()
        );
    }

    public static function infoController(): InfoController
    {
        global $pth, $plugin_cf;
        return new InfoController(
            self::formGateway(),
            $pth["folder"]["plugins"],
            $plugin_cf["advancedform"],
            new SystemChecker(),
            self::view()
        );
    }

    public static function mainAdminController(): MainAdminController
    {
        global $plugin_cf, $plugin_tx;
        return new MainAdminController(
            Dic::formGateway(),
            $plugin_cf["advancedform"],
            $plugin_tx["advancedform"],
            new CsrfProtector(),
            self::pages(),
            new Random(),
            self::view()
        );
    }

    public static function formGateway(): FormGateway
    {
        static $instance = null;

        if ($instance === null) {
            $instance = new FormGateway(self::dataFolder());
        }
        return $instance;
    }

    private static function pages(): Pages
    {
        return new Pages();
    }

    private static function view(): View
    {
        global $pth, $plugin_tx;
        return new View($pth["folder"]["plugins"] . "advancedform/templates/", $plugin_tx["advancedform"]);
    }

    private static function dataFolder(): string
    {
        global $pth, $plugin_cf;
        if ($plugin_cf["advancedform"]["folder_data"] == "") {
            $res = $pth["folder"]["plugins"] . 'advancedform/data/';
        } else {
            $res = $pth["folder"]["base"] . $plugin_cf["advancedform"]["folder_data"];
        }
        $res = rtrim($res, "/") . "/";
        return $res;
    }
}

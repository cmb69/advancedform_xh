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
            new View()
        );
    }

    public static function infoController(): InfoController
    {
        global $pth, $plugin_cf, $plugin_tx;
        return new InfoController(
            self::formGateway(),
            $pth["folder"]["plugins"],
            $plugin_cf["advancedform"],
            $plugin_tx["advancedform"]
        );
    }

    public static function mainAdminController(): MainAdminController
    {
        global $plugin_cf, $plugin_tx, $_XH_csrfProtection, $sn;
        return new MainAdminController(
            Dic::formGateway(),
            $sn,
            $plugin_cf["advancedform"],
            $plugin_tx["advancedform"],
            $_XH_csrfProtection,
            new View()
        );
    }

    public static function formGateway(): FormGateway
    {
        static $instance = null;

        if ($instance === null) {
            $instance = new FormGateway();
        }
        return $instance;
    }
}

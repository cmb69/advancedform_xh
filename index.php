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
use Plib\Request;

if (!defined("CMSIMPLE_XH_VERSION")) {
    http_response_code(403);
    exit;
}

/**
 * @var string $f
 * @var string $o
 * @var array<string,array<string,string>> $plugin_tx
 * @var array<string,array<string,string>> $tx
 */

// Handle replacement of built-in mailform
if ($f === "mailform" && $plugin_tx["advancedform"]["contact_form"] !== "") {#
    $temp = $plugin_tx["advancedform"]["contact_form"];
    $o .= "<h1>" . $tx["title"]["mailform"] . "</h1>\n"
        . Dic::mailFormController($temp)($temp, Request::current())();
    $f = "";
}

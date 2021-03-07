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

namespace Advancedform;

abstract class Controller
{
    /**
     * @var string
     */
    protected $scriptName;

    /**
     * @var string
     */
    protected $pluginsFolder;

    /**
     * @var array<string,string>
     */
    protected $conf;

    /**
     * @var array<string,string>
     */
    protected $text;

    public function __construct()
    {
        global $sn, $pth, $plugin_cf, $plugin_tx;

        $this->scriptName = $sn;
        $this->pluginsFolder = $pth['folder']['plugins'];
        $this->conf = $plugin_cf['advancedform'];
        $this->text = $plugin_tx['advancedform'];
    }
}

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

namespace Advancedform\Infra;

class CaptchaWrapper
{
    /** @var string */
    private $pluginsFolder;

    /** @var string */
    private $captcha;

    public function __construct(string $pluginsFolder, string $captcha)
    {
        $this->pluginsFolder = $pluginsFolder;
        $this->captcha = $captcha;
    }

    public function include(): bool
    {
        $filename = $this->pluginsFolder . $this->captcha . "/captcha.php";
        if (!is_file($filename)) {
            return false;
        }
        return (include_once $filename);
    }

    public function display(): string
    {
        $function = $this->captcha . "_captcha_display";
        if (!function_exists($function)) {
            return "";
        }
        return $function();
    }

    public function check(): bool
    {
        $function = $this->captcha . "_captcha_check";
        if (!function_exists($function)) {
            return false;
        }
        return $function();
    }
}

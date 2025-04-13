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

namespace Advancedform;

use Plib\Random;
use Plib\Request;
use Plib\View;

class Captcha
{
    /** @var string */
    private $key;

    /** @var Random */
    private $random;

    /** @var View */
    private $view;

    public function __construct(
        string $key,
        Random $random,
        View $view
    ) {
        $this->key = $key;
        $this->random = $random;
        $this->view = $view;
    }

    public function display(Request $request): string
    {
        $code = $this->code();
        $timestamp = $request->time();
        $salt = bin2hex($this->random->bytes(4));
        $hmac = hash_hmac("sha256", $code . $timestamp . $salt, $this->key);
        return $this->view->render("captcha", [
            "code" => $code,
            "timestamp" => $timestamp,
            "salt" => $salt,
            "hmac" => $hmac,
        ]);
    }

    private function code(): string
    {
        do {
            $num = unpack('V', $this->random->bytes(3) . "\0")[1];
        } while ($num > 16777209);
        $res = '';
        for ($i = 0; $i < 5; $i++) {
            $res .= $num % 10;
            $num = (int) ($num / 10);
        }
        return $res;
    }

    public function check(Request $request): bool
    {
        $code = $request->post("advancedform-captcha") ?? "";
        $timestamp = (int) ($request->post("advancedform-timestamp") ?? 0);
        $salt = $request->post("advancedform-salt") ?? "";
        $hmac = hash_hmac("sha256", $code . $timestamp . $salt, $this->key);
        return $request->time() <= $timestamp + 5 * 60
            && hash_equals($hmac, $request->post("advancedform-hmac") ?? "");
    }
}

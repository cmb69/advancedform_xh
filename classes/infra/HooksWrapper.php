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

use Advancedform\PHPMailer\PHPMailer;

class HooksWrapper
{
    /** @var string */
    private $dataFolder;

    /** @var string */
    private $ext;

    public function __construct(string $dataFolder, string $ext)
    {
        $this->dataFolder = $dataFolder;
        $this->ext = $ext;
    }

    public function include(string $formname): void
    {
        $filename = $this->dataFolder . $formname . ".inc" . $this->ext;
        if (is_file($filename)) {
            include_once $filename;
        }
    }

    /** @return string|bool|null */
    public function fieldDefault(string $formname, string $fieldname, ?string $opt, bool $isResent)
    {
        if (!function_exists("advfrm_custom_field_default")) {
            return null;
        }
        return advfrm_custom_field_default($formname, $fieldname, $opt, $isResent);
    }

    /**
     * @param string|array<string,mixed> $value
     * @return true|string
     */
    public function validField(string $formname, string $fieldname, $value)
    {
        if (!function_exists("advfrm_custom_valid_field")) {
            return true;
        }
        return advfrm_custom_valid_field($formname, $fieldname, $value);
    }

    public function mail(string $formname, PHPMailer $mailer, bool $isConfirmation): bool
    {
        if (!function_exists("advfrm_custom_mail")) {
            return true;
        }
        return advfrm_custom_mail($formname, $mailer, $isConfirmation);
    }

    /** @param array<string,mixed> $fields */
    public function thanksPage(string $formname, array $fields): ?string
    {
        if (!function_exists("advfrm_custom_thanks_page")) {
            return null;
        }
        return advfrm_custom_thanks_page($formname, $fields);
    }
}

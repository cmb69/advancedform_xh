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

class Validator
{
    /** @var array<string,string> */
    private $conf;

    /** @var array<string,string> */
    private $text;

    /**
     * @var array<int,string>
     * @read-only
     */
    public $errors = [];

    /**
     * @var array<int,string>
     * @read-only
     */
    public $focusField = [];

    /**
     * @param array<string,string> $conf
     * @param array<string,string> $text
     */
    public function __construct($conf, $text)
    {
        $this->conf = $conf;
        $this->text = $text;
    }

    /**
     * Checks sent form. Returns true on success, an (X)HTML error message on failure.
     *
     * @param Form $form
     *
     * @return bool
     */
    public function check(Form $form)
    {
        $this->errors = [];
        $this->focusField = [];
        $res = true;
        foreach ($form->getFields() as $field) {
            $name = 'advfrm-' . $field->getName();
            if ($this->isMissing($field)) {
                $res = $this->checkRequired($form, $field) && $res;
            } else {
                switch ($field->getType()) {
                    case 'from':
                    case 'mail':
                        $res = $this->checkMail($form, $field);
                        break;
                    case 'date':
                        $res = $this->checkDate($form, $field);
                        break;
                    case 'number':
                        $res = $this->checkNumber($form, $field);
                        break;
                    case 'file':
                        $res = $this->checkFile($form, $field);
                        break;
                    case 'custom':
                        $res = $this->checkCustom($form, $field);
                }
                if (function_exists('advfrm_custom_valid_field')) {
                    $value = $field->getType() == 'file'
                        ? $_FILES[$name]
                        : $_POST[$name];
                    $valid = advfrm_custom_valid_field($form->getName(), $field->getName(), $value);
                    if ($valid !== true) {
                        $this->errors[] = $valid;
                        if (empty($this->focusField)) {
                            $this->focusField = [$form->getName(), $name];
                        }
                        $res = false;
                    }
                }
            }
        }
        if ($form->getCaptcha()) {
            if (!call_user_func($this->conf['captcha_plugin'] . '_captcha_check')) {
                $this->errors[] = $this->text['error_captcha_code'];
                if (empty($this->focusField)) {
                    $this->focusField = [$form->getName(), 'advancedform-captcha'];
                }
                $res = false;
            }
        }
        return $res;
    }

    /**
     * @return bool
     */
    private function checkRequired(Form $form, Field $field)
    {
        $name = 'advfrm-' . $field->getName();
        if ($field->getRequired()) {
            $this->errors[] = sprintf($this->text['error_missing_field'], XH_hsc($field->getLabel()));
            if (empty($this->focusField)) {
                $this->focusField = [$form->getName(), $name];
            }
            return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    private function isMissing(Field $field)
    {
        $name = 'advfrm-' . $field->getName();
        switch ($field->getType()) {
            case 'file':
                return empty($_FILES[$name]['name']);
            case 'multi_select':
                return !isset($_POST[$name]) || (count($_POST[$name]) == 1 && empty($_POST[$name][0]));
            default:
                return !isset($_POST[$name]) || $_POST[$name] == '';
        }
    }

    /**
     * @return bool
     */
    private function checkMail(Form $form, Field $field)
    {
        $name = 'advfrm-' . $field->getName();
        if (!preg_match($this->conf['mail_regexp'], $_POST[$name])) {
            $this->errors[] = sprintf($this->text['error_invalid_email'], XH_hsc($field->getLabel()));
            if (empty($this->focusField)) {
                $this->focusField = [$form->getName(), $name];
            }
            return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    private function checkDate(Form $form, Field $field)
    {
        $name = 'advfrm-' . $field->getName();
        $pattern = '/^([0-9]+)-([0-9]+)-([0-9]+)$/';
        if (preg_match($pattern, $_POST[$name], $matches)) {
            $year = $matches[1];
            $month = $matches[2];
            $day = $matches[3];
            if (checkdate($month, $day, $year)) {
                return true;
            }
        }
        $this->errors[] = sprintf($this->text['error_invalid_date'], XH_hsc($field->getLabel()));
        if (empty($this->focusField)) {
            $this->focusField = [$form->getName(), $name];
        }
        return false;
    }

    /**
     * @return bool
     */
    private function checkNumber(Form $form, Field $field)
    {
        $name = 'advfrm-' . $field->getName();
        if (!ctype_digit($_POST[$name])) {
            $this->errors[] = sprintf($this->text['error_invalid_number'], XH_hsc($field->getLabel()));
            if (empty($this->focusField)) {
                $this->focusField = [$form->getName(), $name];
            }
            return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    private function checkFile(Form $form, Field $field)
    {
        $res = true;
        $name = 'advfrm-' . $field->getName();
        $props = explode("\xC2\xA6", $field->getProps());
        switch ($_FILES[$name]['error']) {
            case UPLOAD_ERR_OK:
                if (!empty($props[Plugin::PROP_MAXLEN])
                    && $_FILES[$name]['size'] > $props[Plugin::PROP_MAXLEN]
                ) {
                    $this->errors[] = sprintf($this->text['error_upload_too_large'], XH_hsc($field->getLabel()));
                    if (empty($this->focusField)) {
                        $this->focusField = [$form->getName(), $name];
                    }
                    $res = false;
                }
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $this->errors[] = sprintf($this->text['error_upload_too_large'], XH_hsc($field->getLabel()));
                if (empty($this->focusField)) {
                    $this->focusField = [$form->getName(), $name];
                }
                $res = false;
                break;
            default:
                $this->errors[] = sprintf($this->text['error_upload_general'], XH_hsc($field->getLabel()));
                if (empty($this->focusField)) {
                    $this->focusField = [$form->getName(), $name];
                }
                $res = false;
        }
        $ext = pathinfo($_FILES[$name]['name'], PATHINFO_EXTENSION);
        if (!$this->isFileTypeAllowed($ext, $props)) {
            $this->errors[] = sprintf($this->text['error_upload_illegal_ftype'], XH_hsc($field->getLabel()), XH_hsc($ext));
            if (empty($this->focusField)) {
                $this->focusField = [$form->getName(), $name];
            }
            $res = false;
        }
        return $res;
    }

    /**
     * @return bool
     */
    private function checkCustom(Form $form, Field $field)
    {
        $name = 'advfrm-' . $field->getName();
        $props = explode("\xC2\xA6", $field->getProps());
        $pattern = $props[Plugin::PROP_CONSTRAINT];
        if (!empty($pattern)
            && !preg_match($pattern, $_POST[$name])
        ) {
            $msg = empty($props[Plugin::PROP_ERROR_MSG])
                ? $this->text['error_invalid_custom']
                : $props[Plugin::PROP_ERROR_MSG];
            $this->errors[] = sprintf($msg, $field->getLabel());
            if (empty($this->focusField)) {
                $this->focusField = [$form->getName(), $name];
            }
            return false;
        }
        return true;
    }

    /**
     * @param string $extension
     * @param string[] $properties
     * @return bool
     */
    private function isFileTypeAllowed($extension, array $properties)
    {
        if (trim($properties[Plugin::PROP_FTYPES]) === '') {
            return false;
        }
        $types = explode(',', $properties[Plugin::PROP_FTYPES]);
        foreach ($types as $type) {
            if (!strcasecmp($extension, trim($type))) {
                return true;
            };
        }
        return false;
    }
}

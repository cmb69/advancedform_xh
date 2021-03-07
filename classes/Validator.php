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
     * @return bool|string
     */
    public function check(Form $form)
    {
        $o = '';
        foreach ($form->getFields() as $field) {
            $name = 'advfrm-' . $field->getName();
            if ($field->getRequired()) {
                $o .= $this->checkRequired($form, $field);
            } else {
                switch ($field->getType()) {
                    case 'from':
                    case 'mail':
                        $o .= $this->checkMail($form, $field);
                        break;
                    case 'date':
                        $o .= $this->checkDate($form, $field);
                        break;
                    case 'number':
                        $o .= $this->checkNumber($form, $field);
                        break;
                    case 'file':
                        $o .= $this->checkFile($form, $field);
                        break;
                    case 'custom':
                        $o .= $this->checkCustom($form, $field);
                }
                if (function_exists('advfrm_custom_valid_field')) {
                    $value = $field->getType() == 'file'
                        ? $_FILES[$name]
                        : $_POST[$name];
                    $valid = advfrm_custom_valid_field($form->getName(), $field->getName(), $value);
                    if ($valid !== true) {
                        $o .= '<li>' . $valid . '</li>' . PHP_EOL;
                        Plugin::focusField($form->getName(), $name);
                    }
                }
            }
        }
        if ($form->getCaptcha()) {
            if (!call_user_func($this->conf['captcha_plugin'] . '_captcha_check')) {
                $o .= '<li>' . $this->text['error_captcha_code'] . '</li>' . PHP_EOL;
                Plugin::focusField($form->getName(), 'advancedform-captcha');
            }
        }
        return $o == ''
            ? true
            : '<ul class="advfrm-error">' . PHP_EOL . $o . '</ul>' . PHP_EOL;
    }

    /**
     * @return string
     */
    private function checkRequired(Form $form, Field $field)
    {
        $name = 'advfrm-' . $field->getName();
        if ($this->isMissing($field)) {
            Plugin::focusField($form->getName(), $name);
            return '<li>'
                . sprintf($this->text['error_missing_field'], XH_hsc($field->getLabel()))
                . '</li>' . PHP_EOL;
        }
        return '';
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
     * @return string
     */
    private function checkMail(Form $form, Field $field)
    {
        $name = 'advfrm-' . $field->getName();
        if (!preg_match($this->conf['mail_regexp'], $_POST[$name])) {
            Plugin::focusField($form->getName(), $name);
            return '<li>'
                . sprintf($this->text['error_invalid_email'], XH_hsc($field->getLabel()))
                . '</li>' . PHP_EOL;
        }
        return '';
    }

    /**
     * @return string
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
                return '';
            }
        }
        Plugin::focusField($form->getName(), $name);
        return '<li>'
            . sprintf($this->text['error_invalid_date'], XH_hsc($field->getLabel()))
            .'</li>' . PHP_EOL;
    }

    /**
     * @return string
     */
    private function checkNumber(Form $form, Field $field)
    {
        $name = 'advfrm-' . $field->getName();
        if (!ctype_digit($_POST[$name])) {
            Plugin::focusField($form->getName(), $name);
            return '<li>'
                . sprintf($this->text['error_invalid_number'], XH_hsc($field->getLabel()))
                . '</li>' . PHP_EOL;
        }
        return '';
    }

    /**
     * @return string
     */
    private function checkFile(Form $form, Field $field)
    {
        $o = '';
        $name = 'advfrm-' . $field->getName();
        $props = explode("\xC2\xA6", $field->getProps());
        switch ($_FILES[$name]['error']) {
            case UPLOAD_ERR_OK:
                if (!empty($props[Plugin::PROP_MAXLEN])
                    && $_FILES[$name]['size'] > $props[Plugin::PROP_MAXLEN]
                ) {
                    $o .= '<li>'
                        . sprintf($this->text['error_upload_too_large'], XH_hsc($field->getLabel()))
                        . '</li>' . PHP_EOL;
                    Plugin::focusField($form->getName(), $name);
                }
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $o .= '<li>'
                    . sprintf($this->text['error_upload_too_large'], XH_hsc($field->getLabel()))
                    . '</li>' . PHP_EOL;
                Plugin::focusField($form->getName(), $name);
                break;
            default:
                $o .= '<li>'
                    . sprintf($this->text['error_upload_general'], XH_hsc($field->getLabel()))
                    . '</li>' . PHP_EOL;
                Plugin::focusField($form->getName(), $name);
        }
        $ext = pathinfo($_FILES[$name]['name'], PATHINFO_EXTENSION);
        if (!$this->isFileTypeAllowed($ext, $props)) {
            $o .= '<li>'
                . sprintf($this->text['error_upload_illegal_ftype'], XH_hsc($field->getLabel()), XH_hsc($ext))
                . '</li>' . PHP_EOL;
            Plugin::focusField($form->getName(), $name);
        }
        return $o;
    }

    /**
     * @return string
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
            Plugin::focusField($form->getName(), $name);
            return '<li>' . sprintf($msg, $field->getLabel()) . '</li>'
                . PHP_EOL;
        }
        return '';
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

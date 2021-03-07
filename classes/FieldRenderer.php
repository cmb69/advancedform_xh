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

class FieldRenderer
{
    /** @var string */
    private $formName;

    /**
     * @param string $formName
     */
    public function __construct($formName)
    {
        $this->formName = $formName;
    }

    /**
     * @return string
     */
    public function render(Field $field)
    {
        if ($field->isSelect()) {
            return $this->renderSelect($field);
        } else {
            return $this->renderNonSelect($field);
        }
    }

    /**
     * @return string
     */
    private function renderSelect(Field $field)
    {
        $o = '';
        $name = 'advfrm-' . $field->getName();
        $id = 'advfrm-' . $this->formName . '-' . $field->getName();
        $props = explode("\xC2\xA6", $field->getProps());
        $is_real_select = $field->isRealSelect();
        $is_multi = $field->isMulti();
        $brackets = $is_multi ? '[]' : '';
        if ($is_real_select) {
            $size = array_shift($props);
            $size = empty($size) ? '' : ' size="'.$size.'"';
            $multi = $is_multi ? ' multiple="multiple"' : '';
            $o .= '<select id="' . $id . '" name="' . $name . $brackets . '"'
                . $size . $multi . '>';
            $orient = null;
        } else {
            $orient = array_shift($props) ? 'vert' : 'horz';
        }
        foreach ($props as $opt) {
            $o .= $this->renderOption($field, $opt, $orient);
        }
        if ($is_real_select) {
            $o .= '</select>';
        }
        return $o;
    }

    /**
     * @param string $opt
     * @param string $orient
     * @return string
     */
    private function renderOption(Field $field, $opt, $orient)
    {
        $is_real_select = $field->isRealSelect();
        $name = 'advfrm-' . $field->getName();
        $opt = explode("\xE2\x97\x8F", $opt);
        if (count($opt) > 1) {
            $default = true;
            $opt = $opt[1];
        } else {
            $default = false;
            $opt = $opt[0];
        }
        $sel = $this->isChecked($field, $opt, $default)
            ? ($is_real_select ? ' selected="selected"' : ' checked="checked"')
            : '';
        if ($is_real_select) {
            return '<option' . $sel . '>' . XH_hsc($opt) . '</option>';
        } else {
            return '<div class="' . $orient . '"><label>'
                . '<input type="'.$field->getType() . '" name="' . $name
                . ($field->isMulti() ? '[]' : '') . '" value="' . XH_hsc($opt) . '"'
                . $sel . '>'
                . '&nbsp;' . XH_hsc($opt)
                . '</label></div>';
        }
    }

    /**
     * @param string $opt
     * @param bool $default
     * @return bool
     */
    private function isChecked(Field $field, $opt, $default)
    {
        $name = 'advfrm-' . $field->getName();
        if (function_exists('advfrm_custom_field_default')) {
            $cust_f = advfrm_custom_field_default($this->formName, $field->getName(), $opt, isset($_POST['advfrm']));
        }
        if (isset($cust_f)) {
            $f = $cust_f;
        } else {
            $f = isset($_POST['advfrm']) && isset($_POST[$name])
                && ($field->isMulti()
                    ? in_array($opt, $_POST[$name])
                    : $_POST[$name] == $opt)
                || !isset($_POST['advfrm']) && $default;
        }
        return $f;
    }

    /**
     * @return string
     */
    private function renderNonSelect(Field $field)
    {
        $name = 'advfrm-' . $field->getName();
        $id = 'advfrm-' . $this->formName . '-' . $field->getName();
        $props = explode("\xC2\xA6", $field->getProps());
        $type = in_array($field->getType(), array('file', 'password', 'hidden', 'date'))
            ? $field->getType()
            : 'text';
        $val = $this->getNonSelectValue($field, $props[Plugin::PROP_DEFAULT]);
        if ($field->getType() == 'textarea') {
            $cols = empty($props[Plugin::PROP_COLS]) ? 40 : $props[Plugin::PROP_COLS];
            $rows = empty($props[Plugin::PROP_ROWS]) ? 4 : $props[Plugin::PROP_ROWS];
            return '<textarea id="' . $id . '" name="' . $name . '" cols="' . $cols
                . '" rows="' . $rows . '">'
                . XH_hsc($val) . '</textarea>';
        } elseif ($field->getType() == 'output') {
            return $val;
        } else {
            $o = '';
            if ($field->getType() == 'file' && !empty($props[Plugin::PROP_MAXLEN])) {
                $o .= '<input type="hidden" name="MAX_FILE_SIZE" value="'
                    . $props[Plugin::PROP_MAXLEN] . '">';
            }
            if ($field->getType() == 'file') {
                $value = '';
                $accept = ' accept="'
                    . XH_hsc($this->prefixFileExtensionList($val))
                    . '"';
            } else {
                $value = ' value="' . XH_hsc($val) . '"';
                $accept = '';
            }
            $o .= '<input type="' . $type . '" id="' . $id . '" name="' . $name
                . '"' . $value . $accept . $this->getSize($field, $props[Plugin::PROP_SIZE])
                . $this->getMaxLen($field, $props[Plugin::PROP_MAXLEN])
                . $this->getPlaceholder($field)
                . '>';
            return $o;
        }
    }

    /**
     * @param string $default
     * @return string
     */
    private function getNonSelectValue(Field $field, $default)
    {
        $name = 'advfrm-' . $field->getName();
        if (function_exists('advfrm_custom_field_default')) {
            $val = advfrm_custom_field_default($this->formName, $field->getName(), null, isset($_POST['advfrm']));
        }
        if (!isset($val)) {
            $val =  isset($_POST[$name])
                ? $_POST[$name]
                : $default;
        }
        return $val;
    }

    /**
     * @param string $default
     * @return string
     */
    private function getSize(Field $field, $default)
    {
        return $field->getType() == 'hidden' || empty($default)
            ? ''
            : ' size="' . $default . '"';
    }

    /**
     * @param string $default
     * @return string
     */
    private function getMaxLen(Field $field, $default)
    {
        return in_array($field->getType(), array('hidden', 'file')) || empty($default)
            ? ''
            : ' maxlength="' . $default . '"';
    }

    /**
     * @return string
     */
    private function getPlaceholder(Field $field)
    {
        return $field->getType() == 'date' ? ' placeholder="2019-03-24"' : '';
    }

    /**
     * Prefixes each element of a comma separated list of file extensions with a dot.
     *
     * @param string $list A comma separated list of file extensions.
     *
     * @return string
     */
    private function prefixFileExtensionList($list)
    {
        $extensions = explode(',', $list);
        $func = function ($x) {
            return '.' . trim($x);
        };
        $extensions = array_map($func, $extensions);
        $list = implode(',', $extensions);
        return $list;
    }
}

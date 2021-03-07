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

class Plugin
{
    /**
     * Emits a SCRIPT element to set the focus to the field with name $name.
     *
     * @param string $form_id A form ID.
     * @param string $name    A field name.
     *
     * @return void
     */
    public static function focusField($form_id, $name)
    {
        global $hjs;
        static $done = false;

        if ($done) {
            return;
        }
        $hjs .= <<<SCRIPT
<script>
document.addEventListener("DOMContentLoaded", function () {
    var element = document.querySelector('.advfrm-mailform form[name="$form_id"] *[name="$name"]');
    if (element) element.focus();
});
</script>

SCRIPT;
        $done = true;
    }

    /**
     * Returns an associative array of language texts required for JS.
     *
     * @return array
     */
    public static function getLangForJs()
    {
        global $plugin_tx;

        $res = [];
        foreach ($plugin_tx['advancedform'] as $key => $msg) {
            if (strncmp($key, 'cf_', strlen('cf_'))) {
                $res[$key] = $msg;
            }
        }
        return $res;
    }

    /**
     * Returns the content of the CSV file as array on success, false otherwise.
     *
     * @param string $id A form ID.
     *
     * @return array
     */
    public static function readCsv($id)
    {
        global $e, $plugin_cf, $plugin_tx;

        $pcf = $plugin_cf['advancedform'];
        $forms = FormGateway::instance()->findAll();
        $fields = array();
        if (isset($forms[$id])) {
            foreach ($forms[$id]->getFields() as $field) {
                if ($field->getType() != 'output') {
                    $fields[] = $field->getName();
                }
            }
        } else {
            $e .= '<li>'
                . sprintf($plugin_tx['advancedform']['error_form_missing'], $id)
                . '</li>' . PHP_EOL;
            return false;
        }

        $fn = FormGateway::instance()->dataFolder() . $id . '.csv';
        if ($pcf['csv_separator'] == '') {
            if (($lines = file($fn)) === false) {
                e('cntopen', 'file', $fn);
                return array();
            }
            $data = array();
            foreach ($lines as $line) {
                $line = array_map('trim', explode("\t", $line));
                $rec = array_combine($fields, $line);
                $data[] = $rec;
            }
        } else {
            $sep = $pcf['csv_separator'];
            $data = array();
            if (($stream = fopen($fn, 'r')) !== false) {
                while (($rec = fgetcsv($stream, 0x10000, $sep)) !== false) {
                    $data[] = array_combine($fields, $rec);
                }
                fclose($stream);
            } else {
                e('cntopen', 'file', $fn);
            }
        }
        return $data;
    }

    /**
     * Returns the posted fields, as e.g. needed for advfrm_custom_thanks_page().
     *
     * @return array
     */
    public static function fields()
    {
        $fields = array();
        foreach ($_POST as $key => $val) {
            if (strpos($key, 'advfrm-') === 0) {
                $fields[substr($key, 7)] = is_array($val)
                    ? implode("\xC2\xA6", $val)
                    : $val;
            }
        }
        foreach ($_FILES as $key => $val) {
            if (strpos($key, 'advfrm-') === 0) {
                $fields[substr($key, 7)] = $val;
            }
        }
        return $fields;
    }
}

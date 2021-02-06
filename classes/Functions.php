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

class Functions
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

        if (defined('ADVFRM_FIELD_FOCUSED')) {
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
        define('ADVFRM_FIELD_FOCUSED', true);
    }

    /**
     * Returns the data folder path. Tries to create it, if necessary.
     *
     * @return string
     */
    public static function dataFolder()
    {
        global $pth, $plugin_cf;

        $pcf = $plugin_cf['advancedform'];

        if ($pcf['folder_data'] == '') {
            $fn = $pth['folder']['plugins'] . 'advancedform/data/';
        } else {
            $fn = $pth['folder']['base'] . $pcf['folder_data'];
        }
        if (substr($fn, -1) != '/') {
            $fn .= '/';
        }
        if (file_exists($fn)) {
            if (!is_dir($fn)) {
                e('cntopen', 'folder', $fn);
            }
        } else {
            if (mkdir($fn, 0777, true)) {
                chmod($fn, 0777);
            } else {
                e('cntwriteto', 'folder', $fn);
            }
        }
        return $fn;
    }

    /**
     * Returns the form database, if $forms is omitted.
     * Otherwise writes $forms as form database.
     *
     * @param array $forms A forms collection.
     *
     * @return mixed
     */
    public static function database($forms = null)
    {
        static $db;

        if (isset($forms)) { // write
            ksort($forms);
            $fn = self::dataFolder() . 'forms.json';
            $contents = json_encode($forms, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if (!XH_writeFile($fn, $contents)) {
                e('cntwriteto', 'file', $fn);
            }
            $db = $forms;
        } else {  // read
            if (!isset($db)) {
                $fn = self::dataFolder() . 'forms.json';
                if (file_exists($fn)) {
                    $contents = XH_readFile($fn);
                    $db = ($contents !== false) ? json_decode($contents, true) : array();
                } else {
                    $fn = self::dataFolder() . 'forms.dat';
                    $contents = XH_readFile($fn);
                    $db = ($contents !== false) ? unserialize($contents) : array();
                    self::database($db);
                }
                if (empty($db['%VERSION%'])) {
                    $db['%VERSION%'] = 0;
                }
                if ($db['%VERSION%'] < ADVFRM_DB_VERSION) {
                    $db = self::updatedDb($db);
                    self::database($db);
                }
                foreach ($db as &$form) {
                    if (is_array($form)) {
                        $form = Form::createFromArray($form);
                    }
                }
            }
            return $db;
        }
    }

    /**
     * Returns the forms database updated to the current version.
     *
     * @param array $forms A forms collection.
     *
     * @return array
     */
    public static function updatedDb($forms)
    {
        switch ($forms['%VERSION%']) {
            case 0:
            case 1:
                $forms = array_map(
                    function ($elt) {
                        if (is_object($elt)) {
                            $elt->setStore(false);
                        }
                        return $elt;
                    },
                    $forms
                );
        }
        $forms['%VERSION%'] = ADVFRM_DB_VERSION;
        return $forms;
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
        $forms = self::database();
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

        $fn = self::dataFolder() . $id . '.csv';
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
        return $fields;
    }
}

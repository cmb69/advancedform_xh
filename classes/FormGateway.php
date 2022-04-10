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

class FormGateway
{
    /** @var FormGateway|null */
    private static $instance = null;

    /**
     * @return FormGateway
     */
    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new FormGateway();
        }
        return self::$instance;
    }

    /** @var array<string,(int|Form)>|null */
    private $db = null;

    private function __construct()
    {
    }

    /**
     * Returns the data folder path. Tries to create it, if necessary.
     *
     * @return string
     */
    public function dataFolder()
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
     * @return array<string,(int|Form)>
     */
    public function findAll()
    {
        if (!isset($this->db)) {
            $fn = $this->dataFolder() . 'forms.json';
            if (file_exists($fn)) {
                $contents = XH_readFile($fn);
                $db = ($contents !== false) ? json_decode($contents, true) : array();
            } else {
                $fn = $this->dataFolder() . 'forms.dat';
                $contents = XH_readFile($fn);
                $db = ($contents !== false) ? unserialize($contents) : array();
            }
            $this->cleanDb($db);
            if (!array_key_exists('%VERSION%', $this->db)) {
                $this->db['%VERSION%'] = 0;
            }
            assert(is_int($this->db['%VERSION%']));
            if ($this->db['%VERSION%'] < Plugin::DB_VERSION) {
                $this->db = $this->updatedDb($this->db);
                $this->updateAll($this->db);
            }
        }
        return $this->db;
    }

    /**
     * @param array<string,mixed> $db
     * @return void
     */
    private function cleanDb($db)
    {
        $this->db = [];
        foreach ($db as $key => $form) {
            if ($key === '%VERSION%' && is_numeric($form)) {
                $this->db[$key] = (int) $form;
            } elseif (is_array($form)) {
                $this->db[$key] = Form::createFromArray($form);
            }
        }
    }

    /**
     * @param array<string,(int|Form)> $forms
     * @return void
     */
    public function updateAll($forms)
    {
        ksort($forms);
        $fn = $this->dataFolder() . 'forms.json';
        $contents = json_encode($forms, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (!XH_writeFile($fn, $contents)) {
            e('cntwriteto', 'file', $fn);
        }
        $this->db = $forms;
    }

    /**
     * Returns the forms database updated to the current version.
     *
     * @param array<string,(int|Form)> $forms
     * @return array<string,(int|Form)>
     */
    public function updatedDb($forms)
    {
        switch ($forms['%VERSION%']) {
            case 0:
            case 1:
                $forms = array_map(
                    function ($elt) {
                        if ($elt instanceof Form) {
                            $elt->setStore(false);
                        }
                        return $elt;
                    },
                    $forms
                );
        }
        $forms['%VERSION%'] = Plugin::DB_VERSION;
        return $forms;
    }
}

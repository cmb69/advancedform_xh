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
    /** @var string */
    private $dataFolder;

    /** @var ?array<string,int|Form> */
    private $db = null;

    public function __construct(string $dataFolder)
    {
        $this->dataFolder = $dataFolder;
    }

    public function dataFolder(): string
    {
        if (!file_exists($this->dataFolder)) {
            if (mkdir($this->dataFolder, 0777, true)) {
                chmod($this->dataFolder, 0777);
            }
        }
        return $this->dataFolder;
    }

    /** @return array<string,int|Form> */
    public function findAll(): array
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

    /** @param array<string,mixed> $db */
    private function cleanDb(array $db): void
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

    /** @param array<string,int|Form> $forms */
    public function updateAll(array $forms): bool
    {
        ksort($forms);
        $fn = $this->dataFolder() . 'forms.json';
        $contents = json_encode($forms, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $ok = $this->writeFile($fn, $contents) === strlen($contents);
        $this->db = $forms;
        return $ok;
    }

    /** @return int|false */
    public function writeFile(string $filename, string $contents)
    {
        $stream = fopen($filename, "c+");
        if ($stream === false) {
            return false;
        }
        flock($stream, LOCK_EX);
        $res = fwrite($stream, $contents);
        flock($stream, LOCK_UN);
        fclose($stream);
        return $res;
    }

    /**
     * @param array<string,int|Form> $forms
     * @return array<string,int|Form>
     */
    public function updatedDb(array $forms): array
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

    /** @param list<string> $fields */
    public function appendCsv(string $formname, array $fields, string $separator): bool
    {
        foreach ($fields as &$field) {
            $field = str_replace("\0", "", $field);
        }
        $filename = $this->dataFolder() . $formname . ".csv";
        $stream = @fopen($filename, "a");
        if ($stream === false) {
            return false;
        }
        $written = fputcsv($stream, $fields, $separator, '"', "\0");
        fclose($stream);
        return $written !== false;
    }
}

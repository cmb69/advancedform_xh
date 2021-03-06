<?php

/**
 * Copyright 2021 Christoph M. Becker
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

use JsonSerializable;

class Form implements JsonSerializable
{
    public static function createFromArray(array $record)
    {
        $result = new self;
        $result->captcha = $record['captcha'];
        $result->name = $record['name'];
        $result->title = $record['title'];
        $result->toName = $record['to_name'];
        $result->to = $record['to'];
        $result->cc = $record['cc'];
        $result->bcc = $record['bcc'];
        $result->thanksPage = $record['thanks_page'];
        $result->store = $record['store'];
        $result->fields = [];
        foreach ($record['fields'] as $field) {
            $result->fields[] = Field::createFromArray($field);
        }
        return $result;
    }

    private $captcha;

    private $name;

    private $title;

    private $toName;

    private $to;

    private $cc;

    private $bcc;

    private $thanksPage;

    private $store;

    private $fields;

    public function getCaptcha()
    {
        return $this->captcha;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getToName()
    {
        return $this->toName;
    }

    public function getTo()
    {
        return $this->to;
    }

    public function getCc()
    {
        return $this->cc;
    }

    public function getBcc()
    {
        return $this->bcc;
    }

    public function getThanksPage()
    {
        return $this->thanksPage;
    }

    public function getStore()
    {
        return $this->store;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function setName($value)
    {
        $this->name = $value;
    }

    public function setStore($value)
    {
        $this->store = $value;
    }

    public function jsonSerialize()
    {
        return array(
            'captcha' => $this->captcha,
            'name' => $this->name,
            'title' => $this->title,
            'to_name' => $this->toName,
            'to' => $this->to,
            'cc' => $this->cc,
            'bcc' => $this->bcc,
            'thanks_page' => $this->thanksPage,
            'fields' => $this->fields,
            'store' => $this->store
        );
    }
}

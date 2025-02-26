<?php

/**
 * Copyright 2021-2022 Christoph M. Becker
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
use ReturnTypeWillChange;

class Form implements JsonSerializable
{
    /**
     * @param FormArray $record
     * @return self
     */
    public static function createFromArray(array $record)
    {
        $result = new self();
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

    /** @var bool */
    private $captcha;

    /** @var string */
    private $name;

    /** @var string */
    private $title;

    /** @var string */
    private $toName;

    /** @var string */
    private $to;

    /** @var string */
    private $cc;

    /** @var string */
    private $bcc;

    /** @var string */
    private $thanksPage;

    /** @var bool */
    private $store;

    /** @var Field[] */
    private $fields;

    /**
     * @return bool
     */
    public function getCaptcha()
    {
        return $this->captcha;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getToName()
    {
        return $this->toName;
    }

    /**
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @return string
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * @return string
     */
    public function getBcc()
    {
        return $this->bcc;
    }

    /**
     * @return string
     */
    public function getThanksPage()
    {
        return $this->thanksPage;
    }

    /**
     * @return bool
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * @return Field[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param string $value
     * @return void
     */
    public function setName($value)
    {
        $this->name = $value;
    }

    /**
     * @param bool $value
     * @return void
     */
    public function setStore($value)
    {
        $this->store = $value;
    }

    /**
     * @return array<string,(string|bool|Field[])>
     */
    #[ReturnTypeWillChange]
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

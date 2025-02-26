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

class Field implements JsonSerializable
{
    /**
     * @param FieldArray $record
     * @return self
     */
    public static function createFromArray(array $record)
    {
        $result = new self();
        $result->name = $record['field'];
        $result->label = $record['label'];
        $result->type = $record['type'];
        $result->props = $record['props'];
        $result->required = $record['required'];
        return $result;
    }

    /** @var string */
    private $name;

    /** @var string */
    private $label;

    /** @var string */
    private $type;

    /** @var string */
    private $props;

    /** @var bool */
    private $required;

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
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getProps()
    {
        return $this->props;
    }

    /**
     * @return bool
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * @return bool
     */
    public function isSelect()
    {
        return in_array($this->getType(), ['radio', 'checkbox', 'select', 'multi_select']);
    }

    /**
     * @return bool
     */
    public function isRealSelect()
    {
        return in_array($this->getType(), ['select', 'multi_select']);
    }

    /**
     * @return bool
     */
    public function isMulti()
    {
        return in_array($this->getType(), ['checkbox', 'multi_select']);
    }

    /**
     * @return array<string,(string|bool)>
     */
    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return array(
            'field' => $this->name,
            'label' => $this->label,
            'type' => $this->type,
            'props' => $this->props,
            'required' => $this->required
        );
    }
}

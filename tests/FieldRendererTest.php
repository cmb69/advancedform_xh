<?php

/**
 * Copyright 2022 Christoph M. Becker
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

use Advancedform\Infra\HooksWrapper;
use PHPUnit\Framework\TestCase;

class FieldRendererTest extends TestCase
{
    /**
     * @dataProvider fieldProvider
     */
    public function testRenderTextField(Field $field, string $expected): void
    {
        $hooksWrapper = $this->createStub(HooksWrapper::class);
        $renderer = new FieldRenderer("test", $hooksWrapper);
        $actual = $renderer->render($field);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array<string,array{Field,string}>
     */
    public function fieldProvider(): array
    {
        return [
            "text field" => [
                Field::createFromArray([
                    "field" => "field",
                    "label" => "label",
                    "type" => "text",
                    "required" => false,
                    "props" => "10¦20¦default value",
                ]),
                '<input type="text" id="advfrm-test-field" name="advfrm-field"'
                . ' value="default value" size="10" maxlength="20">',
            ],
            "date field" => [
                Field::createFromArray([
                    "field" => "field",
                    "label" => "label",
                    "type" => "date",
                    "required" => false,
                    "props" => "10¦20¦2022-04-10",
                ]),
                '<input type="date" id="advfrm-test-field" name="advfrm-field"'
                . ' value="2022-04-10" size="10" maxlength="20" placeholder="2019-03-24">',
            ],
            "textarea field" => [
                Field::createFromArray([
                    "field" => "field",
                    "label" => "label",
                    "type" => "textarea",
                    "required" => false,
                    "props" => "10¦20¦default value",
                ]),
                '<textarea id="advfrm-test-field" name="advfrm-field"'
                . ' cols="10" rows="20">default value</textarea>',
            ],
            "checkbox field" => [
                Field::createFromArray([
                    "field" => "field",
                    "label" => "label",
                    "type" => "checkbox",
                    "required" => false,
                    "props" => "vert¦one¦two¦three",
                ]),
                '<div class="vert"><label><input type="checkbox" name="advfrm-field[]"'
                . ' value="one">&nbsp;one</label></div>'
                . '<div class="vert"><label><input type="checkbox" name="advfrm-field[]"'
                . ' value="two">&nbsp;two</label></div>'
                . '<div class="vert"><label><input type="checkbox" name="advfrm-field[]"'
                . ' value="three">&nbsp;three</label></div>',
            ],
            "select field" => [
                Field::createFromArray([
                    "field" => "field",
                    "label" => "label",
                    "type" => "select",
                    "required" => false,
                    "props" => "¦one¦●two¦three",
                ]),
                '<select id="advfrm-test-field" name="advfrm-field"><option>one</option>'
                . '<option selected="selected">two</option><option>three</option></select>' ,
            ],
            "file field" => [
                Field::createFromArray([
                    "field" => "field",
                    "label" => "label",
                    "type" => "file",
                    "required" => false,
                    "props" => "10¦20¦jpeg,png,gif",
                ]),
                '<input type="hidden" name="MAX_FILE_SIZE" value="20">'
                . '<input type="file" id="advfrm-test-field" name="advfrm-field"'
                . ' accept=".jpeg,.png,.gif" size="10">',
            ],
            "output field" => [
                Field::createFromArray([
                    "field" => "field",
                    "label" => "label",
                    "type" => "output",
                    "required" => false,
                    "props" => "10¦20¦<p>default value</p>",
                ]),
                '<p>default value</p>',
            ],
        ];
    }
}

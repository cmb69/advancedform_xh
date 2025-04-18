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

use Advancedform\Infra\CaptchaWrapper;
use Advancedform\Infra\HooksWrapper;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /** @var CaptchaWrapper&Stub */
    private $captchaWrapper;

    /** @var HooksWrapper&Stub */
    private $hooksWrapper;

    public function setUp(): void
    {
        $this->captchaWrapper = $this->createStub(CaptchaWrapper::class);
        $this->hooksWrapper = $this->createStub(HooksWrapper::class);
        $this->hooksWrapper->method("validField")->willReturn(true);
    }

    public function testMissingOptionalAttachment(): void
    {
        $_POST = [
            'advfrm' => "Test",
        ];
        $_FILES = [
            'advfrm-attachment' => [
                'name' => "",
                'type' => "",
                'tmp_name' => "",
                'error' => 4,
                'size' => 0,
            ],
        ];
        $subject = new Validator([], [], $this->captchaWrapper, $this->hooksWrapper);
        $form = $this->getTestForm([[
            'field' => "attachment",
            'label' => "Attachment",
            'type' => "file",
            'props' => "¦100000¦jpeg,jpg,png,zip¦¦",
            'required' => false,
        ]]);
        $this->assertTrue($subject->check($form));
    }

    public function testMissingOptionalDate(): void
    {
        $_POST = [
            'advfrm' => "Test",
            'advfrm-date' => "",
        ];
        $subject = new Validator([], [], $this->captchaWrapper, $this->hooksWrapper);
        $form = $this->getTestForm([[
            'field' => "date",
            'label' => "Date",
            'type' => "date",
            'props' => "¦¦¦",
            'required' => false,
        ]]);
        $this->assertTrue($subject->check($form));
    }

    public function testMissingOptionalMail(): void
    {
        $_POST = [
            'advfrm' => "Test",
            'advfrm-mail' => "",
        ];
        $subject = new Validator([], [], $this->captchaWrapper, $this->hooksWrapper);
        $form = $this->getTestForm([[
            "field" => "mail",
            "label" => "Mail",
            "type" => "from",
            "props" => "¦¦¦",
            "required" => false,
        ]]);
        $this->assertTrue($subject->check($form));
    }

    public function testWrongRequiredMail(): void
    {
        $_POST = [
            'advfrm' => "Test",
            'advfrm-mail' => "xxx",
        ];
        $conf = ['mail_regexp' => "/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i"];
        $text = ['error_invalid_email' => "error_invalid_email"];
        $subject = new Validator($conf, $text, $this->captchaWrapper, $this->hooksWrapper);
        $form = $this->getTestForm([[
            "field" => "mail",
            "label" => "Mail",
            "type" => "from",
            "props" => "¦¦¦",
            "required" => true,
        ]]);
        $this->assertFalse($subject->check($form));
        $this->assertCount(1, $subject->errors);
        $this->assertContains("error_invalid_email", $subject->errors);
    }

    public function testWrongMailAndMissingName(): void
    {
        $_POST = [
            'advfrm' => "Test",
            'advfrm-mail' => "xxx",
            'advfrm-name' => "",
        ];
        $conf = ['mail_regexp' => "/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i"];
        $text = [
            "error_invalid_email" => "error_invalid_email",
            "error_missing_field" => "error_missing_field",
        ];
        $subject = new Validator($conf, $text, $this->captchaWrapper, $this->hooksWrapper);
        $form = $this->getTestForm([[
            "field" => "mail",
            "label" => "Mail",
            "type" => "from",
            "props" => "¦¦¦",
            "required" => true,
        ], [
            "field" => "name",
            "label" => "Name",
            "type" => "text",
            "props" => "¦¦¦",
            "required" => true,
        ]]);
        $this->assertFalse($subject->check($form));
        $this->assertCount(2, $subject->errors);
        $this->assertContains("error_invalid_email", $subject->errors);
        $this->assertContains("error_missing_field", $subject->errors);
    }

    /**
     * @param array<FieldArray> $fields
     */
    private function getTestForm(array $fields): Form
    {
        return Form::createFromArray([
            'captcha' => false,
            'name' => "test",
            'title' => "Test Form",
            'to_name' => "Webmaster",
            'to' => "webmaster@example.com",
            'cc' => "",
            'bcc' => "",
            'thanks_page' => "",
            'store' => false,
            'fields' => $fields,
        ]);
    }
}

function XH_hsc(string $string): string
{
    return $string;
}

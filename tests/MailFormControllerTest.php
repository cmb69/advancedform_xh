<?php

namespace Advancedform;

use ApprovalTests\Approvals;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Plib\FakeRequest;
use Plib\View;

class MailFormControllerTest extends TestCase
{
    /** @var FormGateway */
    private $formGateway;

    /** @var FieldRenderer */
    private $fieldRenderer;

    /** @var Validator&Stub */
    private $validator;

    /** @var MailService&Stub */
    private $mailService;

    public function setUp(): void
    {
        vfsStream::setUp("root");
        copy("./data/forms.json", vfsStream::url("root/forms.json"));
        $this->formGateway = new FormGateway(vfsStream::url("root/"));
        $this->fieldRenderer = new FieldRenderer("Memberpage");
        $this->validator = $this->createStub(Validator::class);
        $this->mailService = $this->createStub(MailService::class);
    }

    private function sut(): MailFormController
    {
        return new MailFormController(
            $this->formGateway,
            $this->fieldRenderer,
            $this->validator,
            "./plugins/advancedform/",
            XH_includeVar("./config/config.php", "plugin_cf")["advancedform"],
            XH_includeVar("./languages/en.php", "plugin_tx")["advancedform"],
            $this->mailService,
            new View("./templates/", XH_includeVar("./languages/en.php", "plugin_tx")["advancedform"])
        );
    }

    public function testRendersMailForm(): void
    {
        Approvals::verifyHtml($this->sut()->main("Memberpage", new FakeRequest()));
    }

    public function testInvalidFormSubmissionRendersValidationErrors(): void
    {
        $_POST = ["advfrm" => "Memberpage"];
        $this->validator->method("check")->willReturn(false);
        $this->validator->method("focusField")->willReturn(["Memberpage", "E_Mail"]);
        $this->validator->method("errors")->willReturn(["Field 'E-Mail' doesn't contain a valid e-mail address!"]);
        $this->assertStringContainsString(
            "Field 'E-Mail' doesn't contain a valid e-mail address!",
            $this->sut()->main("Memberpage", new FakeRequest())
        );
    }
}

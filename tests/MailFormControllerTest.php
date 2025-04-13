<?php

namespace Advancedform;

use Advancedform\Infra\Logger;
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

    /** @var Logger&Stub */
    private $logger;

    public function setUp(): void
    {
        vfsStream::setUp("root");
        copy("./data/forms.json", vfsStream::url("root/forms.json"));
        $this->formGateway = new FormGateway(vfsStream::url("root/"));
        $this->fieldRenderer = new FieldRenderer("Memberpage");
        $this->validator = $this->createStub(Validator::class);
        $this->mailService = $this->createStub(MailService::class);
        $this->logger = $this->createStub(Logger::class);
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
            $this->logger,
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

    public function testFailureToSendMailIsReported(): void
    {
        global $e;
        $_POST = [
            "advfrm" => "Memberpage",
            "advfrm-E_Mail" => "john@example.com",
        ];
        $this->validator->method("check")->willReturn(true);
        $this->mailService->method("sendMail")->willReturn(false);
        $this->sut()->main("Memberpage", new FakeRequest());
        $this->assertSame("<li>The e-mail could not be sent!</li>\n", $e);
    }
}

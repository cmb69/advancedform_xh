<?php

namespace Advancedform;

use Advancedform\Infra\CaptchaWrapper;
use Advancedform\Infra\HooksWrapper;
use Advancedform\Infra\Logger;
use Advancedform\PHPMailer\PHPMailer;
use ApprovalTests\Approvals;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
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

    /** @var Validator */
    private $validator;

    /** @var CaptchaWrapper&Stub */
    private $captchaWrapper;

    /** @var HooksWrapper&MockObject */
    private $hooksWrapper;

    /** @var PHPMailer&MockObject */
    private $mailer;

    /** @var MailService */
    private $mailService;

    /** @var Logger&Stub */
    private $logger;

    public function setUp(): void
    {
        vfsStream::setUp("root");
        mkdir(vfsStream::url("root/data"));
        copy("./data/forms.json", vfsStream::url("root/data/forms.json"));
        mkdir(vfsStream::url("root/css"));
        copy("./css/stylesheet.css", vfsStream::url("root/css/stylesheet.css"));
        $this->formGateway = new FormGateway(vfsStream::url("root/data/"));
        $this->hooksWrapper = $this->createMock(HooksWrapper::class);
        $this->hooksWrapper->method("validField")->willReturn(true);
        $this->hooksWrapper->method("mail")->willReturn(true);
        $this->fieldRenderer = new FieldRenderer("Contact", $this->hooksWrapper);
        $this->captchaWrapper = $this->createStub(CaptchaWrapper::class);
        $lang = XH_includeVar("./languages/en.php", "plugin_tx")["advancedform"];
        $this->validator = new Validator(
            XH_includeVar("./config/config.php", "plugin_cf")["advancedform"],
            $lang,
            $this->captchaWrapper,
            $this->hooksWrapper
        );
        $this->mailer = $this->getMockBuilder(PHPMailer::class)
            ->onlyMethods(["Send"])
            ->getMock();
        $this->mailService = new MailService(
            vfsStream::url("root/data/"),
            vfsStream::url("root/"),
            $lang,
            $this->mailer,
            $this->hooksWrapper
        );
        $this->logger = $this->createStub(Logger::class);
    }

    private function sut(?array $config = null): MailFormController
    {
        return new MailFormController(
            $this->formGateway,
            $this->fieldRenderer,
            $this->validator,
            $this->captchaWrapper,
            $this->hooksWrapper,
            $config ?? $this->config(),
            $this->mailService,
            $this->logger,
            new View("./templates/", XH_includeVar("./languages/en.php", "plugin_tx")["advancedform"])
        );
    }

    public function testRendersMailForm(): void
    {
        $this->captchaWrapper->method("include")->willReturn(true);
        Approvals::verifyHtml($this->sut()->main("Contact", new FakeRequest())->output());
    }

    public function testReportsMissingForm(): void
    {
        $response = $this->sut()->main("NoSuchForm", new FakeRequest());
        $this->assertStringContainsString("A form with the name 'NoSuchForm' does not exist!", $response->output());
    }

    public function testReportsMissingCaptcha(): void
    {
        $this->captchaWrapper->method("include")->willReturn(false);
        $response = $this->sut()->main("Contact", new FakeRequest());
        $this->assertStringContainsString("Could not load CAPTCHA!", $response->output());
    }

    public function testInvalidFormSubmissionRendersValidationErrors(): void
    {
        $_POST = [
            "advfrm" => "Contact",
            "advfrm-E_Mail" => "john",
        ];
        $this->captchaWrapper->method("include")->willReturn(true);
        $response = $this->sut()->main("Contact", new FakeRequest(["post" => ["advfrm" => "Contact"]]));
        $this->assertStringContainsString(
            "Field 'E-Mail' doesn't contain a valid e-mail address!",
            $response->output()
        );
    }

    public function testFailureToSendMailIsReported(): void
    {
        global $e;
        $_SERVER["SERVER_NAME"] = "example.com";
        $_POST = $this->post();
        $e = "";
        $this->captchaWrapper->method("include")->willReturn(true);
        $this->captchaWrapper->method("check")->willReturn(true);
        $this->mailer->method("Send")->willReturn(false);
        $this->sut()->main("Contact", new FakeRequest(["post" => ["advfrm" => "Contact"]]));
        $this->assertSame("<li>The e-mail could not be sent!</li>\n", $e);
    }

    public function testSuccessfulSubmissionsSendsMail(): void
    {
        $_SERVER["SERVER_NAME"] = "example.com";
        $_POST = $this->post();
        $this->captchaWrapper->method("include")->willReturn(true);
        $this->captchaWrapper->method("check")->willReturn(true);
        $this->mailer->method("Send")->willReturn(true);
        $this->sut()->main("Contact", new FakeRequest(["post" => ["advfrm" => "Contact"]]));
        Approvals::verifyHtml($this->mailer->Body);
    }

    public function testShowsFormIfConfirmationMailCouldNotBeSent(): void
    {
        $_SERVER["SERVER_NAME"] = "example.com";
        $_POST = $this->post();
        $config = $this->config();
        $config["mail_confirmation"] = true;
        $this->captchaWrapper->method("include")->willReturn(true);
        $this->captchaWrapper->method("check")->willReturn(true);
        $this->mailer->method("Send")->willReturnOnConsecutiveCalls(true, false);
        $this->hooksWrapper->method("thanksPage")->willReturn("ThankYou");
        $response = $this->sut($config)->main("Contact", new FakeRequest(["post" => ["advfrm" => "Contact"]]));
        $this->assertStringContainsString("<div class=\"advfrm-mailform\">", $response->output());
    }

    public function testRedirectsToThanksPage(): void
    {
        $_SERVER["SERVER_NAME"] = "example.com";
        $_POST = $this->post();
        $config = $this->config();
        $config["mail_confirmation"] = true;
        $this->captchaWrapper->method("include")->willReturn(true);
        $this->captchaWrapper->method("check")->willReturn(true);
        $this->mailer->method("Send")->willReturn(true);
        $this->hooksWrapper->method("thanksPage")->willReturn("ThankYou");
        $response = $this->sut($config)->main("Contact", new FakeRequest(["post" => ["advfrm" => "Contact"]]));
        $this->assertSame("http://example.com/?ThankYou", $response->location());
    }

    public function testSuccessfulSubmissionsRendersMailInfo(): void
    {
        $_SERVER["SERVER_NAME"] = "example.com";
        $_POST = $this->post();
        $this->captchaWrapper->method("include")->willReturn(true);
        $this->captchaWrapper->method("check")->willReturn(true);
        $this->mailer->expects($this->once())->method("Send")->willReturn(true);
        $response = $this->sut()->main("Contact", new FakeRequest(["post" => ["advfrm" => "Contact"]]));
        Approvals::verifyHtml($response->output());
    }

    private function post(): array
    {
        return [
            "advfrm" => "Contact",
            "advfrm-Name" => "John Doe",
            "advfrm-E_Mail" => "john@example.com",
            "advfrm-Comment" => "a comment",
        ];
    }

    private function config(): array
    {
        return XH_includeVar("./config/config.php", "plugin_cf")["advancedform"];
    }
}

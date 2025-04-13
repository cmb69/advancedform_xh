<?php

namespace Advancedform;

use ApprovalTests\Approvals;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Plib\CsrfProtector;
use Plib\View;
use XH\Pages;

class MainAdminControllerTest extends TestCase
{
    /** @var FormGateway&Stub */
    private $formGateway;

    /** @var CsrfProtector&Stub */
    private $csrfProtector;

    /** @var Pages&Stub */
    private $pages;

    public function setUp(): void
    {
        global $tx;
        $tx = XH_includeVar("../../cmsimple/languages/en.php", "tx");
        $this->formGateway = $this->createStub(FormGateway::class);
        $this->formGateway->method("findAll")->willReturn(["Contact" => $this->form()]);
        $this->csrfProtector = $this->createStub(CsrfProtector::class);
        $this->csrfProtector->method("token")->willReturn("0123456789ABCDEF");
        $this->pages = $this->createStub(Pages::class);
        $this->pages->method("linkList")->willReturn([]);
    }

    private function sut(): MainAdminController
    {
        return new MainAdminController(
            $this->formGateway,
            "./",
            XH_includeVar("./config/config.php", "plugin_cf")["advancedform"],
            XH_includeVar("./languages/en.php", "plugin_tx")["advancedform"],
            $this->csrfProtector,
            $this->pages,
            new View("./templates/", XH_includeVar("./languages/en.php", "plugin_tx")["advancedform"])
        );
    }

    public function testRendersFormsOverview(): void
    {
        Approvals::verifyHtml($this->sut()->formsAdministrationAction());
    }

    public function testRendersFormEditor(): void
    {
        Approvals::verifyHtml($this->sut()->editFormAction("Contact"));
    }

    private function form(): Form
    {
        return Form::createFromArray([
            "captcha" => true,
            "name" => "Contact",
            "title" => "Contact",
            "to_name" => "Webmaster",
            "to" => "webmaster@example.com",
            "cc" => "",
            "bcc" => "",
            "thanks_page" => "",
            "store" => false,
            "fields" => [
                [
                    "field" => "Name",
                    "label" => "Name",
                    "type" => "from_name",
                    "props" => "¦¦¦",
                    "required" => true
                ],
                [
                    "field" => "E_Mail",
                    "label" => "E-Mail",
                    "type" => "from",
                    "props" => "¦¦¦",
                    "required" => true
                ],
                [
                    "field" => "Phone",
                    "label" => "Phone",
                    "type" => "custom",
                    "props" => "¦¦¦/^[0-9\\/\\-\\+\\ \\(\\)]*$/¦Field '%s' doesn't contain a valid phone number!",
                    "required" => false
                ],
                [
                    "field" => "Comment",
                    "label" => "Comment",
                    "type" => "textarea",
                    "props" => "¦¦¦",
                    "required" => true
                ],
            ],
        ]);
    }
}

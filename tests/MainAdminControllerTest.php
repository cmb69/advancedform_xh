<?php

namespace Advancedform;

use ApprovalTests\Approvals;
use PHPUnit\Framework\TestCase;
use Plib\CsrfProtector;
use Plib\View;

class MainAdminControllerTest extends TestCase
{
    public function testRendersFormsOverview(): void
    {
        global $tx;
        $tx = XH_includeVar("../../cmsimple/languages/en.php", "tx");
        $formGateway = $this->createStub(FormGateway::class);
        $formGateway->method("findAll")->willReturn(["Contact" => $this->form()]);
        $csrfProtector = $this->createStub(CsrfProtector::class);
        $csrfProtector->method("token")->willReturn("0123456789ABCDEF");
        $sut = new MainAdminController(
            $formGateway,
            "./",
            XH_includeVar("./config/config.php", "plugin_cf")["advancedform"],
            XH_includeVar("./languages/en.php", "plugin_tx")["advancedform"],
            $csrfProtector,
            new View("./templates/", XH_includeVar("./languages/en.php", "plugin_tx")["advancedform"])
        );
        Approvals::verifyHtml($sut->formsAdministrationAction());
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

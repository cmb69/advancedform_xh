<?php

namespace Advancedform;

use ApprovalTests\Approvals;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Plib\CsrfProtector;
use Plib\FakeRequest;
use Plib\Random;
use Plib\View;
use XH\Pages;

class MainAdminControllerTest extends TestCase
{
    /** @var FormGateway */
    private $formGateway;

    /** @var CsrfProtector&Stub */
    private $csrfProtector;

    /** @var Pages&Stub */
    private $pages;

    /** @var Random&Stub */
    private $random;

    public function setUp(): void
    {
        global $tx;
        $tx = XH_includeVar("../../cmsimple/languages/en.php", "tx");
        $this->setUpFormGateway();
        $this->csrfProtector = $this->createStub(CsrfProtector::class);
        $this->csrfProtector->method("token")->willReturn("0123456789ABCDEF");
        $this->pages = $this->createStub(Pages::class);
        $this->pages->method("linkList")->willReturn([]);
        $this->random = $this->createStub(Random::class);
        $this->random->method("bytes")->willReturn("0123456789ABCDE");
    }

    private function setUpFormGateway(): void
    {
        vfsStream::setup("root");
        copy("./data/forms.json", vfsStream::url("root/forms.json"));
        $this->formGateway = new FormGateway(vfsStream::url("root/"));
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
            $this->random,
            new View("./templates/", XH_includeVar("./languages/en.php", "plugin_tx")["advancedform"])
        );
    }

    public function testRendersFormsOverview(): void
    {
        $response = $this->sut()(new FakeRequest());
        Approvals::verifyHtml($response->output());
    }

    public function testRendersFormEditor(): void
    {
        $request = new FakeRequest([
            "url" => "http://example.com/?advancedform&admin=plugin_main&action=edit&form=Contact",
        ]);
        $response = $this->sut()($request);
        Approvals::verifyHtml($response->output());
    }

    public function testCreatesNewForm(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_POST = ["advancedform_token" => "0123456789ABCDEF"];
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?advancedform&admin=plugin_main&action=new",
        ]);
        $response = $this->sut()($request);
        $this->assertArrayHasKey("60OJ4CPK6KR3EE1P85146H25", $this->formGateway->findAll());
        $this->assertSame(
            "http://example.com/?advancedform&admin=plugin_main&action=edit&form=60OJ4CPK6KR3EE1P85146H25",
            $response->location()
        );
    }

    public function testSavingIsCsrfProtected(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_POST = [
            "advancedform_token" => "0123456789ABCDEF",
        ];
        $this->csrfProtector->method("check")->willReturn(false);
        $request = new FakeRequest([
            "url" => "http://example.com/?advancedform&admin=plugin_main&action=save&form=Contact",
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("nope", $response->output());
    }

    public function testSavingReportsMissingForm(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_POST = [
            "advancedform_token" => "0123456789ABCDEF",
        ];
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?advancedform&admin=plugin_main&action=save&form=NoSuchForm",
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("A form with the name 'NoSuchForm' does not exist!", $response->output());
    }

    public function testSavingReportsExistingForm(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_POST = $this->formPost("Remko");
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?advancedform&admin=plugin_main&action=save&form=Contact",
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("A form with this name already exists!", $response->output());
    }

    public function testSavingReportsFailureToSave(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_POST = $this->formPost("Contact");
        $this->csrfProtector->method("check")->willReturn(true);
        vfsStream::setQuota(0);
        $request = new FakeRequest([
            "url" => "http://example.com/?advancedform&admin=plugin_main&action=save&form=Contact",
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("The forms database could not have been saved!", $response->output());
    }

    public function testSavesForm(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_POST = $this->formPost("Saved");
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?advancedform&admin=plugin_main&action=save&form=Contact",
        ]);
        $response = $this->sut()($request);
        $this->assertArrayHasKey("Saved", $this->formGateway->findAll());
        $this->assertSame(
            "http://example.com/?advancedform&admin=plugin_main&action=plugin_text",
            $response->location()
        );
    }

    public function testDeletingIsCsrfProtected(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_POST = [
            "advancedform_token" => "0123456789ABCDEF",
        ];
        $this->csrfProtector->method("check")->willReturn(false);
        $request = new FakeRequest([
            "url" => "http://example.com/?advancedform&admin=plugin_main&action=delete&form=Contact",
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("nope", $response->output());
    }

    public function testDeletingReportsMissingForm(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_POST = [
            "advancedform_token" => "0123456789ABCDEF",
        ];
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?advancedform&admin=plugin_main&action=delete&form=NoSuchForm",
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("A form with the name 'NoSuchForm' does not exist!", $response->output());
    }

    public function testDeletingReportsFailureToSave(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_POST = $this->formPost("Contact");
        $this->csrfProtector->method("check")->willReturn(true);
        vfsStream::setQuota(0);
        $request = new FakeRequest([
            "url" => "http://example.com/?advancedform&admin=plugin_main&action=delete&form=Contact",
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("The forms database could not have been saved!", $response->output());
    }

    public function testDeletesForm(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_POST = $this->formPost("Contact");
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?advancedform&admin=plugin_main&action=delete&form=Contact",
        ]);
        $response = $this->sut()($request);
        $this->assertArrayNotHasKey("Contact", $this->formGateway->findAll());
        $this->assertSame(
            "http://example.com/?advancedform&admin=plugin_main&action=plugin_text",
            $response->location()
        );
    }

    private function formPost(string $name): array
    {
        return [
            "advancedform_token" => "0123456789ABCDEF",
            "advfrm-name" => $name,
            "advfrm-title" => "",
            "advfrm-to_name" => "",
            "advfrm-to" => "",
            "advfrm-cc" => "",
            "advfrm-bcc" => "",
            "advfrm-thanks_page" => "",
            "advfrm-field" => [
                "",
            ],
            "advfrm-label" => [
                "",
            ],
            "advfrm-type" => [
                "",
            ],
            "advfrm-props" => [
                "",
            ],
            "advfrm-required" => [
                "",
            ],
        ];
    }
}

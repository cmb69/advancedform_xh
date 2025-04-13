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
        $contents = file_get_contents(vfsStream::url("root/forms.json"));
        $forms = json_decode($contents, true);
        $forms = ["Import" => $forms["Contact"], '%VERSION%' => Plugin::DB_VERSION];
        $contents = json_encode($forms, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        file_put_contents(vfsStream::url("root/Import.json"), $contents);
        mkdir(vfsStream::url("root/css"));
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

    public function testCopyingIsCsrfProtected(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_POST = [
            "advancedform_token" => "0123456789ABCDEF",
        ];
        $this->csrfProtector->method("check")->willReturn(false);
        $request = new FakeRequest([
            "url" => "http://example.com/?advancedform&admin=plugin_main&action=copy&form=Contact",
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("nope", $response->output());
    }

    public function testCopyingReportsMissingForm(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_POST = [
            "advancedform_token" => "0123456789ABCDEF",
        ];
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?advancedform&admin=plugin_main&action=copy&form=NoSuchForm",
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("A form with the name 'NoSuchForm' does not exist!", $response->output());
    }

    public function testCopyingReportsFailureToSave(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_POST = $this->formPost("Contact");
        $this->csrfProtector->method("check")->willReturn(true);
        vfsStream::setQuota(0);
        $request = new FakeRequest([
            "url" => "http://example.com/?advancedform&admin=plugin_main&action=copy&form=Contact",
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("The forms database could not have been saved!", $response->output());
    }

    public function testCopiesForm(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_POST = $this->formPost("Contact");
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?advancedform&admin=plugin_main&action=copy&form=Contact",
        ]);
        $response = $this->sut()($request);
        $this->assertArrayHasKey("60OJ4CPK6KR3EE1P85146H25", $this->formGateway->findAll());
        $this->assertSame(
            "http://example.com/?advancedform&admin=plugin_main&action=edit&form=60OJ4CPK6KR3EE1P85146H25",
            $response->location()
        );
    }

    public function testImportingIsCsrfProtected(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $this->csrfProtector->method("check")->willReturn(false);
        $request = new FakeRequest([
            "url" => "http://example.com/?advancedform&admin=plugin_main&action=import&form=Contact",
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("nope", $response->output());
    }

    public function testImportingReportsExistingForm(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?advancedform&admin=plugin_main&action=import&form=Contact",
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("A form with this name already exists!", $response->output());
    }

    public function testImportingReportsFailureToImport(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?advancedform&admin=plugin_main&action=import&form=New",
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("'vfs://root/New.json' could not have been imported!", $response->output());
    }

    public function testImportingReportsFailureToSave(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $this->csrfProtector->method("check")->willReturn(true);
        vfsStream::setQuota(0);
        $request = new FakeRequest([
            "url" => "http://example.com/?advancedform&admin=plugin_main&action=import&form=Import",
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("The forms database could not have been saved!", $response->output());
    }

    public function testImportsForm(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?advancedform&admin=plugin_main&action=import&form=Import",
        ]);
        $response = $this->sut()($request);
        $this->assertArrayHasKey("Import", $this->formGateway->findAll());
        $this->assertSame(
            "http://example.com/?advancedform&admin=plugin_main&action=plugin_text",
            $response->location()
        );
    }

    public function testExportingIsCsrfProtected(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $this->csrfProtector->method("check")->willReturn(false);
        $request = new FakeRequest([
            "url" => "http://example.com/?advancedform&admin=plugin_main&action=export&form=Contact",
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("nope", $response->output());
    }

    public function testExportingReportsMissingForm(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?advancedform&admin=plugin_main&action=export&form=NoSuchForm",
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("A form with the name 'NoSuchForm' does not exist!", $response->output());
    }

    public function testExportingReportsFailureToExport(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $this->csrfProtector->method("check")->willReturn(true);
        vfsStream::setQuota(0);
        $request = new FakeRequest([
            "url" => "http://example.com/?advancedform&admin=plugin_main&action=export&form=Contact",
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString(
            "'vfs://root/Contact.json' could not have been exported!",
            $response->output()
        );
    }

    public function testExportsForm(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?advancedform&admin=plugin_main&action=export&form=Contact",
        ]);
        $response = $this->sut()($request);
        $this->assertFileExists(vfsStream::url("root/Contact.json"));
        $this->assertSame(
            "http://example.com/?advancedform&admin=plugin_main&action=plugin_text",
            $response->location()
        );
    }

    public function testCreatingFormTemplateIsCsrfProtected(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $this->csrfProtector->method("check")->willReturn(false);
        $request = new FakeRequest([
            "url" => "http://example.com/?advancedform&admin=plugin_main&action=template&form=Contact",
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("nope", $response->output());
    }

    public function testCreatingFormTemplateReportsMissingForm(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?advancedform&admin=plugin_main&action=template&form=NoSuchForm",
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString("A form with the name 'NoSuchForm' does not exist!", $response->output());
    }

    public function testCreatingFormTemplateReportsFailureToSave(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $this->csrfProtector->method("check")->willReturn(true);
        vfsStream::setQuota(0);
        $request = new FakeRequest([
            "url" => "http://example.com/?advancedform&admin=plugin_main&action=template&form=Contact",
        ]);
        $response = $this->sut()($request);
        $this->assertStringContainsString(
            "'vfs://root/Contact.tpl' could not be saved!",
            $response->output()
        );
    }

    public function testCreatesFormTemplate(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?advancedform&admin=plugin_main&action=template&form=Contact",
        ]);
        $response = $this->sut()($request);
        Approvals::verifyHtml(file_get_contents(vfsStream::url("root/Contact.tpl")));
        $this->assertSame(
            "http://example.com/?advancedform&admin=plugin_main&action=plugin_text",
            $response->location()
        );
    }

    public function testCreatesFormTemplateCSS(): void
    {
        $_SERVER["REQUEST_METHOD"] = "POST";
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?advancedform&admin=plugin_main&action=template&form=Contact",
        ]);
        $response = $this->sut()($request);
        Approvals::verifyStringWithFileExtension(file_get_contents(vfsStream::url("root/css/Contact.css")), "css");
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

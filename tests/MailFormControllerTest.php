<?php

namespace Advancedform;

use ApprovalTests\Approvals;
use org\bovigo\vfs\vfsStream;
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

    /** @var MailService */
    private $mailService;

    public function setUp(): void
    {
        vfsStream::setUp("root");
        copy("./data/forms.json", vfsStream::url("root/forms.json"));
        $this->formGateway = new FormGateway(vfsStream::url("root/"));
        $this->fieldRenderer = new FieldRenderer("Memberpage");
        $this->validator = new Validator(
            XH_includeVar("./config/config.php", "plugin_cf")["advancedform"],
            XH_includeVar("./languages/en.php", "plugin_tx")["advancedform"],
        );
        $this->mailService = new MailService(
            "",
            "",
            XH_includeVar("./languages/en.php", "plugin_tx")["advancedform"]
        );
    }

    public function testRendersMailForm(): void
    {
        $sut = new MailFormController(
            $this->formGateway,
            $this->fieldRenderer,
            $this->validator,
            "./plugins/advancedform/",
            XH_includeVar("./config/config.php", "plugin_cf")["advancedform"],
            XH_includeVar("./languages/en.php", "plugin_tx")["advancedform"],
            $this->mailService,
            new View("./templates/", XH_includeVar("./languages/en.php", "plugin_tx")["advancedform"])
        );
        Approvals::verifyHtml($sut->main("Memberpage", new FakeRequest()));
    }
}

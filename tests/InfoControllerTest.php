<?php

namespace Advancedform;

use ApprovalTests\Approvals;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Plib\FakeSystemChecker;
use Plib\View;

class InfoControllerTest extends TestCase
{
    public function testRendersSystemCheck(): void
    {
        vfsStream::setup("root");
        $sut = new InfoController(
            new FormGateway(vfsStream::url("root/plugins/advancedform/data/")),
            "./",
            XH_includeVar("./config/config.php", "plugin_cf")["advancedform"],
            new FakeSystemChecker(),
            new View("./templates/", XH_includeVar("./languages/en.php", "plugin_tx")["advancedform"])
        );
        Approvals::verifyHtml($sut()->output());
    }
}

<?php

namespace Advancedform;

use ApprovalTests\Approvals;
use PHPUnit\Framework\TestCase;

class InfoControllerTest extends TestCase
{
    public function testRendersSystemCheck(): void
    {
        $formGateway = new FormGateway();
        $sut = new InfoController(
            $formGateway,
            "./",
            XH_includeVar("./config/config.php", "plugin_cf")["advancedform"],
            XH_includeVar("./languages/en.php", "plugin_tx")["advancedform"],
        );
        Approvals::verifyHtml($sut->infoAction());
    }
}

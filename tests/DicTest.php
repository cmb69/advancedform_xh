<?php

namespace Advancedform;

use PHPUnit\Framework\TestCase;
use XH\CSRFProtection;

class DicTest extends TestCase
{
    public function setUp(): void
    {
        global $pth, $plugin_cf, $plugin_tx, $_XH_csrfProtection;
        $pth = ["folder" => ["plugins" => ""]];
        $plugin_cf = ["advancedform" => ["folder_data" => ""]];
        $plugin_tx = ["advancedform" => []];
        $_XH_csrfProtection = new CSRFProtection("xh_csrf_token", true);
    }

    public function testMakesMailFormController(): void
    {
        $this->assertInstanceOf(MailFormController::class, Dic::mailFormController("contact"));
    }

    public function testMakesInfoController(): void
    {
        $this->assertInstanceof(InfoController::class, Dic::infoController());
    }

    public function testMakesMainAdminController(): void
    {
        $this->assertInstanceOf(MainAdminController::class, Dic::mainAdminController());
    }
}

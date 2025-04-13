<?php

namespace Advancedform;

use ApprovalTests\Approvals;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Plib\FakeRequest;
use Plib\Random;
use Plib\View;

class CaptchTest extends TestCase
{
    /** @var Random&Stub */
    private $random;

    public function setUp(): void
    {
        $this->random = $this->createStub(Random::class);
        $this->random->method("bytes")->willReturnMap([
            [3, "ABC"],
            [4, "ABCD"],
        ]);
    }

    private function sut(): Captcha
    {
        return new Captcha(
            "0123456789ABCDEF",
            $this->random,
            new View("./templates/", XH_includeVar("./languages/en.php", "plugin_tx")["advancedform"])
        );
    }

    public function testRendersCaptcha(): void
    {
        Approvals::verifyHtml($this->sut()->display(new FakeRequest()));
    }

    public function testFailsOnInvalidSubmission(): void
    {
        $this->assertFalse($this->sut()->check(new FakeRequest()));
    }

    public function testSucceedsOnValidSubmission(): void
    {
        $request = new FakeRequest([
            "post" => [
                "advancedform-captcha" => "37870",
                "advancedform-timestamp" => "1741617587",
                "advancedform-salt" => "41424344",
                "advancedform-hmac" => "956ea9311aa2d163e7774b6c8f221e30dcfdc2e556e1300a21d3e4954d3e16fc",
            ],
        ]);
        $this->assertTrue($this->sut()->check($request));
    }
}

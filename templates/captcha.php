<?php

use Plib\View;

if (!defined("CMSIMPLE_XH_VERSION")) {http_response_code(403); exit;}

/**
 * @var View $this
 * @var string $code
 * @var int $timestamp
 * @var string $salt
 * @var string $hmac
 */
?>

<div class="captcha">
  <span class="captcha-explanation"><?=$this->plain('captcha_explanation')?></span>
  <span class="captcha"><?=$this->esc($code)?></span>
  <input type="text" name="advancedform-captcha">
  <input type="hidden" name="advancedform-timestamp" value="<?=$timestamp?>">
  <input type="hidden" name="advancedform-salt" value="<?=$this->esc($salt)?>">
  <input type="hidden" name="advancedform-hmac" value="<?=$this->esc($hmac)?>">
</div>

<?php

use Plib\View;

if (!defined("CMSIMPLE_XH_VERSION")) {http_response_code(403); exit;}

/**
 * @var View $this
 * @var string $id
 * @var string $url
 * @var string $required_message
 * @var string $inner_view
 * @var string $captcha
 */
?>
<!-- advancedform/mail-form -->
<div class="advfrm-mailform">
  <form name="<?=$id?>" action="<?=$url?>" method="post" enctype="multipart/form-data" accept-charset="UTF-8">
    <input type="hidden" name="advfrm" value="<?=$id?>">
    <div class="required"><?=$required_message?></div>
    <?=$inner_view?>
    <?=$captcha?>
    <div class="buttons">
      <input type="submit" class="submit" value="<?=$this->plain('button_send')?>">
      &nbsp;
      <input type="reset" class="submit" value="<?=$this->plain('button_reset')?>">
    </div>
  </form>
</div>

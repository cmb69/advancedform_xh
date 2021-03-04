<?php
if (!isset($this)) {
    header('HTTP/1.0 404 Not Found');
    exit;
}
?>
<!-- advancedform/mail-form -->
<div class="advfrm-mailform">
  <form name="<?=$id?>" action="<?=$url?>" method="post" enctype="multipart/form-data" accept-charset="UTF-8">
    <input type="hidden" name="advfrm" value="<?=$id?>">
    <div class="required"><?=$required_message?></div>
    <?=$inner_view?>
    <?=$captcha?>
    <div class="buttons">
      <input type="submit" class="submit" value="<?=$tx['button_send']?>">
      &nbsp;
      <input type="reset" class="submit" value="<?=$tx['button_reset']?>">
    </div>
  </form>
</div>

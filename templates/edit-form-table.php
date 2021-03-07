<?php
if (!isset($this)) {
    header('HTTP/1.0 404 Not Found');
    exit;
}
?>
<!-- advancedform/edit-form-table -->
<table id="advfrm-form">
<tr>
    <td><label for="advfrm-name"><?=$text['label_name']?></label></td>
    <td><input type="text" id="advfrm-name" name="advfrm-name" value="<?=XH_hsc($form->getName())?>" size="40"></td>
  </tr>
  <tr>
    <td><label for="advfrm-title"><?=$text['label_title']?></label></td>
    <td><input type="text" id="advfrm-title" name="advfrm-title" value="<?=XH_hsc($form->getTitle())?>" size="40"></td>
  </tr>
  <tr>
    <td><label for="advfrm-to_name"><?=$text['label_to_name']?></label></td>
    <td><input type="text" id="advfrm-to_name" name="advfrm-to_name" value="<?=XH_hsc($form->getToName())?>" size="40"></td>
  </tr>
  <tr>
    <td><label for="advfrm-to"><?=$text['label_to']?></label></td>
    <td><input type="text" id="advfrm-to" name="advfrm-to" value="<?=XH_hsc($form->getTo())?>" size="40"></td>
  </tr>
  <tr>
    <td><label for="advfrm-cc"><?=$text['label_cc']?></label></td>
    <td><input type="text" id="advfrm-cc" name="advfrm-cc" value="<?=XH_hsc($form->getCc())?>" size="40"></td>
  </tr>
  <tr>
    <td><label for="advfrm-bcc"><?=$text['label_bcc']?></label></td>
    <td><input type="text" id="advfrm-bcc" name="advfrm-bcc" value="<?=XH_hsc($form->getBcc())?>" size="40"></td>
  </tr>
  <tr>
    <td><label for="advfrm-captcha"><?=$text['label_captcha']?></label></td>
    <td><input type="checkbox" id="advfrm-captcha" name="advfrm-captcha" <?=$captcha_checked?>></td>
  </tr>
  <tr>
    <td><label for="advfrm-store"><?=$text['label_store']?></label></td>
    <td><input type="checkbox" id="advfrm-store" name="advfrm-store" <?=$store_checked?>></td>
  </tr>
  <tr>
    <td><label for="advfrm-thanks_page"><?=$text['label_thanks_page']?></label></td>
    <td><?=$thanks_page_select?></td>
  </tr>
</table>

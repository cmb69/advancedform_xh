<?php
if (!isset($this)) {
    header('HTTP/1.0 404 Not Found');
    exit;
}
?>
<!-- advancedform/edit-form -->
<div id="advfrm-editor">
  <h1><?=$id?></h1>
  <form action="<?=$this->esc($action)?>" method="post" accept-charset="UTF-8">
    <table id="advfrm-form">
      <tr>
        <td><label for="advfrm-name"><?=$this->plain('label_name')?></label></td>
        <td><input type="text" id="advfrm-name" name="advfrm-name" value="<?=XH_hsc($form->getName())?>" size="40"></td>
      </tr>
      <tr>
        <td><label for="advfrm-title"><?=$this->plain('label_title')?></label></td>
        <td><input type="text" id="advfrm-title" name="advfrm-title" value="<?=XH_hsc($form->getTitle())?>" size="40"></td>
      </tr>
      <tr>
        <td><label for="advfrm-to_name"><?=$this->plain('label_to_name')?></label></td>
        <td><input type="text" id="advfrm-to_name" name="advfrm-to_name" value="<?=XH_hsc($form->getToName())?>" size="40"></td>
      </tr>
      <tr>
        <td><label for="advfrm-to"><?=$this->plain('label_to')?></label></td>
        <td><input type="text" id="advfrm-to" name="advfrm-to" value="<?=XH_hsc($form->getTo())?>" size="40"></td>
      </tr>
      <tr>
        <td><label for="advfrm-cc"><?=$this->plain('label_cc')?></label></td>
        <td><input type="text" id="advfrm-cc" name="advfrm-cc" value="<?=XH_hsc($form->getCc())?>" size="40"></td>
      </tr>
      <tr>
        <td><label for="advfrm-bcc"><?=$this->plain('label_bcc')?></label></td>
        <td><input type="text" id="advfrm-bcc" name="advfrm-bcc" value="<?=XH_hsc($form->getBcc())?>" size="40"></td>
      </tr>
      <tr>
        <td><label for="advfrm-captcha"><?=$this->plain('label_captcha')?></label></td>
        <td><input type="checkbox" id="advfrm-captcha" name="advfrm-captcha" <?=$captcha_checked?>></td>
      </tr>
      <tr>
        <td><label for="advfrm-store"><?=$this->plain('label_store')?></label></td>
        <td><input type="checkbox" id="advfrm-store" name="advfrm-store" <?=$store_checked?>></td>
      </tr>
      <tr>
        <td><label for="advfrm-thanks_page"><?=$this->plain('label_thanks_page')?></label></td>
        <td>
          <select id="<?=$thanks_page_select['name']?>" name="<?=$thanks_page_select['name']?>">
            <option value="" <?=$thanks_page_select['selected']?>><?=$this->plain('label_none')?></option>
<?php foreach ($thanks_page_select['pages'] as $page):?>
            <option value="<?=$page[1]?>" <?=$thanks_page_select['page_selected']($page)?>><?=$page[0]?></option>
<?php endforeach?>
          </select>
        </td>
      </tr>
    </table>
    <div class="toolbar">
<?php foreach ($tools as $tool):?>
      <button type="button" class="advfrm-<?=$tool?>"><?=$toolIcon($tool)?></button>
<?php endforeach?>
    </div>
    <table id="advfrm-fields">
      <thead>
        <tr>
          <th><?=$this->plain('label_field')?></th>
          <th><?=$this->plain('label_label')?></th>
          <th colspan="3"><?=$this->plain('label_type')?></th>
          <th><?=$this->plain('label_required')?></th>
        </tr>
      </thead>
<?php foreach ($fields as $field):?>
      <tr>
        <td>
          <input type="text" size="10" name="advfrm-field[]" value="<?=$field['name']?>" class="highlightable">
        </td>
        <td>
          <input type="text" size="10" name="advfrm-label[]" value="<?=$field['label']?>" class="highlightable">
        </td>
        <td>
          <select name="advfrm-type[]" class="highlightable">
<?php   foreach ($field_types as $type):?>
            <option value="<?=$type?>"<?=$field['selected']($type)?>><?=$field_typelabel($type)?></option>
<?php   endforeach?>
          </select>
        </td>
        <td>
          <input type="hidden" class="hidden" name="advfrm-props[]" value="<?=$field['properties']?>">
        </td>
        <td>
          <button type="button"><?=$toolIcon('props')?></button>
        </td>
        <td>
          <input type="checkbox"<?=$field['checked']?> onchange="this.nextElementSibling.value = this.checked ? 1 : 0">
          <input type="hidden" name="advfrm-required[]" value="<?=$field['required']?>">
        </td>
      </tr>
<?php endforeach?>
    </table>
    <input type="submit" class="submit" value="<?=$label_save?>" style="display:none">
    <input type="hidden" name="advancedform_token" value="<?=$csrf_token?>">
  </form>
</div>
<div id="advfrm-text-props" style="display:none">
  <table>
<?php foreach ($text_properties as $prop):?>
    <tr id="advfrm-text-props-<?=$prop?>">
      <td><?=$prop?></td>
      <td><input type="text" size="30"></td>
    </tr>
<?php endforeach?>
  </table>
</div>
<div id="advfrm-select-props" style="display:none">
  <p id="advfrm-select-props-size"><?=$this->plain('label_size')?>
    <input type="text">
  </p>
  <p id="advfrm-select-props-orient">
    <input type="radio" id="advrm-select-props-orient-horz" name="advrm-select-props-orient">
    <label for="advrm-select-props-orient-horz">
      &nbsp;<?=$this->plain('label_horizontal')?>
    </label>
    &nbsp;&nbsp;&nbsp;
    <input type="radio" id="advrm-select-props-orient-vert" name="advrm-select-props-orient">
    <label for="advrm-select-props-orient-vert">
      &nbsp;<?=$this->plain('label_vertical')?>
    </label>
  </p>
  <div class="toolbar">
<?php foreach ($property_tools as $tool):?>
    <button type="button" class="advfrm-prop-<?=$tool?>">
      <?=$toolIcon($tool)?>
    </button>
<?php endforeach?>
  </div>
  <table id="advfrm-prop-fields">
    <tr>
      <td>
        <input type="radio" name="advfrm-select-props-default">
      </td>
      <td>
        <input type="text" name="advfrm-select-props-opt" size="25" class="highlightable">
      </td>
    </tr>
  </table>
</div>

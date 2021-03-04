<?php
if (!isset($this)) {
    header('HTTP/1.0 404 Not Found');
    exit;
}
?>
<!-- advancedform/page-select -->
<select id="<?=$name?>" name="<?=$name?>">
  <option value="" <?=$selected?>><?=$tx['label_none']?></option>
<?foreach ($pages as $page):?>
  <option value="<?=$page[1]?>" <?=$page_selected($page)?>><?=$page[0]?></option>
<?endforeach?>
</select>

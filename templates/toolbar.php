<?php
if (!isset($this)) {
    header('HTTP/1.0 404 Not Found');
    exit;
}
?>
<!-- advancedform/toolbar -->
<div class="toolbar">
<?foreach ($tools as $tool):?>
  <button type="button" class="advfrm-<?=$tool?>">
    <?=$toolIcon($tool)?>
  </button>
<?endforeach?>
</div>

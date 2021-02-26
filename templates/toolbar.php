<?php
if (!isset($this)) {
    header('HTTP/1.0 404 Not Found');
    exit;
}
?>

<div class="toolbar">
<?php foreach ($tools as $tool):?>
    <button type="button" class="advfrm-<?=$tool?>">
        <?=$toolIcon($tool)?>
    </button>
<?php endforeach?>
</div>

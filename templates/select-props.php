<?php
if (!isset($this)) {
    header('HTTP/1.0 404 Not Found');
    exit;
}
?>

<div id="advfrm-select-props" style="display:none">
    <p id="advfrm-select-props-size"><?=$tx['label_size']?>
        <input type="text">
    </p>
    <p id="advfrm-select-props-orient">
        <input type="radio" id="advrm-select-props-orient-horz" name="advrm-select-props-orient">
        <label for="advrm-select-props-orient-horz">
            &nbsp;<?=$tx['label_horizontal']?>
        </label>
        &nbsp;&nbsp;&nbsp;
        <input type="radio" id="advrm-select-props-orient-vert" name="advrm-select-props-orient">
        <label for="advrm-select-props-orient-vert">
            &nbsp;<?=$tx['label_vertical']?>
        </label>
    </p>
    <div class="toolbar">
<?php foreach ($tools as $tool):?>
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

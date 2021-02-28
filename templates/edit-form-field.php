<?php
if (!isset($this)) {
    header('HTTP/1.0 404 Not Found');
    exit;
}
?>

<tr>
    <td>
        <input type="text" size="10" name="advfrm-field[]" value="<?=$name?>" class="highlightable">
    </td>
    <td>
        <input type="text" size="10" name="advfrm-label[]" value="<?=$label?>" class="highlightable">
    </td>
    <td>
        <select name="advfrm-type[]" onfocus="this.oldvalue = this.value" class="highlightable">
<?php foreach ($types as $type):?>
            <option value="<?=$type?>"<?=$selected($type)?>><?=$typelabel($type)?></option>
<?php endforeach?>
        </select>
    </td>
    <td>
        <input type="hidden" class="hidden" name="advfrm-props[]" value="<?=$properties?>">
    </td>
    <td>
        <button type="button"><?=$toolicon?></button>
    </td>
    <td>
        <input type="checkbox"<?=$checked?> onchange="this.nextElementSibling.value = this.checked ? 1 : 0">
        <input type="hidden" name="advfrm-required[]" value="<?=$required?>">
    </td>
</tr>

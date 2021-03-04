<?php
if (!isset($this)) {
    header('HTTP/1.0 404 Not Found');
    exit;
}
?>
<!-- advancedform/mail-form-default-view -->
<div style="overflow:auto">
  <table>
<?foreach ($fields as $field):?>
    <tr <?=$field['class']?>>
<?  if (!$field['hidden']):?>
      <td class="label">
<?    if ($field['labeled']):?>
        <label for="<?=$field['field_id']?>">
<?    endif?>
          <?=$field['label']?>
<?    if ($field['labeled']):?>
        </label>
<?    endif?>
      </td>
<?  else:?>
      <td></td>
<?  endif?>
      <td class="field"><?=$field['inner_view']?></td>
    </tr>
<?endforeach?>
  </table>
</div>

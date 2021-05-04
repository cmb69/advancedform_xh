<?php
if (!isset($this)) {
    header('HTTP/1.0 404 Not Found');
    exit;
}
?>
<!-- advancedform/mail-form-default-view -->
<div style="overflow:auto">
  <table>
<?php foreach ($fields as $field):?>
    <tr <?=$field['class']?>>
<?php   if (!$field['hidden']):?>
      <td class="label">
<?php     if ($field['labeled']):?>
        <label for="<?=$field['field_id']?>">
<?php     endif?>
          <?=$field['label']?>
<?php     if ($field['labeled']):?>
        </label>
<?php     endif?>
      </td>
<?php   else:?>
      <td></td>
<?php   endif?>
      <td class="field"><?=$field['inner_view']?></td>
    </tr>
<?php endforeach?>
  </table>
</div>

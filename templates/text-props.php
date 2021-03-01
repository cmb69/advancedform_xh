<?php
if (!isset($this)) {
    header('HTTP/1.0 404 Not Found');
    exit;
}
?>
<!-- advancedform/text-props -->
<div id="advfrm-text-props" style="display:none">
  <table>
<?php foreach ($properties as $prop):?>
    <tr id="advfrm-text-props-<?=$prop?>">
      <td><?=$prop?></td>
      <td><input type="text" size="30"></td>
    </tr>
<?php endforeach?>
  </table>
</div>

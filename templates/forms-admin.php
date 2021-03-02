<?php
if (!isset($this)) {
    header('HTTP/1.0 404 Not Found');
    exit;
}
?>
<!-- advancedform/forms-admin -->
<div id="advfrm-form-list">
  <h1><?=$title?></h1>
  <form class="<?=$add_form['class']?>" action="<?=$add_form['action']?>" method="post" <?=$add_form['onsubmit']?>>
    <button title="<?=$add_form['title']?>"><?=$add_form['icon']?></button>
    <span><?=$add_form['token_input']?></span>
  </form>
  <form class="<?=$import_form['class']?>" action="<?=$import_form['action']?>" method="post" <?=$import_form['onsubmit']?>>
    <button title="<?=$import_form['title']?>"><?=$import_form['icon']?></button>
    <span><?=$import_form['token_input']?></span>
  </form>
  <table>
<?foreach ($forms as $id => $form):?>
    <tr>
      <td class="tool">
        <form class="<?=$form['delete_form']['class']?>" action="<?=$form['delete_form']['action']?>" method="post" <?=$form['delete_form']['onsubmit']?>>
          <button title="<?=$form['delete_form']['title']?>"><?=$form['delete_form']['icon']?></button>
          <span><?=$form['delete_form']['token_input']?></span>
        </form>
      </td>
      <td class="tool">
        <form class="<?=$form['template_form']['class']?>" action="<?=$form['template_form']['action']?>" method="post" <?=$form['template_form']['onsubmit']?>>
          <button title="<?=$form['template_form']['title']?>"><?=$form['template_form']['icon']?></button>
          <span><?=$form['template_form']['token_input']?></span>
        </form>
      </td>
      <td class="tool">
        <form class="<?=$form['copy_form']['class']?>" action="<?=$form['copy_form']['action']?>" method="post" <?=$form['copy_form']['onsubmit']?>>
          <button title="<?=$form['copy_form']['title']?>"><?=$form['copy_form']['icon']?></button>
          <span><?=$form['copy_form']['token_input']?></span>
        </form>
      </td>
      <td class="tool">
        <form class="<?=$form['export_form']['class']?>" action="<?=$form['export_form']['action']?>" method="post" <?=$form['export_form']['onsubmit']?>>
          <button title="<?=$form['export_form']['title']?>"><?=$form['export_form']['icon']?></button>
          <span><?=$form['export_form']['token_input']?></span>
        </form>
      </td>
      <td class="name"><a href="<?=$form['edit_url']?>" title="<?=$edit_label?>"><?=$id?></a></td>
      <td class="script" title="<?=$code_label?>">
        <input type="text" readonly onclick="this.select()" value="{{{advancedform('<?=$id?>')}}}"></input>
      </td>
    </tr>
<?endforeach?>
  </table>
</div>

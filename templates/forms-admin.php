<?php

use Plib\View;

if (!defined("CMSIMPLE_XH_VERSION")) {http_response_code(403); exit;}

/**
 * @var View $this
 * @var string $title
 * @var array<string,mixed> $add_form
 * @var array<string,mixed> $import_form
 * @var string $edit_label
 * @var array<string,mixed> $forms
 * @var string $code_label
 */
?>
<!-- advancedform/forms-admin -->
<div id="advfrm-form-list">
  <h1><?=$title?></h1>
  <form class="<?=$add_form['class']?>" action="<?=$this->esc($add_form['action'])?>" method="post" <?=$add_form['onsubmit']?>>
    <button title="<?=$add_form['title']?>"><?=$add_form['icon']?></button>
    <input type="hidden" name="advancedform_token" value="<?=$add_form['token']?>">
  </form>
  <form class="<?=$import_form['class']?>" action="<?=$this->esc($import_form['action'])?>" method="post" <?=$import_form['onsubmit']?>>
    <button title="<?=$import_form['title']?>"><?=$import_form['icon']?></button>
    <input type="hidden" name="advancedform_token" value="<?=$import_form['token']?>">
  </form>
  <table>
<?php foreach ($forms as $id => $form):?>
    <tr>
      <td class="tool">
        <form class="<?=$form['delete_form']['class']?>" action="<?=$this->esc($form['delete_form']['action'])?>" method="post" <?=$form['delete_form']['onsubmit']?>>
          <button title="<?=$form['delete_form']['title']?>"><?=$form['delete_form']['icon']?></button>
          <input type="hidden" name="advancedform_token" value="<?=$form['delete_form']['token']?>">
        </form>
      </td>
      <td class="tool">
        <form class="<?=$form['template_form']['class']?>" action="<?=$this->esc($form['template_form']['action'])?>" method="post" <?=$form['template_form']['onsubmit']?>>
          <button title="<?=$form['template_form']['title']?>"><?=$form['template_form']['icon']?></button>
          <input type="hidden" name="advancedform_token" value="<?=$form['template_form']['token']?>">
        </form>
      </td>
      <td class="tool">
        <form class="<?=$form['copy_form']['class']?>" action="<?=$this->esc($form['copy_form']['action'])?>" method="post" <?=$form['copy_form']['onsubmit']?>>
          <button title="<?=$form['copy_form']['title']?>"><?=$form['copy_form']['icon']?></button>
          <input type="hidden" name="advancedform_token" value="<?=$form['copy_form']['token']?>">
        </form>
      </td>
      <td class="tool">
        <form class="<?=$form['export_form']['class']?>" action="<?=$this->esc($form['export_form']['action'])?>" method="post" <?=$form['export_form']['onsubmit']?>>
          <button title="<?=$form['export_form']['title']?>"><?=$form['export_form']['icon']?></button>
          <input type="hidden" name="advancedform_token" value="<?=$form['export_form']['token']?>">
        </form>
      </td>
      <td class="name"><a href="<?=$this->esc($form['edit_url'])?>" title="<?=$edit_label?>"><?=$id?></a></td>
      <td class="script" title="<?=$code_label?>">
        <input type="text" readonly onclick="this.select()" value="{{{advancedform('<?=$id?>')}}}"></input>
      </td>
    </tr>
<?php endforeach?>
  </table>
</div>

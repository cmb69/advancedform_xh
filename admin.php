<?php

/**
 * Back-end functionality of Advancedform_XH.
 * Copyright (c) 2005-2010 Jan Kanters
 * Copyright (c) 2011-2013 Christoph M. Becker (see license.txt)
 */


if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}


include_once $pth['folder']['plugins'].'advancedform/advfrm.php';


/**
 * Returns (x)html plugin version information.
 *
 * @return string
 */
function advfrm_version() {
    global $pth;

    return '<h1><a href="http://3-magi.net/?CMSimple_XH/Advancedform_XH">Advancedform_XH</a></h1>'."\n"
	    .tag('img src="'.$pth['folder']['plugins'].'advancedform/advancedform.png" width="128"'
	    .' height="128" alt="Plugin icon" class="advancedform_plugin_icon"')."\n"
	    .'<p>Version: '.ADVFRM_VERSION.'</p>'."\n"
	    .'<p>Copyright &copy; 2005-2010 <a href="http://www.jat-at-home.be/">Jan Kanters</a>'.tag('br')
	    .'Copyright &copy; 2011-2013 <a href="http://3-magi.net">Christoph M. Becker</a></p>'."\n"
	    .'<p>Advancedform_XH is powered by '
	    .'<a href="http://www.cmsimple-xh.com/wiki/doku.php/plugins:jquery4cmsimple" target="_blank">'
	    .'jQuery4CMSimple</a>'
	    .' and <a href="http://phpmailer.worxware.com/" target"_blank">PHPMailer</a>.</p>'."\n"
	    .'<p class="advancedform_license">This program is free software: you can redistribute it and/or modify'
	    .' it under the terms of the GNU General Public License as published by'
	    .' the Free Software Foundation, either version 3 of the License, or'
	    .' (at your option) any later version.</p>'."\n"
	    .'<p class="advancedform_license">This program is distributed in the hope that it will be useful,'
	    .' but WITHOUT ANY WARRANTY; without even the implied warranty of'
	    .' MERCHAN&shy;TABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the'
	    .' GNU General Public License for more details.</p>'."\n"
	    .'<p class="advancedform_license">You should have received a copy of the GNU General Public License'
	    .' along with this program.  If not, see'
	    .' <a href="http://www.gnu.org/licenses/">http://www.gnu.org/licenses/</a>.</p>'."\n";
}


/**
 * Returns requirements information.
 *
 * @return string
 */
function advancedform_system_check() { // RELEASE-TODO
    global $pth, $tx, $plugin_tx, $plugin_cf;

    define('ADVFRM_PHP_VERSION', '4.3.0');
    $ptx = $plugin_tx['advancedform'];
    $imgdir = $pth['folder']['plugins'].'advancedform/images/';
    $ok = tag('img src="'.$imgdir.'ok.png" alt="ok"');
    $warn = tag('img src="'.$imgdir.'warn.png" alt="warning"');
    $fail = tag('img src="'.$imgdir.'fail.png" alt="failure"');
    $htm = tag('hr').'<h4>'.$ptx['syscheck_title'].'</h4>'
	    .(version_compare(PHP_VERSION, ADVFRM_PHP_VERSION) >= 0 ? $ok : $fail)
	    .'&nbsp;&nbsp;'.sprintf($ptx['syscheck_phpversion'], ADVFRM_PHP_VERSION)
	    .tag('br').tag('br')."\n";
    foreach (array('ctype', 'date', 'mbstring', 'pcre', 'session') as $ext) {
	$htm .= (extension_loaded($ext) ? $ok : $fail)
		.'&nbsp;&nbsp;'.sprintf($ptx['syscheck_extension'], $ext).tag('br')."\n";
    }
    $htm .= tag('br').(strtoupper($tx['meta']['codepage']) == 'UTF-8' ? $ok : $warn)
	    .'&nbsp;&nbsp;'.$ptx['syscheck_encoding'].tag('br')."\n";
    $htm .= (!get_magic_quotes_runtime() ? $ok : $warn)
	    .'&nbsp;&nbsp;'.$ptx['syscheck_magic_quotes'].tag('br')."\n";
    $htm .= (file_exists($pth['folder']['plugins'].'jquery/jquery.inc.php') ? $ok : $fail)
	    .'&nbsp;&nbsp;'.$ptx['syscheck_jquery'].tag('br')."\n";
    $htm .= (file_exists($pth['folder']['plugins'].$plugin_cf['advancedform']['captcha_plugin'].'/captcha.php') ? $ok : $warn)
	    .'&nbsp;&nbsp;'.$ptx['syscheck_captcha_plugin'].tag('br').tag('br')."\n";
    foreach (array('config/', 'css/', 'languages/') as $folder) {
	$folders[] = $pth['folder']['plugins'].'advancedform/'.$folder;
    }
    $folders[] = advfrm_data_folder();
    foreach ($folders as $folder) {
	$htm .= (is_writable($folder) ? $ok : $warn)
		.'&nbsp;&nbsp;'.sprintf($ptx['syscheck_writable'], $folder).tag('br')."\n";
    }
    return $htm;
}


/**
 * Returns <img> element for the tool $name.
 *
 * @param  string $name  The tool's name
 * @return string
 */
function advfrm_tool_icon($name) {
    global $pth, $plugin_tx;

    $ptx = $plugin_tx['advancedform'];

    return tag('img src="'.$pth['folder']['plugins'].'advancedform/images/'.$name.'.gif"'
	    .' alt="'.$ptx['tool_'.$name].'" title="'.$ptx['tool_'.$name].'"');
}


/**
 * Returns a selectbox with all CMSimple pages of the current language/subsite.
 *
 * @param string $name  The name and id of the select.
 * @param string $selected  The url of the thanks page.
 * @return string
 */
function advfrm_page_select($name, $selected) {
    global $cl, $h, $u, $l, $sn;

    $htm = '<select id="'.$name.'" name="'.$name.'">'."\n";
    $sel = $selected == '' ? ' selected="selected"' : '';
    $htm .= '<option value=""'.$sel.'></option>'."\n";
    for ($i = 0; $i < $cl; $i++) {
	$sel = $u[$i] == $selected ? ' selected="selected"' : '';
	$htm .= '<option value="'.$u[$i].'"'.$sel.'>'.str_repeat('&nbsp;&nbsp;', $l[$i]-1).$h[$i].'</option>'."\n";
    }
    $htm .= '</select>'."\n";
    return $htm;
}


/**
 * Returns (x)html of the mail forms administration.
 *
 * @return string
 */
function advfrm_admin_default() {
    global $sn, $tx, $plugin_tx;

    $ptx = $plugin_tx['advancedform'];

    $forms = advfrm_db();
    $htm = '<div id="advfrm-form-list">'."\n"
	    .'<h1>'.$ptx['menu_main'].'</h1>'."\n";
    $htm .= '<a href="'.$sn.'?advancedform&amp;admin=plugin_main&amp;action=new">'.advfrm_tool_icon('add').'</a>';
    $htm .= '<a href="javascript:advfrm_import(\''.$sn.'?advancedform&amp;admin=plugin_main&amp;action=import&amp;form=\')">'
	    .advfrm_tool_icon('import').'</a>';
    $htm .= '<table>'."\n";
    foreach ($forms as $id => $form) {
	if ($id != '%VERSION%') {
	    $htm .= '<tr>'
		    .'<td class="tool"><a href="'.$sn.'?advancedform&amp;admin=plugin_main&amp;action=delete&amp;form='.$id.'"'
		    .' onclick="return confirm(\''.addslashes($ptx['message_confirm_delete']).'\')">'
		    .advfrm_tool_icon('delete').'</a></td>'
		    .'<td class="tool"><a href="'.$sn.'?advancedform&amp;admin=plugin_main&amp;action=template&amp;form='.$id.'"'
		    .' onclick="return confirm(\''.addslashes(sprintf($ptx['message_confirm_template'], $form['name'])).'\')">'
		    .advfrm_tool_icon('template').'</a></td>'
		    .'<td class="tool"><a href="'.$sn.'?advancedform&amp;admin=plugin_main&amp;action=copy&amp;form='.$id.'">'
		    .advfrm_tool_icon('copy').'</a></td>'
		    .'<td class="tool"><a href="'.$sn.'?advancedform&amp;admin=plugin_main&amp;action=export&amp;form='.$id.'"'
		    .' onclick="return confirm(\''.addslashes(sprintf($ptx['message_confirm_export'], $form['name'])).'\')">'
		    .advfrm_tool_icon('export').'</a></td>'
		    .'<td class="name"><a href="'.$sn.'?advancedform&amp;admin=plugin_main&amp;action=edit&amp;form='.$id.'" title="'
		    .ucfirst($tx['action']['edit']).'">'.$id.'</a></td>'
		    .'<td class="script" title="'.$ptx['message_script_code'].'">{{{PLUGIN:advancedform(\''.$id.'\');}}}</td>'
		    .'</tr>'."\n";
	}
    }
    $htm .= '</table>'."\n";
    $htm .= '</div>'."\n";
    return $htm;
}


/**
 * Creates new mail form and returns the form editor.
 *
 * @return string
 */
function advfrm_admin_new() {
    global $plugin_cf;

    $pcf = $plugin_cf['advancedform'];
    $forms = advfrm_db();
    $id = uniqid();
    $forms[$id] = array(
	'name' => '',
	'title' => '',
	'to_name' => $pcf['mail_to_name'],
	'to' => $pcf['mail_to'],
	'cc' => $pcf['mail_cc'],
	'bcc' => $pcf['mail_bcc'],
	'captcha' => (bool) $pcf['mail_captcha'],
	'store' => FALSE,
	'thanks_page' => $pcf['mail_thanks_page'],
	'fields' => array(
	    array(
		'field' => '',
		'label' => '',
		'type' => 'text',
		'props' => "\xC2\xA6\xC2\xA6\xC2\xA6",
		'required' => '0'
	    )
	)
    );
    advfrm_db($forms);
    return advfrm_admin_edit($id);
}


/**
 * Returns (x)html of the form editor.
 *
 * @param string $id  The forms $id.
 * @return string
 */
function advfrm_admin_edit($id) {
    global $pth, $sn, $plugin_cf, $plugin_tx, $tx, $e;

    $pcf = $plugin_cf['advancedform'];
    $ptx = $plugin_tx['advancedform'];

    $forms = advfrm_db();
    $form = $forms[$id];
    if (!isset($form)) {
	$e .= '<li><b>'.sprintf($plugin_tx['advancedform']['error_form_missing'], $id).'</b></li>';
	return advfrm_admin_default();
    }

    // general settings

    $htm = '<div id="advfrm-editor">'."\n".'<h1>'.$id.'</h1>'."\n";
    $htm .= '<form action="'.$sn.'?advancedform&amp;admin=plugin_main&amp;action=save&amp;form='.$id.'"'
	    .' method="post" accept-charset="UTF-8" onsubmit="return advfrm_checkForm()">'."\n";
    $htm .= '<table id="advfrm-form">'."\n";
    foreach (array('name', 'title', 'to_name', 'to', 'cc', 'bcc', 'captcha', 'store', 'thanks_page') as $det) {
	$name = 'advfrm-'.$det;
	$htm .= '<tr>'
		.'<td><label for="'.$name.'">'.$ptx['label_'.$det].'</label></td>';
	switch ($det) {
	    case 'captcha':
	    case 'store':
		$checked = $form[$det] ? ' checked="checked"' : '';
		$htm .= '<td>'.tag('input type="checkbox" id="'.$name.'" name="'.$name.'"'.$checked).'</td>';
		break;
	    case 'thanks_page':
		$htm .= '<td>'.advfrm_page_select($name, $form[$det]).'</td>';
		break;
	    default:
		$htm .= '<td>'.tag('input type="text" id="'.$name.'" name="'.$name.'"'
			.' value="'.htmlspecialchars($form[$det]).'" size="40"').'</td>';
	}
	$htm .= '</tr>'."\n";
    }
    $htm .= '</table>'."\n";

    // field settings

    $htm .= '<div class="toolbar">';
    foreach (array('add', 'delete', 'up', 'down') as $tool) {
	$htm .=  '<a onclick="advfrm_'.$tool.'(\'advfrm-fields\')">'
		.advfrm_tool_icon($tool).'</a>'."\n";
    }
    $htm .= '</div>'."\n";

    $htm .= '<table id="advfrm-fields">'."\n";
    $htm .= '<thead><tr>'
	    .'<th>'.$ptx['label_field'].'</th>'
	    .'<th>'.$ptx['label_label'].'</th>'
	    .'<th colspan="3">'.$ptx['label_type'].'</th>'
	    .'<th>'.$ptx['label_required'].'</th>'
	    .'</tr></thead>'."\n";
   foreach ($form['fields'] as $num => $field) {
	$htm .= '<tr>'
		.'<td>'.tag('input type="text" size="10" name="advfrm-field[]"'
		.' value="'.$field['field'].'" class="highlightable"').'</td>'
		.'<td>'.tag('input type="text" size="10" name="advfrm-label[]"'
		.' value="'.htmlspecialchars($field['label']).'" class="highlightable"').'</td>'
		.'<td><select name="advfrm-type[]" onfocus="this.oldvalue = this.value" class="highlightable">';
	foreach (array('text', 'from_name', 'from', 'mail', 'date', 'number', 'textarea',
		'radio', 'checkbox', 'select', 'multi_select', 'password', 'file',
		'hidden', 'output', 'custom') as $type) {
	    $sel = $field['type'] == $type ? ' selected="selected"' : '';
	    $htm .= '<option value="'.$type.'"'.$sel.'>'.$ptx['field_'.$type].'</option>';
	}
	$htm .= '</select></td>'
		.'<td>'.tag('input type="'.(ADVFRM_DEBUG ? 'text' : 'hidden').'" class="hidden" name="advfrm-props[]"'
		.' size="10" value="'.htmlspecialchars($field['props']).'"')
		.'<td><a>'
		.advfrm_tool_icon('props')
		.'</a>'."\n";
	$checked = $field['required'] ? ' checked="checked"' : '';
	$htm .= '<td>'.tag('input type="checkbox"'.$checked
		.' onchange="this.parentNode.nextSibling.firstChild.value = this.checked ? 1 : 0"').'</td>';
	$htm .= '<td>'.tag('input type="hidden" name="advfrm-required[]" value="'.$field['required'].'"').'</td>'
		.'</tr>'."\n";
    }
    $htm .= '</table>'."\n";
    $htm .= tag('input type="submit" class="submit" value="'.ucfirst($tx['action']['save']).'" style="display:none"');
    $htm .= '</form>'."\n".'</div>'."\n";

    // property dialogs

    $htm .= '<div id="advfrm-text-props" style="display:none">'."\n".'<table>'."\n";
    foreach (array('size', 'maxlength', 'default', 'constraint', 'error_msg') as $prop) {
	$htm .= '<tr id="advfrm-text-props-'.$prop.'">'.'<td>'.$prop.'</td>'
		.'<td>'.tag('input type="text" size="30"').'</td>'.'</tr>'."\n";
    }
    $htm .= '</table>'."\n".'</div>'."\n";

    $htm .= '<div id="advfrm-select-props" style="display:none">'."\n";
    $htm .= '<p id="advfrm-select-props-size">'.$ptx['label_size'].' '.tag('input type="text"').'</p>'."\n";
    $htm .= '<p id="advfrm-select-props-orient">'
	    .tag('input type="radio" id="advrm-select-props-orient-horz" name="advrm-select-props-orient"')
	    .'<label for="advrm-select-props-orient-horz">&nbsp;'.$ptx['label_horizontal'].'</label>&nbsp;&nbsp;&nbsp;'
	    .tag('input type="radio" id="advrm-select-props-orient-vert" name="advrm-select-props-orient"')
	    .'<label for="advrm-select-props-orient-vert">&nbsp;'.$ptx['label_vertical'].'</label>'
	    .'</p>'."\n";
    $htm .= '<div class="toolbar">';
    foreach (array('add', 'delete', 'up', 'down', 'clear_defaults') as $tool) {
	$htm .=  '<a onclick="advfrm_'.$tool.'(\'advfrm-prop-fields\')">'
	    .advfrm_tool_icon($tool).'</a>'."\n";
    }
    $htm .= '</div>'."\n";
    $htm .= '<table id="advfrm-prop-fields">'."\n".'<tr>'
	    .'<td>'.tag('input type="radio" name="advfrm-select-props-default"').'</td>'
	    .'<td>'.tag('input type="text" name="advfrm-select-props-opt" size="25" class="highlightable"').'</td>'
	    .'</tr>'."\n".'</table>'."\n".'</div>'."\n";

    return $htm;
}


/**
 * Saves the modified mail form definition.
 * Returns the (x)html of the mail form list on success,
 * or the mail form editor on failure.
 *
 * @param  string $id  The form ID.
 * @return string
 */
function advfrm_admin_save($id) {
    global $e, $plugin_tx;

    $ptx = $plugin_tx['advancedform'];

    $forms = advfrm_db();
    if (!isset($forms[$id])) {
	$e .= '<li><b>'.sprintf($ptx['error_form_missing'], $id).'</b></li>';
	return advfrm_admin_default();
    }
    unset($forms[$id]);
    if (!isset($forms[$_POST['advfrm-name']])) {
	$id = $_POST['advfrm-name'];
	$ok = TRUE;
    } else {
	$_POST['advfrm-name'] = $id;
	$e .= '<li>'.$ptx['error_form_exists'].'</li>';
	$ok = FALSE;
    }
    $forms[$id]['captcha'] = FALSE;
    $forms[$id]['store'] = FALSE;
    foreach ($_POST as $key => $val) {
	$keys = explode('-', $key);
	if ($keys[0] == 'advfrm') {
	    if (!is_array($val)) {
		if (in_array($keys[1], array('captcha', 'store'))) {
		    $forms[$id][$keys[1]] = TRUE;
		} else {
		    $forms[$id][$keys[1]] = stsl($val);
		}
	    } else {
		foreach ($val as $num => $fieldval) {
		    $forms[$id]['fields'][$num][$keys[1]] = stsl($fieldval);
		}
	    }
	}
    }
    advfrm_db($forms);
    return $ok ? advfrm_admin_default() : advfrm_admin_edit($id);
}


/**
 * Deletes the form $id.
 * Returns the (x)html of mail form list.
 *
 * @param  string $id
 * @return string
 */
function advfrm_admin_delete($id) {
    global $e, $plugin_tx;

    $forms = advfrm_db();
    if (isset($forms[$id])) {
	unset($forms[$id]);
	advfrm_db($forms);
    } else {
    	$e .= '<li><b>'.sprintf($plugin_tx['advancedform']['error_form_missing'], $id).'</b></li>';
    }
    return advfrm_admin_default();
}


/**
 * Makes a copy of form $id.
 * Returns the (x)html of the mail form editor.
 *
 * @param  string $id  The soure form.
 * @return string
 */
function advfrm_admin_copy($id) {
    global $e, $plugin_tx;

    $forms = advfrm_db();
    if (isset($forms[$id])) {
	$form = $forms[$id];
	$form['name'] = '';
	$id = uniqid();
	$forms[$id] = $form;
	advfrm_db($forms);
    } else {
	$e .= '<li><b>'.sprintf($plugin_tx['advancedform']['error_form_missing'], $id).'</b></li>';
    }
    return advfrm_admin_edit($id);
}


/**
 * Imports the form definition from a *.frm file.
 *
 *
 * @return string
 */
function advfrm_admin_import($id) {
    global $plugin_tx, $e;

    $ptx = $plugin_tx['advancedform'];
    $forms = advfrm_db();
    if (!isset($forms[$id])) {
	$fn = advfrm_data_folder().$id.'.frm';
	if (($cnt = file_get_contents($fn)) !== FALSE
		&& ($form = unserialize($cnt)) !== FALSE
		&& isset($form['%VERSION%'])
		&& count($form) == 2) {
	    if ($form['%VERSION%'] < ADVFRM_DB_VERSION) {
		$form = advfrm_updated_db($form);
	    }
	    unset($form['%VERSION%']);
	    foreach ($form as $f) {
		$f['name'] = $id;
		$forms[$id] = $f;
	    }
	    advfrm_db($forms);
	} else {
	    e('cntopen', 'file', $fn);
	}
    } else {
	$e .= '<li><b>'.$ptx['error_form_exists'].'</b></li>';
    }
    return advfrm_admin_default();
}


/**
 * Exports the form definition to a *.frm file.
 * Returns the (x)html of the mail form administration.
 *
 * @param string $id
 * @return string
 */
function advfrm_admin_export($id) {
    global $e, $plugin_tx;

    $ptx = $plugin_tx['advancedform'];
    $forms = advfrm_db();
    if (isset($forms[$id])) {
	$form[$id] = $forms[$id];
	$form['%VERSION%'] = ADVFRM_DB_VERSION;
	$fn = advfrm_data_folder().$id.'.frm';
	if (!($fh = fopen($fn, 'w')) || fwrite($fh, serialize($form)) === FALSE) {
	    e('cntwriteto', 'file', $fn);
	}
	if ($fh)
	    fclose($fh);
    } else {
	$e .= '<li><b>'.sprintf($ptx['error_form_missing'], $id).'</b></li>';
    }
    return advfrm_admin_default();
}


/**
 * Creates a basic template of the form.
 * Returns the (x)html of the mail form administration.
 *
 * @param string $id  The form id.
 * @return string
 */
function advfrm_admin_template($id) {
    global $plugin_cf;

    $forms = advfrm_db();
    if (isset($forms[$id])) {
	$form = $forms[$id];
	$tpl = '<div id="advfrm-'.$id.'">'."\n";
	$css = '#advfrm-'.$id.' {}'."\n\n"
		.'#advfrm-'.$id.' div.break {clear: both}'."\n"
		.'#advfrm-'.$id.' div.float {float: left; margin-right: 1em}'."\n\n"
		.'#advfrm-'.$id.' div.label {/* float: left; width: 12em; margin-bottom: 0.5em; */}'."\n"
		.'#advfrm-'.$id.' div.field { margin-bottom: 0.5em; /* float: left;*/}'."\n\n"
		.'/* the individual fields */'."\n\n";
	$first = TRUE;
	foreach ($form['fields'] as $field) {
	    if ($first) {
		$tpl .= '  <?php advfrm_focus_field(\''.$id.'\', \'advfrm-'.$field['field'].'\')'
			.' // focus the first field?>'."\n";
		$first = FALSE;
	    }
	    $labelled = !in_array($field['type'], array('checkbox', 'radio', 'hidden')); //'output',
	    $label = in_array($field['type'], array('hidden')) ? '' //'output',
		    : (!$field['required'] ? $field['label']
			: sprintf($plugin_cf['advancedform']['required_field_mark'], $field['label']));
	    $tpl .= '  <div class="break">'."\n"
		    .'    <div class="label">'
		    .($labelled ? '<label for="advfrm-'.$id.'-'.$field['field'].'">' : '')
		    .$label
		    .($labelled ? '</label>' : '').'</div>'."\n"
		    .'    <div class="field"><?field '.$field['field'].'?></div>'."\n"
		    .'  </div>'."\n";
	    $css .= '#advfrm-'.$id.'-'.$field['field'].' {}'."\n";
	}
	$tpl .= '  <div class="break"></div>'."\n".'</div>'."\n";
	$fn = advfrm_data_folder().$id.'.tpl';
	if (!($fh = fopen($fn, 'w')) || fwrite($fh, $tpl) === FALSE) {
	    e('cntsave', 'file', $fn);
	}
	if ($fh)
	    fclose($fh);
	$fn = advfrm_data_folder().'css/'.$id.'.css';
	if (!($fh = fopen($fn, 'w')) || fwrite($fh, $css) === FALSE) {
	    e('cntsave', 'file', $fn);
	}
	if ($fh)
	    fclose($fh);
    } else {
	$e .= '<li><b>'.sprintf($ptx['error_form_missing'], $id).'</b></li>';
    }
    return advfrm_admin_default();
}


/**
 * Handle the plugin administration.
 */
if (!empty($advancedform)) {
    initvar('admin');
    initvar('action');

    if (include_once($pth['folder']['plugins'].'jquery/jquery.inc.php')) {
	include_jQuery();
	include_jQueryUI();
    }
    if (advfrm_update_lang_js()) {
	$hjs .= "\n".'<script type="text/javascript" src="'.$pth['folder']['plugins']
		.'advancedform/languages/'.$sl.'.js"></script>'."\n";
    }
    $hjs .= '<script type="text/javascript" src="'.$pth['folder']['plugins'].'advancedform/admin.js"></script>'."\n";

    $o .= print_plugin_admin('on');

    switch ($admin) {
	case '':
	    $o .= advfrm_version().advancedform_system_check();
	    break;
	case 'plugin_main':
	    switch ($action) {
		case 'new':
		    $o .= advfrm_admin_new();
		    break;
		case 'edit':
		    $o .= advfrm_admin_edit($_GET['form']);
		    break;
		case 'save':
		    $o .= advfrm_admin_save($_GET['form']);
		    break;
		case 'delete':
		    $o .= advfrm_admin_delete($_GET['form']);
		    break;
		case 'copy':
		    $o .= advfrm_admin_copy($_GET['form']);
		    break;
		case 'import':
		    $o .= advfrm_admin_import($_GET['form']);
		    break;
		case 'export':
		    $o .= advfrm_admin_export($_GET['form']);
		    break;
		case 'template':
		    $o .= advfrm_admin_template($_GET['form']);
		    break;
		default:
		    $o .= advfrm_admin_default();
	    }
	    break;
	default:
	    $o .= plugin_admin_common($admin, $action, $plugin);
    }
}

?>

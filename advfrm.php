<?php

/**
 * Front-end of Advancedform_XH.
 * Copyright (c) 2005-2010 Jan Kanters
 * Copyright (c) 2011-2012 Christoph M. Becker (see license.txt)
 */


if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}


define('ADVFRM_DB_VERSION', 2);


define('ADVFRM_PROP_SIZE', 0);
define('ADVFRM_PROP_COLS', 0);
define('ADVFRM_PROP_MAXLEN', 1);
define('ADVFRM_PROP_ROWS', 1);
define('ADVFRM_PROP_DEFAULT', 2);
define('ADVFRM_PROP_VALUE', 2);
define('ADVFRM_PROP_FTYPES', 2);
define('ADVFRM_PROP_CONSTRAINT', 3);
define('ADVFRM_PROP_ERROR_MSG', 4);


/* PHP4 compat */
if (!function_exists('array_combine')) {
    function array_combine($arr1, $arr2) {
	if (count($arr1) !== count($arr2)) {
	    return FALSE;
	}
	$res = array();
	$arr1 = array_values($arr1);
	$arr2 = array_values($arr2);
	foreach($arr1 as $key1 => $value1) {
	    $res[(string)$value1] = $arr2[$key1];
	}
	return $res;
    }
}


/**
 * Emits <script> to set the focus to the field with name $name.
 *
 * @param  string $form_id
 * @param  string $name
 * @return void
 */
function advfrm_focus_field($form_id, $name) {
    global $hjs;

    if (defined('ADVFRM_FIELD_FOCUSED')) {
	return;
    }
    advfrm_init_jquery();
    $hjs .= <<<SCRIPT
<script type="text/javascript">
/* <![CDATA[ */
jQuery(function() {jQuery('.advfrm-mailform form[name="$form_id"] *[name="$name"]').focus()})
/* ]]> */
</script>

SCRIPT;
    define('ADVFRM_FIELD_FOCUSED', TRUE);
}

/**
 * Includes jquery and initializes the datepicker,
 * if not already done.
 *
 * @return void
 */
function advfrm_init_jquery() {
    global $pth, $sl, $hjs, $cf, $plugin_cf, $plugin_tx;

    if (defined('ADVFRM_JQUERY_INITIALIZED')) {
	return;
    }
    $ptx = $plugin_tx['advancedform'];

    if (include_once($pth['folder']['plugins'].'jquery/jquery.inc.php')) {
	include_jQuery();
	include_jQueryUI();
    }
    $date_format = $ptx['date_order'][0].$ptx['date_order'][0].$ptx['date_delimiter']
	    .$ptx['date_order'][1].$ptx['date_order'][1].$ptx['date_delimiter']
	    .$ptx['date_order'][2].$ptx['date_order'][2];

    $lang = strlen($sl) == 2 ? $sl : $cf['language']['default'];
    $fn = $pth['folder']['plugins'].'advancedform/languages/jquery.ui.datepicker-'.$lang.'.js';
    if (file_exists($fn)) {
	$hjs .= '<script type="text/javascript" src="'.$fn.'"></script>'."\n";
    } else {
	if ($sl != 'en') {e('missing', 'language', $fn);}
    }
    $hjs .= <<<SCRIPT

<script type="text/javascript">
/* <![CDATA[ */
jQuery(function() {
    jQuery.datepicker.setDefaults(jQuery.datepicker.regional['$lang']);
    jQuery.datepicker.setDefaults({dateFormat: '$date_format'});
})
/* ]]> */
</script>

SCRIPT;
    define('ADVFRM_JQUERY_INITIALIZED', TRUE);
}


/**
 * Returns wether $field is a selection field (select, checkbox or radio).
 *
 * @param  array $field
 * @return bool
 */
function advfrm_is_select($field) {
    return in_array($field['type'], array('radio', 'checkbox', 'select', 'multi_select'));
}


/**
 * Returns wether $field is a select field.
 *
 * @param  array $field
 * @return bool
 */
function advfrm_is_real_select($field) {
    return in_array($field['type'], array('select', 'multi_select'));
}


/**
 * Returns wether $field is a multi selection field (select multiple or checkbox).
 *
 * @param  array $field
 * @return bool
 */
function advfrm_is_multi($field) {
    return in_array($field['type'], array('checkbox', 'multi_select'));
}


/**
 * Returns the data folder.
 *
 * @return string
 */
function advfrm_data_folder() {
    global $pth, $plugin_cf;

    $pcf = $plugin_cf['advancedform'];

    if ($pcf['folder_data'] == '') {
	$fn = $pth['folder']['plugins'].'advancedform/data/';
    } else {
	$fn = $pth['folder']['base'].$pcf['folder_data'];
    }
    if (substr($fn, -1) != '/') {
	$fn .= '/';
    }
    if (file_exists($fn)) {
	if (!is_dir($fn)) {
	    e('cntopen', 'folder', $fn);
	}
    } else {
	if (!mkdir($fn, 0777, TRUE)) {
	    e('cntwriteto', 'folder', $fn);
	}
    }
    return $fn;
}


/**
 * Returns the form database, if $forms is omitted.
 * Otherwise writes $forms as form database.
 *
 * @param array $forms
 * @return array/void
 */
function advfrm_db($forms = NULL) {
    static $db;

    if (isset($forms)) { // write
	ksort($forms);
	$fn = advfrm_data_folder().'forms.dat';
	if (!($fh = fopen($fn, 'w')) || fwrite($fh, serialize($forms)) === FALSE) {
	    e('cntwriteto', 'file', $fn);
	}
	if ($fh) {fclose($fh);}
	$db = $forms;
    } else {  // read
	if (!isset($db)) {
	    $fn = advfrm_data_folder().'forms.dat';
	    if (($cnt = file_get_contents($fn)) !== FALSE) {
		$res = unserialize($cnt);
	    } else {
		$res = FALSE;
	    }
	    $db = $res !== FALSE ? $res : array();
	    if (empty($db['%VERSION%'])) {
		$db['%VERSION%'] = 0;
	    }
	    if ($db['%VERSION%'] < ADVFRM_DB_VERSION) {
		$db = advfrm_updated_db($db);
		advfrm_db($db);
	    }
	}
	return $db;
    }
}


/**
 * Returns the forms db updated to the current version.
 *
 * @param array $forms
 * @return array
 */
function advfrm_updated_db($forms) {
    switch ($forms['%VERSION%']) {
	case 0:
	case 1:
	    $forms = array_map(create_function('$elt',
		    'if (is_array($elt)) {$elt["store"] = FALSE;} return $elt;'), $forms);
    }
    $forms['%VERSION%'] = ADVFRM_DB_VERSION;
    return $forms;
}


/**
 * Updates the LANG.js file if necessary
 * with the strings from LANG.php.
 * Returns FALSE on failure.
 *
 * @return bool
 */
function advfrm_update_lang_js() {
    global $pth, $sl, $plugin_tx;

    $ptx = $plugin_tx['advancedform'];

    $fn = $pth['folder']['plugins'].'advancedform/languages/'.$sl;
    if (!file_exists($fn.'.php')) {
	e('missing', 'language', $fn.'.php');
	return FALSE;
    }
    if (!file_exists($fn.'.js') || filemtime($fn.'.js') < filemtime($fn.'.php')) {
	$js = '// auto-generated by Advancedform_XH -- do not modify!'."\n"
		.'// any modifications should be made in '.$sl.'.php'."\n\n"
		.'ADVFRM_TX = {'."\n";
	$first = TRUE;
	foreach ($ptx as $key => $msg) {
	    $parts = explode('_', $key);
	    if ($parts[0] != 'cf') {
		if ($first) {
		    $first = FALSE;
		} else {
		    $js .= ','."\n";
		}
		$js .= '    \''.$key.'\': \''.addslashes($msg).'\'';
	    }
	}
	$js .= "\n".'};'."\n";
	if (!($fh = fopen($fn.'.js', 'w')) || ($res = fwrite($fh, $js)) === FALSE) {
	    e('cntwriteto', 'file', $fn.'.js');
	}
	if ($fh)
	    fclose($fh);
	return $fh && $res;
    }
    return TRUE;
}


/**
 * Returns the content of the csv file as array on success,
 * FALSE otherwise.
 *
 * @param string $id  Form ID
 * @return array
 */
function advfrm_read_csv($id) {
    global $e, $plugin_tx;

    $forms = advfrm_db();
    $fields = array();
    if (isset($forms[$id])) {
	foreach ($forms[$id]['fields'] as $field) {
	    if ($field['type'] != 'output') {
		$fields[] = $field['field'];
	    }
	}
    } else {
	$e .= '<li>'.sprintf($plugin_tx['advancedform']['error_form_missing'], $id).'</li>'."\n";
	return FALSE;
    }

    $fn = advfrm_data_folder().$id.'.csv';
    if (($lines = file($fn)) === FALSE) {
	e('cntopen', 'file', $fn);
	return array();
    }
    $data = array();
    foreach ($lines as $line) {
	$line = array_map('trim', explode("\t", $line));
	$rec = array_combine($fields, $line);
	$data[] = $rec;
    }
    return $data;
}

/**
 * Appends current record to csv file.
 *
 * @param string $id  Form ID
 * @return void
 */
function advfrm_append_csv($id) {
    $forms = advfrm_db();
    $fields = array();
    foreach ($forms[$id]['fields'] as $field) {
	if ($field['type'] != 'output') {
	    $name = $field['field'];
	    $val = $field['type'] == 'file' ? $_FILES['advfrm-'.$name]['name'] : $_POST['advfrm-'.$name];
	    $fields[] = is_array($val) ? implode("\xC2\xA6", array_map('stsl', $val)) : stsl($val);
	}
    }
    $fn = advfrm_data_folder().$id.'.csv';
    if (($fh = fopen($fn, 'a')) === FALSE
	    || fwrite($fh, implode("\t", $fields)."\n") === FALSE) {
	e('cntwriteto', 'file', $fn);
    }
    if ($fh !== FALSE) {fclose($fh);}
}

/**
 * Returns the the posted fields, as e.g. needed for advfrm_custom_thanks_page().
 *
 * @return array
 */
function advfrm_fields() {
    $fields = array();
    foreach ($_POST as $key => $val) {
	if (strpos($key, 'advfrm-') === 0) {
	    $fields[substr($key, 7)] = is_array($val)
		    ? implode("\xC2\xA6", array_map('stsl', $val))
		    : stsl($val);
	}
    }
    return $fields;
}


/**
 * Returns the information sent/to send.
 *
 * @param  string $id		Form ID
 * @param  bool   $show_hidden
 * @param  bool	  $html
 * @return string
 */
function advfrm_mail_info($id, $show_hidden, $html) {
    global $cf, $plugin_tx;

    $ptx = $plugin_tx['advancedform'];
    $forms = advfrm_db();
    $form = $forms[$id];
    $res = '';
    if ($html) {$res .= '<div class="advfrm-mailform">'."\n";}
    if (!$show_hidden) {
	$res .= $html
		? '<p>'.$ptx['message_sent_info'].'</p>'."\n"
		: strip_tags($ptx['message_sent_info'])."\n\n";
    }
    if ($html) {$res .= '<table>'."\n";}
    foreach ($form['fields'] as $field) {
	if (($field['type'] != 'hidden' || $show_hidden) && $field['type'] != 'output') {
	    $name = 'advfrm-'.$field['field'];
	    if ($html) {
		$res .= '<tr><td class="label">'.htmlspecialchars($field['label'])
			.'</td><td class="field">';
	    } else {
		$res .= $field['label']."\n";
	    }
	    if (isset($_POST[$name])) {
		if (is_array($_POST[$name])) {
		    foreach ($_POST[$name] as $val) {
			$res .= $html ? '<div>'.htmlspecialchars(stsl($val)).'</div>' : '  '.stsl($val)."\n";
		    }
		} else {
		    $res .= $html
			    ? nl2br(htmlspecialchars(stsl($_POST[$name])), $cf['xhtml']['endtags'] == 'true')
			    : '  '.stsl($_POST[$name])."\n";
		}
	    } elseif (isset($_FILES[$name])) {
		$res .= $html ? stsl($_FILES[$name]['name']) : '  '.stsl($_FILES[$name]['name'])."\n";
	    }
	    if ($html) {
		$res .= '</td></tr>'."\n";
	    }
	}
    }
    if ($html) {
	$res .= '</table>'."\n".'</div>'."\n";
    }
    return $res;
}


/**
 * Returns the top of the css file $fn,
 * i.e. everything above the comment line:
 * END OF MAIL CSS
 * If the file couldn't be read, returns an empty string.
 *
 * @param string $fn
 * @return string
 */
function advfrm_mail_css($fn) {
    if (($css = file_get_contents($fn)) !== FALSE) {
	$css = explode('/* END OF MAIL CSS */', $css);
	return $css[0];
    } else {
	return '';
    }
}


/**
 * Returns the body of the mail.
 *
 * @param  string $id		Form ID
 * @param  bool   $show_hidden
 * @param  bool	  $html
 * @return string
 */
function advfrm_mail_body($id, $show_hidden, $html) {
    global $cf, $pth;

    $forms = advfrm_db();
    $form = $forms[$id];
    $res = '';
    if ($html) {
	if ($cf['xhtml']['endtags'] == 'true') {
	    $res .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"'
		    .' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n"
		    .'<html xmlns="http://www.w3.org/1999/xhtml">'."\n";
	} else {
	    $res .= '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"'
		    .' "http://www.w3.org/TR/html4/loose.dtd">'."\n"
		    .'<html>'."\n";
	}
	$res .= '<head>'."\n".'<style type="text/css">'."\n";
	$res .= advfrm_mail_css($pth['folder']['plugins'].'advancedform/css/stylesheet.css');
	$fn = advfrm_data_folder().'css/'.$id.'.css';
	if (file_exists($fn)) {$res .= advfrm_mail_css($fn);}
	$res .= '</style>'."\n".'</head>'."\n".'<body>'."\n";
    }
    $res .= advfrm_mail_info($id, $show_hidden, $html);
    if ($html) {
	$res .= '</body>'."\n".'</html>'."\n";
    }
    return $res;
}


/**
 * Returns the display of the form field.
 *
 * @param  string $form_id
 * @param  string $field
 * @return string	    (x)html
 */
function advfrm_display_field($form_id, $field) {
    global $plugin_cf, $hjs;

    $pcf = $plugin_cf['advancedform'];

    $htm = '';
    $name = 'advfrm-'.$field['field'];
    $id = 'advfrm-'.$form_id.'-'.$field['field'];
    $props = explode("\xC2\xA6", $field['props']);
    $is_select = advfrm_is_select($field);
    $is_real_select = advfrm_is_real_select($field);
    $is_multi = advfrm_is_multi($field);
    if ($is_select) {
	$brackets = $is_multi ? '[]' : '';
	if ($is_real_select) {
	    $size = array_shift($props);
	    $size = empty($size) ? '' : ' size="'.$size.'"';
	    $multi = $is_multi ? ' multiple="multiple"' : '';
	    $htm .= '<select id="'.$id.'" name="'.$name.$brackets.'"'.$size.$multi.'>';
	} else {
	    $orient = array_shift($props) ? 'vert' : 'horz';
	}
	foreach ($props as $i => $opt) {
	    $opt = explode("\xE2\x97\x8F", $opt);
	    if (count($opt) > 1) {
		$f = TRUE;
		$opt = $opt[1];
	    } else {
		$f = FALSE;
		$opt = $opt[0];
	    }
	    if (function_exists('advfrm_custom_field_default')) {
		$cust_f = advfrm_custom_field_default($form_id, $field['field'], $opt, isset($_POST['advfrm']));
	    }
	    $f = isset($cust_f) ? $cust_f : isset($_POST['advfrm']) && isset($_POST[$name])
		    && ($is_multi ? in_array($opt, array_map('stsl', $_POST[$name])) : stsl($_POST[$name]) == $opt)
		    || !isset($_POST['advfrm']) && $f;
	    $sel = $f ? ($is_real_select ? ' selected="selected"' : ' checked="checked"') : '';
	    if ($is_real_select) {
		$htm .= '<option'.$sel.'>'.htmlspecialchars($opt).'</option>';
	    } else {
		$id .= '-'.htmlspecialchars($opt);
		$htm .= '<div class="'.$orient.'">'.tag('input type="'.$field['type'].'"'
			.' id="'.$id.'" name="'.$name.$brackets.'" value="'.htmlspecialchars($opt).'"'.$sel)
			.'&nbsp;<label for="'.$id.'">'.htmlspecialchars($opt).'</label></div>';
	    }
	}
	if ($is_real_select) {
	    $htm .= '</select>';
	}
    } else {
	$type = in_array($field['type'], array('file', 'password', 'hidden'))
		? $field['type']
		: 'text';
	if (function_exists('advfrm_custom_field_default')) {
	    $val = advfrm_custom_field_default($form_id, $field['field'], null, isset($_POST['advfrm']));
	}
	if (!isset($val)) {
	    $val =  isset($_POST[$name]) ? stsl($_POST[$name]) : $props[ADVFRM_PROP_DEFAULT];
	}
	if ($field['type'] == 'textarea') {
	    $cols = empty($props[ADVFRM_PROP_COLS]) ? 40 : $props[ADVFRM_PROP_COLS];
	    $rows = empty($props[ADVFRM_PROP_ROWS]) ? 4 : $props[ADVFRM_PROP_ROWS];
	    $htm .= '<textarea id="'.$id.'" name="'.$name.'" cols="'.$cols.'" rows="'.$rows.'">'
		    .htmlspecialchars($val).'</textarea>';
	} elseif ($field['type'] == 'output') {
	    $htm .= $val;
	} else {
	    if ($field['type'] == 'date') {
		$hjs .= '<script type="text/javascript">'."\n".'/* <![CDATA[ */'."\n"
			.'jQuery(function() {jQuery(\'.advfrm-mailform form[name="'.$form_id.'"]'
			.' input[name="'.$name.'"]\').datepicker()})'."\n"
			.'/* ]]> */'."\n".'</script>'."\n";
	    }
	    $size = $field['type'] == 'hidden' || empty($props[ADVFRM_PROP_SIZE])
		    ? ''
		    : ' size="'.$props[ADVFRM_PROP_SIZE].'"';
	    $maxlen = $field['type'] == 'hidden' || empty($props[ADVFRM_PROP_MAXLEN])
		    ? ''
		    : ' maxlength="'.$props[ADVFRM_PROP_MAXLEN].'"';
	    if ($field['type'] == 'file' && !empty($props[ADVFRM_PROP_MAXLEN])) {
		$htm .= tag('input type="hidden" name="MAX_FILE_SIZE" value="'.$props[ADVFRM_PROP_MAXLEN].'"');
	    }
	    $htm .= tag('input type="'.$type.'" id="'.$id.'" name="'.$name.'" value="'.htmlspecialchars($val).'"'.$size.$maxlen);
	}
    }
    return $htm;
}


/**
 * Returns the default display of the form.
 *
 * @param  string $id
 * @return string	(x)html
 */
function advfrm_default_display($id) {
    global $plugin_cf;

    $pcf = $plugin_cf['advancedform'];
    $forms = advfrm_db();
    $form = $forms[$id];

    $htm = '';
    $htm .= '<div style="overflow:auto">'."\n".'<table>'."\n";
    foreach ($form['fields'] as $field) {
	$label = htmlspecialchars($field['label']);
	$label = $field['required'] ? sprintf($pcf['required_field_mark'], $label) : $label;
	$hidden = $field['type'] == 'hidden';
	$class = $hidden ? ' class="hidden"' : '';
	$field_id = 'advfrm-'.$id.'-'.$field['field'];
	$labelled = !in_array($field['type'], array('checkbox', 'radio', 'output'));
	$htm .= '<tr'.$class.'>';
	if (!$hidden) {
	    $htm .= '<td class="label">'
		    .($labelled ? '<label for="'.$field_id.'">' : '')
		    .$label
		    .($labelled ? '</label>' : '')
		    .'</td>';
	}
	$htm .= '<td class="field">';
	$htm .= advfrm_display_field($id, $field);
	$htm .= '</td></tr>'."\n";
	if ($labelled) {
	    advfrm_focus_field($id, 'advfrm-'.$field['field']);
	}
    }
    $htm .= '</table>'."\n".'</div>'."\n";
    return $htm;
}


/**
 * Returns the display of the form by instatiating the template.
 *
 * @param  string $id
 * @return string	(x)html
 */
function advfrm_template_display($id) {
    global $hjs, $plugin_cf;

    $forms = advfrm_db();
    $fn = advfrm_data_folder().'css/'.$id.'.css';
    if (file_exists($fn)) {
	$hjs .= tag('link rel="stylesheet" href="'.$fn.'" type="text/css"')."\n";
    }
    $fn = advfrm_data_folder().'js/'.$id.'.js';
    if (file_exists($fn)) {
	$hjs .= '<script type="text/javascript" src="'.$fn.'"></script>'."\n";
    }

    $form = $forms[$id];
    $fn = advfrm_data_folder().$id.'.tpl'
	    .($plugin_cf['advancedform']['php_extension'] ? '.php' : '');
    $advfrm_script = file_get_contents($fn);
    foreach ($form['fields'] as $field) {
	$advfrm_script = str_replace('<?field '.$field['field'].'?>',
		advfrm_display_field($id, $field), $advfrm_script);
    }
    extract($GLOBALS);
    ob_start();
    eval('?>'.$advfrm_script);
    return ob_get_clean();
}


/**
 * Returns the display of the form.
 *
 * @param  string $id
 * @return string	(x)html
 */
function advfrm_display($id) {
    global $sn, $su, $pth, $plugin_cf, $plugin_tx;

    $ptx = $plugin_tx['advancedform'];
    $pcf = $plugin_cf['advancedform'];

    $forms = advfrm_db();
    $form = $forms[$id];
    advfrm_init_jquery();
    $htm = '';
    $htm .= '<div class="advfrm-mailform">'."\n";
    $htm .= '<form name="'.$id.'" action="'.$sn.'?'.$su.'" method="post"'
	    .' enctype="multipart/form-data" accept-charset="UTF-8">'."\n";
    $htm .= tag('input type="hidden" name="advfrm" value="'.$id.'"')."\n";
    $htm .= '<div class="required">'.sprintf($ptx['message_required_fields'],
	    sprintf($pcf['required_field_mark'], $ptx['message_required_field'])).'</div>'."\n";
    if (file_exists(advfrm_data_folder().$id.'.tpl')) {
	$htm .= advfrm_template_display($id);
    } else {
	$htm .= advfrm_default_display($id);
    }
    if ($form['captcha']) {
	$htm .= call_user_func($pcf['captcha_plugin'].'_captcha_display');
    }
    $htm .= '<div class="buttons">'.tag('input type="submit" class="submit" value="'.$ptx['button_send'].'"').'&nbsp;'
	    .tag('input type="reset" class="submit" value="'.$ptx['button_reset'].'"').'</div>'."\n";
    $htm .= '<div class="powered-by" style="display: block !important; visibility: visible !important">Powered by '
	    .'<a href="http://3-magi.net/?CMSimple_XH/Advancedform_XH">Advancedform_XH</a></div>'."\n";
    $htm .= '</form>'."\n";
    $htm .= '</div>'."\n";
    return $htm;
}


/**
 * Checks sent form.
 * Returns TRUE on success, x(html) error message on failure.
 *
 * @param  string $id
 * @return mixed
 */
function advfrm_check($id) {
    global $plugin_cf, $plugin_tx;

    $pcf = $plugin_cf['advancedform'];
    $ptx = $plugin_tx['advancedform'];
    $res = '';
    $forms = advfrm_db();
    $form = $forms[$id];
    foreach ($form['fields'] as $field) {
	$name = 'advfrm-'.$field['field'];
	if ($field['type'] != 'file' && empty($_POST[$name])
		|| $field['type'] == 'file' && empty($_FILES[$name]['name'])) {
	    if ($field['required']) {
		$res .= '<li>'.sprintf($ptx['error_missing_field'],
			htmlspecialchars($field['label'])).'</li>'."\n";
		advfrm_focus_field($id, $name);
	    }
	} else {
	    switch ($field['type']) {
		case 'from':
		case 'email':
		    if (!preg_match($pcf['mail_regexp'], stsl($_POST[$name]))) {
			$res .= '<li>'.sprintf($ptx['error_invalid_email'],
				htmlspecialchars($field['label'])).'</li>'."\n";
			advfrm_focus_field($id, $name);
		    }
		    break;
		case 'date':
		    $pattern = '/^([0-9]+)\\'.$ptx['date_delimiter'].'([0-9]+)\\'.$ptx['date_delimiter'].'([0-9]+)$/';
		    $matched = preg_match($pattern, stsl($_POST[$name]), $matches);
		    if (count($matches) == 4) {
			$month = $matches[strpos($ptx['date_order'], 'm')+1];
			$day = $matches[strpos($ptx['date_order'], 'd')+1];
			$year = $matches[strpos($ptx['date_order'], 'y')+1];
		    }
		    if (!$matched || !checkdate($month, $day, $year)) {
			$res .= '<li>'.sprintf($ptx['error_invalid_date'],
				htmlspecialchars($field['label'])).'</li>';
			advfrm_focus_field($id, $name);
		    }
		    break;
		case 'number':
		    if (!ctype_digit(stsl($_POST[$name]))) {
			$res .= '<li>'.sprintf($ptx['error_invalid_number'],
				htmlspecialchars($field['label'])).'</li>'."\n";
			advfrm_focus_field($id, $name);
		    }
		    break;
		case 'file':
		    $props = explode("\xC2\xA6", $field['props']);
		    switch ($_FILES[$name]['error']) {
			case UPLOAD_ERR_OK:
			    if (!empty($props[ADVFRM_PROP_MAXLEN])
				    && $_FILES[$name]['size'] > $props[ADVFRM_PROP_MAXLEN]) {
				$res .= '<li>'.sprintf($ptx['error_upload_too_large'],
					htmlspecialchars($field['label'])).'</li>'."\n";
				advfrm_focus_field($id, $name);
			    }
			    break;
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
			    $res .= '<li>'.sprintf($ptx['error_upload_too_large'],
				    htmlspecialchars($field['label'])).'</li>'."\n";
			    advfrm_focus_field($id, $name);
			    break;
			default:
			    $res .= '<li>'.sprintf($ptx['error_upload_general'],
				    htmlspecialchars($field['label'])).'</li>'."\n";
			    advfrm_focus_field($id, $name);
		    }
		    $ext = pathinfo($_FILES[$name]['name'], PATHINFO_EXTENSION);
		    if (!empty($props[ADVFRM_PROP_FTYPES])
			    && !in_array($ext, explode(',', $props[ADVFRM_PROP_FTYPES]))) {
			$res .= '<li>'.sprintf($ptx['error_upload_illegal_ftype'],
				htmlspecialchars($field['label']),
				htmlspecialchars($ext)).'</li>'."\n";
			advfrm_focus_field($id, $name);
    		    }
		    break;
		case 'custom':
		    $props = explode("\xC2\xA6", $field['props']);
		    $pattern = $props[ADVFRM_PROP_CONSTRAINT];
		    if (!empty($pattern) && !preg_match($pattern, stsl($_POST[$name]))) {
			$msg = empty($props[ADVFRM_PROP_ERROR_MSG])
				? $ptx['error_invalid_custom']
				: $props[ADVFRM_PROP_ERROR_MSG];
			$res .= '<li>'.sprintf($msg, $field['label']).'</li>'."\n";
			advfrm_focus_field($id, $name);
		    }
	    }
	    if (function_exists('advfrm_custom_valid_field')) {
		if (($valid = advfrm_custom_valid_field($id, $field['field'],
			($field['type'] == 'file' ? $_FILES[$name] : stsl($_POST[$name])))) !== TRUE) {
		    $res .= '<li>'.$valid.'</li>'."\n";
		    advfrm_focus_field($id, $name);
		}
	    }
	}
    }
    if ($form['captcha']) {
	if (!call_user_func($pcf['captcha_plugin'].'_captcha_check')) {
	    $res .= '<li>'.$ptx['error_captcha_code'].'</li>';
	    advfrm_focus_field($id, 'advancedform-captcha');
	}
    }
    return $res == '' ? TRUE : '<ul class="advfrm-error">'."\n".$res.'</ul>'."\n";
}


/**
 * Sends the mail.
 * Returns wether that was successful.
 *
 * @param  string $id		 The form ID.
 * @param  bool   $confirmation
 * @return bool
 */
function advfrm_mail($id, $confirmation) {
    global $pth, $sl, $plugin_tx, $plugin_cf, $e;

    include_once $pth['folder']['plugins'].'advancedform/phpmailer/class.phpmailer.php';
    $pcf = $plugin_cf['advancedform'];
    $ptx = $plugin_tx['advancedform'];
    $forms = advfrm_db();
    $form = $forms[$id];
    $type = strtolower($pcf['mail_type']);
    $from = '';
    $from_name = '';
    foreach ($form['fields'] as $field) {
	if ($field['type'] == 'from_name') {
	    $from_name = stsl($_POST['advfrm-'.$field['field']]);
	} elseif ($field['type'] == 'from') {
	    $from = stsl($_POST['advfrm-'.$field['field']]);
	}
    }
    if ($confirmation && empty($from)) {
	$e .= '<li>'.$ptx['error_missing_sender'].'</li>'."\n";
	return FALSE;
    }

    $mail = new PHPMailer();
    $mail->LE = $pcf['mail_line_ending_*nix'] ? "\n" : "\r\n";
    $mail->set('CharSet', 'UTF-8');
    $mail->SetLanguage($sl, $pth['folder']['plugins'].'advancedform/phpmailer/language/');
    $mail->set('WordWrap', 72);
    if ($confirmation) {
	$mail->set('From', $form['to']);
	$mail->set('FromName', $form['to_name']);
	$mail->AddAddress($from, $from_name);
    } else {
	$mail->set('From', $from);
	$mail->set('FromName', $from_name);
	$mail->AddAddress($form['to'], $form['to_name']);
	foreach (explode(';', $form['cc']) as $cc) {
	    if (trim($cc) != '') {$mail->AddCC($cc);}
	}
	foreach (explode(';', $form['bcc']) as $bcc) {
	    if (trim($bcc) != '') {$mail->AddBCC($bcc);}
	}
    }
    if ($confirmation) {
	$mail->set('Subject', sprintf($ptx['mail_subject_confirmation'], $form['title'], $_SERVER['SERVER_NAME']));
    } else {
	$mail->set('Subject', sprintf($ptx['mail_subject'], $form['title'], $_SERVER['SERVER_NAME'], $_SERVER['REMOTE_ADDR']));
    }
    $mail->IsHtml($type != 'text');
    if ($type == 'text') {
	$mail->set('Body', advfrm_mail_body($id, !$confirmation, FALSE));
    } else {
	$body = advfrm_mail_body($id, !$confirmation, TRUE);
	$mail->MsgHTML($body);
	$mail->set('AltBody', advfrm_mail_body($id, !$confirmation, FALSE));
    }
    if (!$confirmation) {
	foreach ($form['fields'] as $field) {
	    if ($field['type'] == 'file') {
		$name = 'advfrm-'.$field['field'];
		$mail->AddAttachment($_FILES[$name]['tmp_name'], stsl($_FILES[$name]['name']));
	    }
	}
    }

    if (function_exists('advfrm_custom_mail')) {
	if (advfrm_custom_mail($id, $mail, $confirmation) === FALSE) {return TRUE;}
    }

    if (ADVFRM_DEBUG) {
	return TRUE;
    }

    if(!$mail->Send()) {
	$e .= '<li>'.(!empty($mail->ErrorInfo) ? htmlspecialchars($mail->ErrorInfo) : $ptx['error_mail']).'</li>'."\n";
	return FALSE;
    } else {
	return TRUE;
    }
}


/**
 * Main plugin call.
 *
 * @param string $id  Name of the form.
 * @return string
 */
function advfrm_advancedform($id) {
    global $plugin_cf, $plugin_tx, $sn, $e, $pth;

    $pcf = $plugin_cf['advancedform'];
    $ptx = $plugin_tx['advancedform'];

    $fn = $pth['folder']['plugins'].$pcf['captcha_plugin'].'/captcha.php';
    if (file_exists($fn)) {
	include_once $fn;
    } else {
	e('cntopen', 'file', $fn);
    }

    $hooks = advfrm_data_folder().$id.'.inc'
	    .($pcf['php_extension'] ? '.php' : '');
    if (file_exists($hooks)) {
	include $hooks;
    }

    $forms = advfrm_db();
    if (!isset($forms[$id])) {
	$e .= '<li>'.sprintf($ptx['error_form_missing'], $id).'</li>'."\n";
	return '';
    }
    $form = $forms[$id];
    if (isset($_POST['advfrm']) && $_POST['advfrm'] == $id) {
    	if (($res = advfrm_check($id)) === TRUE) {
	    if ($form['store']) {advfrm_append_csv($id);}
	    if (!advfrm_mail($id, FALSE)) {
		return advfrm_display($id);
	    }
	    if (function_exists('advfrm_custom_thanks_page')) {
		advfrm_fields($fields);
		$thanks = advfrm_custom_thanks_page($id, $fields);
	    }
	    if (empty($thanks)) {$thanks = $form['thanks_page'];}
	    if (!empty($thanks)) {
		if (!advfrm_mail($id, TRUE)) {
		    return advfrm_display($id);
		}
		header('Location: '.$sn.'?'.$thanks);
	    } else {
		return advfrm_mail_info($id, FALSE, TRUE);
	    }
	} else {
	    return $res.advfrm_display($id);
	}
    }
    return advfrm_display($id);
}

?>

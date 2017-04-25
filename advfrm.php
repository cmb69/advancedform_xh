<?php

/**
 * Main functionionality of Advancedform_XH.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   Advancedform
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2005-2010 Jan Kanters
 * @copyright 2011-2017 Christoph M. Becker <http://3-magi.net>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://3-magi.net/?CMSimple_XH/Advancedform_XH
 */

/**
 * The version of the database format.
 */
define('ADVFRM_DB_VERSION', 2);

/**
 * The index of the size property.
 */
define('ADVFRM_PROP_SIZE', 0);

/**
 * The index of the cols property.
 */
define('ADVFRM_PROP_COLS', 0);

/**
 * The index of the maxlen property.
 */
define('ADVFRM_PROP_MAXLEN', 1);

/**
 * The index of the rows property.
 */
define('ADVFRM_PROP_ROWS', 1);

/**
 * The index of the default property.
 */
define('ADVFRM_PROP_DEFAULT', 2);

/**
 * The index of the value property.
 */
define('ADVFRM_PROP_VALUE', 2);

/**
 * The index of the field types property.
 */
define('ADVFRM_PROP_FTYPES', 2);

/**
 * The index of the contstraint property.
 */
define('ADVFRM_PROP_CONSTRAINT', 3);

/**
 * The index of the error message property.
 */
define('ADVFRM_PROP_ERROR_MSG', 4);

/**
 * Returns a new array as with {@link array_combine array_combine()}.
 *
 * @param array $array1 An array.
 * @param array $array2 An array.
 *
 * @return array
 */
function Advancedform_combineArrays($array1, $array2)
{
    if (function_exists('array_combine')) {
        return array_combine($array1, $array2);
    } else {
        if (count($arr1) !== count($arr2)) {
            return false;
        }
        $res = array();
        $arr1 = array_values($arr1);
        $arr2 = array_values($arr2);
        foreach ($arr1 as $key1 => $value1) {
            $res[(string)$value1] = $arr2[$key1];
        }
        return $res;
    }
}

/**
 * Returns string with a BR element inserted before all linebreaks.
 *
 * @param string $string An (X)HTML fragment.
 *
 * @return string (X)HTML.
 */
function Advancedform_nl2br($string)
{
    return preg_replace('/(\r\n|\n\r|\n|\r)/su', tag('br').'$1', $string);
}

/**
 * Returns string with two spaces inserted after all linebreaks.
 *
 * @param string $string A string.
 *
 * @return string
 */
function Advancedform_indent($string)
{
    return preg_replace('/(\r\n|\n\r|\n|\r)/su', '$1  ', $string);
}

/**
 * Emits a SCRIPT element to set the focus to the field with name $name.
 *
 * @param string $form_id A form ID.
 * @param string $name    A field name.
 *
 * @return void
 *
 * @global string The (X)HTML fragment to for insertion into the HEAD element.
 */
function Advancedform_focusField($form_id, $name)
{
    global $hjs;

    if (defined('ADVFRM_FIELD_FOCUSED')) {
        return;
    }
    Advancedform_initJQuery();
    $hjs .= <<<SCRIPT
<script type="text/javascript">/* <![CDATA[ */
jQuery(function() {
    jQuery('.advfrm-mailform form[name="$form_id"] *[name="$name"]').focus()
})
/* ]]> */</script>

SCRIPT;
    define('ADVFRM_FIELD_FOCUSED', true);
}

/**
 * Includes jquery and initializes the datepicker, if not already done.
 *
 * @return void
 *
 * @global array  The paths of system files and folders.
 * @global string The current language.
 * @global string The (X)HTML fragment for insertion into the HEAD element.
 * @global array  The configuration of the core.
 * @global array  The configuration of the plugins.
 * @global array  The localization of the plugins.
 */
function Advancedform_initJQuery()
{
    global $pth, $sl, $hjs, $cf, $plugin_cf, $plugin_tx;

    if (defined('ADVFRM_JQUERY_INITIALIZED')) {
        return;
    }
    $ptx = $plugin_tx['advancedform'];

    if (include_once $pth['folder']['plugins'] . 'jquery/jquery.inc.php') {
        include_jQuery();
        include_jQueryUI();
    }
    $date_format = $ptx['date_order'][0] . $ptx['date_order'][0]
        . $ptx['date_delimiter'] . $ptx['date_order'][1] . $ptx['date_order'][1]
        . $ptx['date_delimiter'] . $ptx['date_order'][2] . $ptx['date_order'][2];

    $lang = (strlen($sl) == 2) ? $sl : $cf['language']['default'];
    $fn = $pth['folder']['plugins']
        . 'advancedform/languages/jquery.ui.datepicker-' . $lang . '.js';
    if (file_exists($fn)) {
        $hjs .= '<script type="text/javascript" src="' . $fn . '"></script>'
            . PHP_EOL;
    } else {
        if ($sl != 'en') {
            e('missing', 'language', $fn);
        }
    }
    $hjs .= <<<SCRIPT

<script type="text/javascript">/* <![CDATA[ */
jQuery(function() {
    jQuery.datepicker.setDefaults(jQuery.datepicker.regional['$lang']);
    jQuery.datepicker.setDefaults({dateFormat: '$date_format'});
})
/* ]]> */</script>

SCRIPT;
    define('ADVFRM_JQUERY_INITIALIZED', true);
}

/**
 * Returns whether a field is a selection field (select, checkbox or radio).
 *
 * @param array $field A field.
 *
 * @return bool
 */
function Advancedform_isSelect($field)
{
    $selectionFieldTypes = array('radio', 'checkbox', 'select', 'multi_select');
    return in_array($field['type'], $selectionFieldTypes);
}

/**
 * Returns whether a field is a proper selection field.
 *
 * @param array $field A field.
 *
 * @return bool
 */
function Advancedform_isRealSelect($field)
{
    $selectionFieldTypes = array('select', 'multi_select');
    return in_array($field['type'], $selectionFieldTypes);
}

/**
 * Returns whether a field is a multi selection field (select multiple or checkbox).
 *
 * @param array $field A field.
 *
 * @return bool
 */
function Advancedform_isMulti($field)
{
    $selectionFieldTypes = array('checkbox', 'multi_select');
    return in_array($field['type'], $selectionFieldTypes);
}

/**
 * Returns the data folder path. Tries to create it, if necessary.
 *
 * @return string
 *
 * @global array The paths of system files and folders.
 * @global array The configuration of the plugins.
 */
function Advancedform_dataFolder()
{
    global $pth, $plugin_cf;

    $pcf = $plugin_cf['advancedform'];

    if ($pcf['folder_data'] == '') {
        $fn = $pth['folder']['plugins'] . 'advancedform/data/';
    } else {
        $fn = $pth['folder']['base'] . $pcf['folder_data'];
    }
    if (substr($fn, -1) != '/') {
        $fn .= '/';
    }
    if (file_exists($fn)) {
        if (!is_dir($fn)) {
            e('cntopen', 'folder', $fn);
        }
    } else {
        if (!mkdir($fn, 0777, true)) {
            e('cntwriteto', 'folder', $fn);
        }
    }
    return $fn;
}

/**
 * Returns the remaining contents of a stream.
 *
 * @param resource $stream An open stream.
 *
 * @return string
 */
function Advancedform_getStreamContents($stream)
{
    $func = 'stream_get_contents';
    if (function_exists($func)) {
        $contents = $func($stream);
    } else {
        ob_start();
        fpassthru($stream);
        $contents = ob_get_clean();
    }
    return $contents;
}

/**
 * Reads a file and returns its contents; <var>false</var> on failure.
 * During reading, the file is locked for shared access.
 *
 * @param string $filename A file path.
 *
 * @return string
 */
function Advancedform_readFile($filename)
{
    $contents = false;
    $stream = fopen($filename, 'rb');
    if ($stream) {
        if (flock($stream, LOCK_SH)) {
            $contents = Advancedform_getStreamContents($stream);
            flock($stream, LOCK_UN);
        }
        fclose($stream);
    }
    return $contents;
}

/**
 * Writes <var>$contents</var> to the file <var>$filename</var>.
 * During writing the file is locked exclusively.
 *
 * @param string $filename The filename.
 * @param string $contents The content to write.
 *
 * @return int The number of bytes written, or false on failure.
 */
function Advancedform_writeFile($filename, $contents)
{
    $res = false;
    // we can't use "cb" as it is available only since PHP 5.2.6
    // we can't use "r+b" as it will fail if the file does not already exist
    $stream = fopen($filename, 'a+b');
    if ($stream) {
        if (flock($stream, LOCK_EX)) {
            fseek($stream, 0);
            ftruncate($stream, 0);
            $res = fwrite($stream, $contents);
            fflush($stream);
            flock($stream, LOCK_UN);
        }
        fclose($stream);
    }
    return $res;
}

/**
 * Returns the form database, if $forms is omitted.
 * Otherwise writes $forms as form database.
 *
 * @param array $forms A forms collection.
 *
 * @return mixed
 */
function Advancedform_db($forms = null)
{
    static $db;

    if (isset($forms)) { // write
        ksort($forms);
        $fn = Advancedform_dataFolder() . 'forms.dat';
        $contents = serialize($forms);
        if (!Advancedform_writeFile($fn, $contents)) {
            e('cntwriteto', 'file', $fn);
        }
        $db = $forms;
    } else {  // read
        if (!isset($db)) {
            $fn = Advancedform_dataFolder() . 'forms.dat';
            $contents = Advancedform_readFile($fn);
            $db = ($contents !== false) ? unserialize($contents) : array();
            if (empty($db['%VERSION%'])) {
                $db['%VERSION%'] = 0;
            }
            if ($db['%VERSION%'] < ADVFRM_DB_VERSION) {
                $db = Advancedform_updatedDb($db);
                Advancedform_db($db);
            }
        }
        return $db;
    }
}

/**
 * Returns the forms database updated to the current version.
 *
 * @param array $forms A forms collection.
 *
 * @return array
 */
function Advancedform_updatedDb($forms)
{
    switch ($forms['%VERSION%']) {
    case 0:
    case 1:
        $forms = array_map(
            create_function(
                '$elt',
                'if (is_array($elt)) {$elt["store"] = false;} return $elt;'
            ),
            $forms
        );
    }
    $forms['%VERSION%'] = ADVFRM_DB_VERSION;
    return $forms;
}

/**
 * Escapes a JS string.
 *
 * @param string $string A string.
 *
 * @return string
 */
function Advancedform_escapeJsString($string)
{
    return addcslashes($string, "\t\n\r\"\'\\");
}

/**
 * Updates the LANG.js file if necessary, with the strings from LANG.php.
 * Returns false on failure.
 *
 * @return bool
 *
 * @global array  The paths of system files and folders.
 * @global string The current language.
 * @global array  The localization of the plugins.
 */
function Advancedform_updateLangJs()
{
    global $pth, $sl, $plugin_tx;

    $ptx = $plugin_tx['advancedform'];

    $fn = $pth['folder']['plugins'] . 'advancedform/languages/' . $sl;
    if (!file_exists($fn . '.php')) {
        e('missing', 'language', $fn . '.php');
        return false;
    }
    if (!file_exists($fn . '.js')
        || filemtime($fn . '.js') < filemtime($fn . '.php')
    ) {
        $js = '// auto-generated by Advancedform_XH -- do not modify!' . PHP_EOL
            . '// any modifications should be made in ' . $sl . '.php' . PHP_EOL
            . PHP_EOL . 'ADVFRM_TX = {' . PHP_EOL;
        $first = true;
        foreach ($ptx as $key => $msg) {
            $parts = explode('_', $key);
            if ($parts[0] != 'cf') {
                if ($first) {
                    $first = false;
                } else {
                    $js .= ',' . PHP_EOL;
                }
                $js .= '    \'' . $key . '\': \''
                    . Advancedform_escapeJsString($msg) . '\'';
            }
        }
        $js .= PHP_EOL . '};' . PHP_EOL;
        if (!($fh = fopen($fn . '.js', 'w'))
            || ($res = fwrite($fh, $js)) === false
        ) {
            e('cntwriteto', 'file', $fn . '.js');
        }
        if ($fh) {
            fclose($fh);
        }
        return $fh && $res;
    }
    return true;
}

/**
 * Returns the content of the CSV file as array on success, false otherwise.
 *
 * @param string $id A form ID.
 *
 * @return array
 *
 * @global string The (X)HTML fragment containing error messages.
 * @global array  The configuration of the plugins.
 * @global array  The localization of the plugins.
 */
function Advancedform_readCsv($id)
{
    global $e, $plugin_cf, $plugin_tx;

    $pcf = $plugin_cf['advancedform'];
    $forms = Advancedform_db();
    $fields = array();
    if (isset($forms[$id])) {
        foreach ($forms[$id]['fields'] as $field) {
            if ($field['type'] != 'output') {
                $fields[] = $field['field'];
            }
        }
    } else {
        $e .= '<li>'
            . sprintf($plugin_tx['advancedform']['error_form_missing'], $id)
            . '</li>' . PHP_EOL;
        return false;
    }

    $fn = Advancedform_dataFolder() . $id . '.csv';
    if ($pcf['csv_separator'] == '') {
        if (($lines = file($fn)) === false) {
            e('cntopen', 'file', $fn);
            return array();
        }
        $data = array();
        foreach ($lines as $line) {
            $line = array_map('trim', explode("\t", $line));
            $rec = Advancedform_combineArrays($fields, $line);
            $data[] = $rec;
        }
    } else {
        $sep = $pcf['csv_separator'];
        $data = array();
        if (($stream = fopen($fn, 'r')) !== false) {
            while (($rec = fgetcsv($stream, 0x10000, $sep)) !== false) {
                $data[] = Advancedform_combineArrays($fields, $rec);
            }
            fclose($stream);
        } else {
            e('cntopen', 'file', $fn);
        }
    }
    return $data;
}

/**
 * Escapes a field value for use in a CSV file.
 *
 * @param string $field A field value.
 *
 * @return string
 *
 * @global array The configuration of the plugins.
 */
function Advancedform_escapeCsvField($field)
{
    global $plugin_cf;

    $specialChars = "\"\r\n" . $plugin_cf['advancedform']['csv_separator'];
    $specialChars = preg_quote($specialChars, '/');
    if (preg_match('/[' . $specialChars . ']/', $field)) {
        $field = str_replace('"', '""', $field);
        $field = '"' . $field . '"';
    }
    return $field;
}

/**
 * Appends the posted record to csv file.
 *
 * @param string $id A form ID.
 *
 * @return void
 *
 * @global array The configuration of the plugins.
 */
function Advancedform_appendCsv($id)
{
    global $plugin_cf;

    $forms = Advancedform_db();
    $fields = array();
    foreach ($forms[$id]['fields'] as $field) {
        if ($field['type'] != 'output') {
            $name = $field['field'];
            $val = ($field['type'] == 'file')
                ? $_FILES['advfrm-'.$name]['name']
                : $_POST['advfrm-'.$name];
            $fields[] = is_array($val)
                ? implode("\xC2\xA6", array_map('stsl', $val))
                : stsl($val);
        }
    }
    if ($plugin_cf['advancedform']['csv_separator'] != '') {
        $fields = array_map('Advancedform_escapeCsvField', $fields);
        $separator = $plugin_cf['advancedform']['csv_separator'];
    } else {
        $separator = "\t";
    }
    $fn = Advancedform_dataFolder() . $id . '.csv';
    if (($fh = fopen($fn, 'a')) === false
        || fwrite($fh, implode($separator, $fields)."\n") === false
    ) {
        e('cntwriteto', 'file', $fn);
    }
    if ($fh !== false) {
        fclose($fh);
    }
}

/**
 * Returns the posted fields, as e.g. needed for advfrm_custom_thanks_page().
 *
 * @return array
 */
function Advancedform_fields()
{
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
 * Returns a string where all special HTML characters are replaced with entities.
 *
 * @param string $string A string.
 *
 * @return string
 */
function Advancedform_hsc($string)
{
    if (function_exists('XH_hsc')) {
        return XH_hsc($string);
    } else {
        return htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
    }
}

/**
 * Returns the information sent/to send.
 *
 * @param string $id          A form ID.
 * @param bool   $show_hidden Whether to include hidden fields.
 * @param bool   $html        Whether to return (X)HTML.
 *
 * @return string
 *
 * @global array The configuration of the core.
 * @global array The localization of the plugins.
 */
function Advancedform_mailInfo($id, $show_hidden, $html)
{
    global $cf, $plugin_tx;

    $ptx = $plugin_tx['advancedform'];
    $forms = Advancedform_db();
    $form = $forms[$id];
    $o = '';
    if ($html) {
        $o .= '<div class="advfrm-mailform">' . PHP_EOL;
    }
    if (!$show_hidden) {
        $o .= $html
            ? '<p>' . $ptx['message_sent_info'] . '</p>' . PHP_EOL
            : strip_tags($ptx['message_sent_info']) . PHP_EOL . PHP_EOL;
    }
    if ($html) {
        $o .= '<table>' . PHP_EOL;
    }
    foreach ($form['fields'] as $field) {
        if (($field['type'] != 'hidden' || $show_hidden)
            && $field['type'] != 'output'
        ) {
            $name = 'advfrm-' . $field['field'];
            if ($html) {
                $o .= '<tr><td class="label">' . Advancedform_hsc($field['label'])
                    . '</td><td class="field">';
            } else {
                $o .= $field['label'] . PHP_EOL;
            }
            if (isset($_POST[$name])) {
                if (is_array($_POST[$name])) {
                    foreach ($_POST[$name] as $val) {
                        $o .= $html
                            ? '<div>' . Advancedform_hsc(stsl($val)) . '</div>'
                            : '  ' . stsl($val) . PHP_EOL;
                    }
                } else {
                    $o .= $html
                        ? Advancedform_nl2br(Advancedform_hsc(stsl($_POST[$name])))
                        : '  ' . Advancedform_indent(stsl($_POST[$name])) . PHP_EOL;
                }
            } elseif (isset($_FILES[$name])) {
                $o .= $html
                    ? stsl($_FILES[$name]['name'])
                    : '  ' . stsl($_FILES[$name]['name']) . PHP_EOL;
            }
            if ($html) {
                $o .= '</td></tr>' . PHP_EOL;
            }
        }
    }
    if ($html) {
        $o .= '</table>' . PHP_EOL . '</div>' . PHP_EOL;
    }
    return $o;
}

/**
 * Returns the top of a CSS file, i.e. everything above the comment line:
 * <i>END OF MAIL CSS</i>. If the file couldn't be read, returns an empty string.
 *
 * @param string $fn A CSS file name.
 *
 * @return string
 */
function Advancedform_mailCss($fn)
{
    if (($css = file_get_contents($fn)) !== false) {
        $css = explode('/* END OF MAIL CSS */', $css);
        return $css[0];
    } else {
        return '';
    }
}

/**
 * Returns the body of the mail.
 *
 * @param string $id          A form ID.
 * @param bool   $show_hidden Whether to include hidden fields.
 * @param bool   $html        Whether to return (X)HTML.
 *
 * @return string
 *
 * @global array The configuration of the core.
 * @global array The paths of system files and folders.
 */
function Advancedform_mailBody($id, $show_hidden, $html)
{
    global $cf, $pth;

    $forms = Advancedform_db();
    $form = $forms[$id];
    $o = '';
    if ($html) {
        if ($cf['xhtml']['endtags'] == 'true') {
            $o .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"'
                . ' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'
                . PHP_EOL . '<html xmlns="http://www.w3.org/1999/xhtml">' . PHP_EOL;
        } else {
            $o .= '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"'
                . ' "http://www.w3.org/TR/html4/loose.dtd">' . PHP_EOL
                . '<html>' . PHP_EOL;
        }
        $o .= '<head>' . PHP_EOL . '<style type="text/css">' . PHP_EOL;
        $o .= Advancedform_mailCss(
            $pth['folder']['plugins'] . 'advancedform/css/stylesheet.css'
        );
        $fn = Advancedform_dataFolder() . 'css/' . $id . '.css';
        if (file_exists($fn)) {
            $o .= Advancedform_mailCss($fn);
        }
        $o .= '</style>' . PHP_EOL . '</head>' . PHP_EOL . '<body>' . PHP_EOL;
    }
    $o .= Advancedform_mailInfo($id, $show_hidden, $html);
    if ($html) {
        $o .= '</body>' . PHP_EOL . '</html>' . PHP_EOL;
    }
    return $o;
}

/**
 * Prefixes each element of a comma separated list of file extensions with a dot.
 *
 * @param string $list A comma separated list of file extensions.
 *
 * @return string
 */
function Advancedform_prefixFileExtensionList($list)
{
    $extensions = explode(',', $list);
    $func = create_function('$x', 'return \'.\' . $x;');
    $extensions = array_map($func, $extensions);
    $list = implode(',', $extensions);
    return $list;
}

/**
 * Returns the view of a form field.
 *
 * @param string $form_id A form ID.
 * @param string $field   A field.
 *
 * @return string (X)HTML.
 *
 * @global array  The paths of system files and folders.
 * @global array  The configuration of the core.
 * @global string The (X)HTML fragment for insertion into the HEAD element.
 */
function Advancedform_displayField($form_id, $field)
{
    global $pth, $plugin_cf, $hjs;

    $pcf = $plugin_cf['advancedform'];

    $o = '';
    $name = 'advfrm-' . $field['field'];
    $id = 'advfrm-' . $form_id . '-' . $field['field'];
    $props = explode("\xC2\xA6", $field['props']);
    $is_select = Advancedform_isSelect($field);
    $is_real_select = Advancedform_isRealSelect($field);
    $is_multi = Advancedform_isMulti($field);
    if ($is_select) {
        $brackets = $is_multi ? '[]' : '';
        if ($is_real_select) {
            $size = array_shift($props);
            $size = empty($size) ? '' : ' size="'.$size.'"';
            $multi = $is_multi ? ' multiple="multiple"' : '';
            $o .= '<select id="' . $id . '" name="' . $name . $brackets . '"'
                . $size . $multi . '>';
        } else {
            $orient = array_shift($props) ? 'vert' : 'horz';
        }
        foreach ($props as $i => $opt) {
            $opt = explode("\xE2\x97\x8F", $opt);
            if (count($opt) > 1) {
                $f = true;
                $opt = $opt[1];
            } else {
                $f = false;
                $opt = $opt[0];
            }
            if (function_exists('advfrm_custom_field_default')) {
                $cust_f = advfrm_custom_field_default(
                    $form_id, $field['field'], $opt, isset($_POST['advfrm'])
                );
            }
            if (isset($cust_f)) {
                $f = $cust_f;
            } else {
                $f = isset($_POST['advfrm']) && isset($_POST[$name])
                    && ($is_multi
                        ? in_array($opt, array_map('stsl', $_POST[$name]))
                        : stsl($_POST[$name]) == $opt)
                    || !isset($_POST['advfrm']) && $f;
            }
            $sel = $f
                ? ($is_real_select ? ' selected="selected"' : ' checked="checked"')
                : '';
            if ($is_real_select) {
                $o .= '<option' . $sel . '>' . Advancedform_hsc($opt) . '</option>';
            } else {
                $o .= '<div class="' . $orient . '"><label>'
                    . tag(
                        'input type="'.$field['type'] . '" name="' . $name
                        . $brackets . '" value="' . Advancedform_hsc($opt) . '"'
                        . $sel
                    )
                    . '&nbsp;' . Advancedform_hsc($opt)
                    . '</label></div>';
            }
        }
        if ($is_real_select) {
            $o .= '</select>';
        }
    } else {
        $type = in_array($field['type'], array('file', 'password', 'hidden'))
            ? $field['type']
            : 'text';
        if (function_exists('advfrm_custom_field_default')) {
            $val = advfrm_custom_field_default(
                $form_id, $field['field'], null, isset($_POST['advfrm'])
            );
        }
        if (!isset($val)) {
            $val =  isset($_POST[$name])
                ? stsl($_POST[$name])
                : $props[ADVFRM_PROP_DEFAULT];
        }
        if ($field['type'] == 'textarea') {
            $cols = empty($props[ADVFRM_PROP_COLS]) ? 40 : $props[ADVFRM_PROP_COLS];
            $rows = empty($props[ADVFRM_PROP_ROWS]) ? 4 : $props[ADVFRM_PROP_ROWS];
            $o .= '<textarea id="' . $id . '" name="' . $name . '" cols="' . $cols
                . '" rows="' . $rows . '">'
                . Advancedform_hsc($val) . '</textarea>';
        } elseif ($field['type'] == 'output') {
            $o .= $val;
        } else {
            if ($field['type'] == 'date') {
                $showOn = $pcf['datepicker_icon'] ? 'both' : 'focus';
                $iconPath = $pth['folder']['plugins']
                    . 'advancedform/images/calendar.png';
                $hjs .= <<<EOS
<script type="text/javascript">/* <![CDATA[ */
jQuery(function() {
    jQuery('.advfrm-mailform form[name="$form_id"] input[name="$name"]')
        .datepicker({
            showOn: "$showOn",
            buttonImage: "$iconPath",
            buttonImageOnly: true
        })
});
/* ]]> */</script>

EOS;
            }
            $size = $field['type'] == 'hidden' || empty($props[ADVFRM_PROP_SIZE])
                ? ''
                : ' size="' . $props[ADVFRM_PROP_SIZE] . '"';
            $maxlen = in_array($field['type'], array('hidden', 'file'))
                || empty($props[ADVFRM_PROP_MAXLEN])
                ? ''
                : ' maxlength="' . $props[ADVFRM_PROP_MAXLEN] . '"';
            if ($field['type'] == 'file' && !empty($props[ADVFRM_PROP_MAXLEN])) {
                $o .= tag(
                    'input type="hidden" name="MAX_FILE_SIZE" value="'
                    . $props[ADVFRM_PROP_MAXLEN] . '"'
                );
            }
            if ($field['type'] == 'file') {
                $value = '';
                $accept = ' accept="'
                    . Advancedform_hsc(Advancedform_prefixFileExtensionList($val))
                    . '"';
            } else {
                $value = ' value="' . Advancedform_hsc($val) . '"';
                $accept = '';
            }
            $o .= tag(
                'input type="' . $type . '" id="' . $id . '" name="' . $name
                . '"' . $value . $accept . $size . $maxlen
            );
        }
    }
    return $o;
}

/**
 * Returns the default view of the form.
 *
 * @param string $id A form ID.
 *
 * @return string (X)HTML.
 *
 * @global array The configuration of the plugins.
 */
function Advancedform_defaultView($id)
{
    global $plugin_cf;

    $pcf = $plugin_cf['advancedform'];
    $forms = Advancedform_db();
    $form = $forms[$id];

    $o = '';
    $o .= '<div style="overflow:auto">' . PHP_EOL . '<table>' . PHP_EOL;
    foreach ($form['fields'] as $field) {
        $label = Advancedform_hsc($field['label']);
        $label = $field['required']
            ? sprintf($pcf['required_field_mark'], $label)
            : $label;
        $hidden = $field['type'] == 'hidden';
        $class = $hidden ? ' class="hidden"' : '';
        $field_id = 'advfrm-' . $id . '-' . $field['field'];
        $labelled = !in_array($field['type'], array('checkbox', 'radio', 'output'));
        $o .= '<tr' . $class . '>';
        if (!$hidden) {
            $o .= '<td class="label">'
                . ($labelled ? '<label for="' . $field_id . '">' : '')
                . $label
                . ($labelled ? '</label>' : '')
                . '</td>';
        } else {
            $o .= '<td></td>';
        }
        $o .= '<td class="field">';
        $o .= Advancedform_displayField($id, $field);
        $o .= '</td></tr>' . PHP_EOL;
        if ($labelled && $pcf['focus_form']) {
            Advancedform_focusField($id, 'advfrm-' . $field['field']);
        }
    }
    $o .= '</table>' . PHP_EOL . '</div>' . PHP_EOL;
    return $o;
}

/**
 * Returns the view of a form by instatiating the template.
 *
 * @param string $id A form ID.
 *
 * @return string (X)HTML.
 *
 * @global string The (X)HTML fragment for insertion into the HEAD element.
 * @global array  The configuration of the plugins.
 */
function Advancedform_templateView($id)
{
    global $hjs, $plugin_cf;

    $forms = Advancedform_db();
    $fn = Advancedform_dataFolder() . 'css/' . $id . '.css';
    if (file_exists($fn)) {
        $hjs .= tag(
            'link rel="stylesheet" href="' . $fn . '" type="text/css"'
        )
        . PHP_EOL;
    }
    $fn = Advancedform_dataFolder() . 'js/' . $id . '.js';
    if (file_exists($fn)) {
        $hjs .= '<script type="text/javascript" src="' . $fn . '"></script>'
            . PHP_EOL;
    }

    $form = $forms[$id];
    $fn = Advancedform_dataFolder() . $id . '.tpl'
        . ($plugin_cf['advancedform']['php_extension'] ? '.php' : '');
    $advfrm_script = file_get_contents($fn);
    foreach ($form['fields'] as $field) {
        $advfrm_script = str_replace(
            '<?field ' . $field['field'] . '?>',
            Advancedform_displayField($id, $field),
            $advfrm_script
        );
    }
    extract($GLOBALS);
    ob_start();
    eval('?>' . $advfrm_script);
    return ob_get_clean();
}

/**
 * Returns the view of the form.
 *
 * @param string $id A form ID.
 *
 * @return string (X)HTML.
 *
 * @global string The script name.
 * @global string The current page URL.
 * @global array  The paths of system files and folders.
 * @global array  The configuration of the plugins.
 * @global array  The localization of the plugins.
 */
function Advancedform_formView($id)
{
    global $sn, $su, $pth, $plugin_cf, $plugin_tx;

    $ptx = $plugin_tx['advancedform'];
    $pcf = $plugin_cf['advancedform'];

    $forms = Advancedform_db();
    $form = $forms[$id];
    Advancedform_initJQuery();
    $o = '';
    $o .= '<div class="advfrm-mailform">' . PHP_EOL
        . '<form name="' . $id . '" action="' . $sn . '?' . $su . '" method="post"'
        . ' enctype="multipart/form-data" accept-charset="UTF-8">' . PHP_EOL
        . tag('input type="hidden" name="advfrm" value="'.$id.'"') . PHP_EOL
        . '<div class="required">'
        . sprintf(
            $ptx['message_required_fields'],
            sprintf($pcf['required_field_mark'], $ptx['message_required_field'])
        )
        . '</div>' . PHP_EOL;
    if (file_exists(Advancedform_dataFolder() . $id . '.tpl')) {
        $o .= Advancedform_templateView($id);
    } else {
        $o .= Advancedform_defaultView($id);
    }
    if ($form['captcha']) {
        $o .= call_user_func($pcf['captcha_plugin'] . '_captcha_display');
    }
    $o .= '<div class="buttons">'
        . tag('input type="submit" class="submit" value="'.$ptx['button_send'].'"')
        . '&nbsp;'
        . tag('input type="reset" class="submit" value="'.$ptx['button_reset'].'"')
        . '</div>' . PHP_EOL;
    $o .= '</form>' . PHP_EOL . '</div>' . PHP_EOL;
    return $o;
}

/**
 * Checks sent form. Returns true on success, an (X)HTML error message on failure.
 *
 * @param string $id A form ID.
 *
 * @return mixed
 *
 * @global array The configuration of the plugins.
 * @global array The localization of the plugins.
 */
function Advancedform_check($id)
{
    global $plugin_cf, $plugin_tx;

    $pcf = $plugin_cf['advancedform'];
    $ptx = $plugin_tx['advancedform'];
    $o = '';
    $forms = Advancedform_db();
    $form = $forms[$id];
    foreach ($form['fields'] as $field) {
        $name = 'advfrm-' . $field['field'];
        if ($field['type'] != 'file' && $field['type'] != 'multi_select'
            && empty($_POST[$name])
            || $field['type'] == 'file' && empty($_FILES[$name]['name'])
            || $field['type'] == 'multi_select'
            && (!isset($_POST[$name])
            || count($_POST[$name]) == 1 && empty($_POST[$name][0]))
        ) {
            if ($field['required']) {
                $o .= '<li>'
                    . sprintf(
                        $ptx['error_missing_field'],
                        Advancedform_hsc($field['label'])
                    )
                    . '</li>' . PHP_EOL;
                Advancedform_focusField($id, $name);
            }
        } else {
            switch ($field['type']) {
            case 'from':
            case 'mail':
                if (!preg_match($pcf['mail_regexp'], stsl($_POST[$name]))) {
                    $o .= '<li>'
                        . sprintf(
                            $ptx['error_invalid_email'],
                            Advancedform_hsc($field['label'])
                        )
                        . '</li>' . PHP_EOL;
                    Advancedform_focusField($id, $name);
                }
                break;
            case 'date':
                $pattern = '/^([0-9]+)\\' . $ptx['date_delimiter']
                    . '([0-9]+)\\' . $ptx['date_delimiter'] . '([0-9]+)$/';
                $matched = preg_match($pattern, stsl($_POST[$name]), $matches);
                if (count($matches) == 4) {
                    $month = $matches[strpos($ptx['date_order'], 'm') + 1];
                    $day = $matches[strpos($ptx['date_order'], 'd') + 1];
                    $year = $matches[strpos($ptx['date_order'], 'y') + 1];
                }
                if (!$matched || !checkdate($month, $day, $year)) {
                    $o .= '<li>'
                        . sprintf(
                            $ptx['error_invalid_date'],
                            Advancedform_hsc($field['label'])
                        )
                        .'</li>' . PHP_EOL;
                    Advancedform_focusField($id, $name);
                }
                break;
            case 'number':
                if (!ctype_digit(stsl($_POST[$name]))) {
                    $o .= '<li>'
                        . sprintf(
                            $ptx['error_invalid_number'],
                            Advancedform_hsc($field['label'])
                        )
                        . '</li>' . PHP_EOL;
                    Advancedform_focusField($id, $name);
                }
                break;
            case 'file':
                $props = explode("\xC2\xA6", $field['props']);
                switch ($_FILES[$name]['error']) {
                case UPLOAD_ERR_OK:
                    if (!empty($props[ADVFRM_PROP_MAXLEN])
                        && $_FILES[$name]['size'] > $props[ADVFRM_PROP_MAXLEN]
                    ) {
                        $o .= '<li>'
                            . sprintf(
                                $ptx['error_upload_too_large'],
                                Advancedform_hsc($field['label'])
                            )
                            . '</li>' . PHP_EOL;
                        Advancedform_focusField($id, $name);
                    }
                    break;
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $o .= '<li>'
                        . sprintf(
                            $ptx['error_upload_too_large'],
                            Advancedform_hsc($field['label'])
                        )
                        . '</li>' . PHP_EOL;
                    Advancedform_focusField($id, $name);
                    break;
                default:
                    $o .= '<li>'
                        . sprintf(
                            $ptx['error_upload_general'],
                            Advancedform_hsc($field['label'])
                        )
                        . '</li>' . PHP_EOL;
                    Advancedform_focusField($id, $name);
                }
                $ext = pathinfo($_FILES[$name]['name'], PATHINFO_EXTENSION);
                if (!empty($props[ADVFRM_PROP_FTYPES])
                    && !in_array($ext, explode(',', $props[ADVFRM_PROP_FTYPES]))
                ) {
                    $o .= '<li>'
                        . sprintf(
                            $ptx['error_upload_illegal_ftype'],
                            Advancedform_hsc($field['label']),
                            Advancedform_hsc($ext)
                        )
                        . '</li>' . PHP_EOL;
                    Advancedform_focusField($id, $name);
                }
                break;
            case 'custom':
                $props = explode("\xC2\xA6", $field['props']);
                $pattern = $props[ADVFRM_PROP_CONSTRAINT];
                if (!empty($pattern)
                    && !preg_match($pattern, stsl($_POST[$name]))
                ) {
                    $msg = empty($props[ADVFRM_PROP_ERROR_MSG])
                        ? $ptx['error_invalid_custom']
                        : $props[ADVFRM_PROP_ERROR_MSG];
                    $o .= '<li>' . sprintf($msg, $field['label']) . '</li>'
                        . PHP_EOL;
                    Advancedform_focusField($id, $name);
                }
            }
            if (function_exists('advfrm_custom_valid_field')) {
                $value = $field['type'] == 'file'
                    ? $_FILES[$name]
                    : stsl($_POST[$name]);
                $valid = advfrm_custom_valid_field($id, $field['field'], $value);
                if ($valid !== true) {
                    $o .= '<li>' . $valid . '</li>' . PHP_EOL;
                    Advancedform_focusField($id, $name);
                }
            }
        }
    }
    if ($form['captcha']) {
        if (!call_user_func($pcf['captcha_plugin'] . '_captcha_check')) {
            $o .= '<li>' . $ptx['error_captcha_code'] . '</li>' . PHP_EOL;
            Advancedform_focusField($id, 'advancedform-captcha');
        }
    }
    return $o == ''
        ? true
        : '<ul class="advfrm-error">' . PHP_EOL . $o . '</ul>' . PHP_EOL;
}

/**
 * Sends the mail and returns whether that was successful.
 *
 * @param string $id           A form ID.
 * @param bool   $confirmation Whether to send the confirmation mail.
 *
 * @return bool
 *
 * @global array  The paths of system files and folders.
 * @global string The current language.
 * @global array  The configuration of the plugins.
 * @global array  The localization of the plugins.
 * @global string The (X)HTML fragment that contains error messages.
 */
function Advancedform_mail($id, $confirmation)
{
    global $pth, $sl, $plugin_cf, $plugin_tx, $e;

    include_once $pth['folder']['plugins']
        . 'advancedform/phpmailer/class.phpmailer.php';
    $pcf = $plugin_cf['advancedform'];
    $ptx = $plugin_tx['advancedform'];
    $forms = Advancedform_db();
    $form = $forms[$id];
    $type = strtolower($pcf['mail_type']);
    $from = '';
    $from_name = '';
    foreach ($form['fields'] as $field) {
        if ($field['type'] == 'from_name') {
            $from_name = stsl($_POST['advfrm-' . $field['field']]);
        } elseif ($field['type'] == 'from') {
            $from = stsl($_POST['advfrm-' . $field['field']]);
        }
    }
    if ($confirmation && empty($from)) {
        $e .= '<li>' . $ptx['error_missing_sender'] . '</li>' . PHP_EOL;
        return false;
    }

    $mail = new PHPMailer();
    $mail->LE = $pcf['mail_line_ending_*nix'] ? "\n" : "\r\n";
    $mail->set('CharSet', 'UTF-8');
    $mail->SetLanguage(
        $sl,
        $pth['folder']['plugins'] . 'advancedform/phpmailer/language/'
    );
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
            if (trim($cc) != '') {
                $mail->AddCC($cc);
            }
        }
        foreach (explode(';', $form['bcc']) as $bcc) {
            if (trim($bcc) != '') {
                $mail->AddBCC($bcc);
            }
        }
    }
    if ($confirmation) {
        $mail->set(
            'Subject',
            sprintf(
                $ptx['mail_subject_confirmation'], $form['title'],
                $_SERVER['SERVER_NAME']
            )
        );
    } else {
        $mail->set(
            'Subject',
            sprintf(
                $ptx['mail_subject'], $form['title'],
                $_SERVER['SERVER_NAME'], $_SERVER['REMOTE_ADDR']
            )
        );
    }
    $mail->IsHtml($type != 'text');
    if ($type == 'text') {
        $mail->set('Body', Advancedform_mailBody($id, !$confirmation, false));
    } else {
        $body = Advancedform_mailBody($id, !$confirmation, true);
        $mail->MsgHTML($body);
        $mail->set('AltBody', Advancedform_mailBody($id, !$confirmation, false));
    }
    if (!$confirmation) {
        foreach ($form['fields'] as $field) {
            if ($field['type'] == 'file') {
                $name = 'advfrm-' . $field['field'];
                $mail->AddAttachment(
                    $_FILES[$name]['tmp_name'],
                    stsl($_FILES[$name]['name'])
                );
            }
        }
    }

    if (function_exists('advfrm_custom_mail')) {
        if (advfrm_custom_mail($id, $mail, $confirmation) === false) {
            return true;
        }
    }

    $ok = $mail->Send();

    if (!$confirmation) {
        if (!$ok) {
            $message = !empty($mail->ErrorInfo)
                ? Advancedform_hsc($mail->ErrorInfo)
                : $ptx['error_mail'];
            $e .= '<li>' . $message . '</li>' . PHP_EOL;
        }
        if (function_exists('XH_logMessage')) {
            $type = $ok ? 'info' : 'error';
            $message = $ok ? $ptx['log_success'] : $ptx['log_error'];
            $message = sprintf($message, $from);
            XH_logMessage($type, 'Advancedform', $id, $message);
        }
    }

    return $ok;
}

/**
 * Main plugin call.
 *
 * @param string $id A form ID.
 *
 * @return string (X)HTML.
 *
 * @global array  The configuration of the plugins.
 * @global array  The localization of the plugins.
 * @global string The script name.
 * @global string The (X)HTML fragment containing error messages.
 * @global array  The paths of system files and folders.
 */
function Advancedform_main($id)
{
    global $plugin_cf, $plugin_tx, $sn, $e, $pth;

    $pcf = $plugin_cf['advancedform'];
    $ptx = $plugin_tx['advancedform'];

    $fn = $pth['folder']['plugins'] . $pcf['captcha_plugin'] . '/captcha.php';
    if (file_exists($fn)) {
        include_once $fn;
    } else {
        e('cntopen', 'file', $fn);
    }

    $hooks = Advancedform_dataFolder() . $id . '.inc'
        . ($pcf['php_extension'] ? '.php' : '');
    if (file_exists($hooks)) {
        include $hooks;
    }

    $forms = Advancedform_db();
    if (!isset($forms[$id])) {
        $e .= '<li>' . sprintf($ptx['error_form_missing'], $id) . '</li>' . PHP_EOL;
        return '';
    }
    $form = $forms[$id];
    if (isset($_POST['advfrm']) && $_POST['advfrm'] == $id) {
        if (($res = Advancedform_check($id)) === true) {
            if ($form['store']) {
                Advancedform_appendCsv($id);
            }
            if (!Advancedform_mail($id, false)) {
                return Advancedform_formView($id);
            }
            if (function_exists('advfrm_custom_thanks_page')) {
                Advancedform_fields($fields);
                $thanks = advfrm_custom_thanks_page($id, $fields);
            }
            if (empty($thanks)) {
                $thanks = $form['thanks_page'];
            }
            if (!empty($thanks)) {
                if (!Advancedform_mail($id, true)) {
                    return Advancedform_formView($id);
                }
                header('Location: ' . $sn . '?' . $thanks);
                // FIXME: exit()?
            } else {
                return Advancedform_mailInfo($id, false, true);
            }
        } else {
            return $res . Advancedform_formView($id);
        }
    }
    return Advancedform_formView($id);
}

?>

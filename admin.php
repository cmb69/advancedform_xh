<?php

/**
 * Administration of Advancedform_XH.
 *
 * PHP versions 4 and 5
 *
 * @category  CMSimple_XH
 * @package   Advancedform
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2005-2010 Jan Kanters
 * @copyright 2011-2014 Christoph M. Becker <http://3-magi.net>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://3-magi.net/?CMSimple_XH/Advancedform_XH
 */

/*
 * Prevent direct access.
 */
if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

/**
 * Compatibility functions.
 */
require_once $pth['folder']['plugins'] . 'advancedform/compat.php';

/**
 * The main functionality.
 */
require_once $pth['folder']['plugins'].'advancedform/advfrm.php';


/**
 * Returns the plugin version information.
 *
 * @return string (X)HTML
 *
 * @global array The paths of system files and folders.
 */
function Advancedform_version()
{
    global $pth;

    $o = '<h1><a href="http://3-magi.net/?CMSimple_XH/Advancedform_XH">'
        . 'Advancedform_XH</a></h1>' . PHP_EOL
        . tag(
            'img src="'.$pth['folder']['plugins'].'advancedform/advancedform.png"'
            . ' width="128" height="128" alt="Plugin icon"'
            . ' class="advancedform_plugin_icon"'
        ) . PHP_EOL
        . '<p>Version: ' . ADVANCEDFORM_VERSION . '</p>' . PHP_EOL
        . '<p>Copyright &copy; 2005-2010 Jan Kanters' . tag('br')
        . 'Copyright &copy; 2011-2014 <a href="http://3-magi.net">'
        . 'Christoph M. Becker</a></p>' . PHP_EOL
        . '<p>Advancedform_XH is powered by <a'
        . ' href="http://www.cmsimple-xh.org/wiki/doku.php/extend:jquery4cmsimple"'
        . ' target="_blank">jQuery4CMSimple</a>'
        . ' and <a href="http://phpmailer.worxware.com/" target="_blank">'
        . 'PHPMailer</a>.</p>' . PHP_EOL
        . '<p class="advancedform_license">This program is free software:'
        . ' you can redistribute it and/or modify'
        . ' it under the terms of the GNU General Public License as published by'
        . ' the Free Software Foundation, either version 3 of the License, or'
        . ' (at your option) any later version.</p>' . PHP_EOL
        . '<p class="advancedform_license">This program is distributed'
        . ' in the hope that it will be useful,'
        . ' but WITHOUT ANY WARRANTY; without even the implied warranty of'
        . ' MERCHAN&shy;TABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the'
        . ' GNU General Public License for more details.</p>' . PHP_EOL
        . '<p class="advancedform_license">You should have received'
        . ' a copy of the GNU General Public License along with this program.'
        . ' If not, see'
        . ' <a href="http://www.gnu.org/licenses/">http://www.gnu.org/licenses/</a>'
        . '</p>' . PHP_EOL;
    return $o;
}

/**
 * Returns requirements information.
 *
 * @return string (X)HTML
 *
 * @global array The paths of system files and folders.
 * @global array The configuration of the plugins.
 * @global array The localization of the core.
 * @global array The localization of the plugins.
 */
function Advancedform_systemCheck()
{
    global $pth, $plugin_cf, $tx, $plugin_tx;

    define('ADVFRM_PHP_VERSION', '4.3.10');
    $ptx = $plugin_tx['advancedform'];
    $imgdir = $pth['folder']['plugins'] . 'advancedform/images/';
    $ok = tag('img src="' . $imgdir . 'ok.png" alt="ok"');
    $warn = tag('img src="' . $imgdir . 'warn.png" alt="warning"');
    $fail = tag('img src="' . $imgdir . 'fail.png" alt="failure"');
    $o = tag('hr') . '<h4>' . $ptx['syscheck_title'] . '</h4>'
        . (version_compare(PHP_VERSION, ADVFRM_PHP_VERSION) >= 0 ? $ok : $fail)
        . '&nbsp;&nbsp;' . sprintf($ptx['syscheck_phpversion'], ADVFRM_PHP_VERSION)
        . tag('br') . tag('br') . PHP_EOL;
    foreach (array('ctype', 'mbstring', 'pcre', 'session') as $ext) {
        $o .= (extension_loaded($ext) ? $ok : $fail)
            . '&nbsp;&nbsp;' . sprintf($ptx['syscheck_extension'], $ext)
            . tag('br') . PHP_EOL;
    }
    $o .= tag('br') . (strtoupper($tx['meta']['codepage']) == 'UTF-8' ? $ok : $warn)
        . '&nbsp;&nbsp;' . $ptx['syscheck_encoding'] . tag('br') . PHP_EOL;
    $o .= (!get_magic_quotes_runtime() ? $ok : $warn)
        . '&nbsp;&nbsp;' . $ptx['syscheck_magic_quotes'] . tag('br') . PHP_EOL;
    $filename = $pth['folder']['plugins'] . 'jquery/jquery.inc.php';
    $o .= (file_exists($filename) ? $ok : $fail)
        . '&nbsp;&nbsp;' . $ptx['syscheck_jquery'] . tag('br') . PHP_EOL;
    $filename = $pth['folder']['plugins']
        . $plugin_cf['advancedform']['captcha_plugin'] . '/captcha.php';
    $o .= (file_exists($filename) ? $ok : $warn)
        . '&nbsp;&nbsp;' . $ptx['syscheck_captcha_plugin']
        . tag('br') . tag('br') . PHP_EOL;
    foreach (array('config/', 'css/', 'languages/') as $folder) {
        $folders[] = $pth['folder']['plugins'] . 'advancedform/' . $folder;
    }
    $folders[] = Advancedform_dataFolder();
    foreach ($folders as $folder) {
        $o .= (is_writable($folder) ? $ok : $warn)
            . '&nbsp;&nbsp;' . sprintf($ptx['syscheck_writable'], $folder)
            . tag('br') . PHP_EOL;
    }
    return $o;
}

/**
 * Returns the IMG element for the tool $name.
 *
 * @param string $name A tool's name.
 *
 * @return string (X)HTML.
 *
 * @global array The paths of system files and folders.
 * @global array The localization of the plugins.
 */
function Advancedform_toolIcon($name)
{
    global $pth, $plugin_tx;

    $ptx = $plugin_tx['advancedform'];

    $src = $pth['folder']['plugins'] . 'advancedform/images/' . $name . '.gif';
    $title = $ptx['tool_'.$name];
    return tag(
        'img src="' . $src . '" alt="' . $title . '" title="' . $title . '"'
    );
}

/**
 * Returns a tool form.
 *
 * @param string $name     A tool name.
 * @param string $action   A URL.
 * @param string $onsubmit An onsubmit event handler.
 *
 * @return string (X)HTML.
 */
function Advancedform_toolForm($name, $action, $onsubmit = false)
{
    global $_XH_csrfProtection;

    $onsubmit = $onsubmit ? 'onsubmit="' . $onsubmit . '"' : '';
    $icon = Advancedform_toolIcon($name);
    if (isset($_XH_csrfProtection)) {
        $tokenInput = $_XH_csrfProtection->tokenInput();
    } else {
        $tokenInput = '';
    }
    return <<<EOT
<form action="$action" method="post" $onsubmit>
    <button>$icon</button>
    $tokenInput
</form>
EOT;
}

/**
 * Returns a selectbox with all  pages of the current language/subsite.
 *
 * @param string $name     Name and id of the select.
 * @param string $selected URL of the thanks page.
 *
 * @return string (X)HTML.
 *
 * @global int    The number of pages.
 * @global array  The page headings.
 * @global array  The page URLs.
 * @global array  The page levels.
 * @global string The script name.
 * @global array  The localization of the plugins.
 */
function Advancedform_pageSelect($name, $selected)
{
    global $cl, $h, $u, $l, $sn, $plugin_tx;

    $ptx = $plugin_tx['advancedform'];
    $o = '<select id="' . $name . '" name="' . $name . '">' . PHP_EOL;
    $sel = ($selected == '') ? ' selected="selected"' : '';
    $o .= '<option value=""' . $sel . '>' . $ptx['label_none'] . '</option>'
        . PHP_EOL;
    for ($i = 0; $i < $cl; $i++) {
        $sel = ($u[$i] == $selected) ? ' selected="selected"' : '';
        $o .= '<option value="' . $u[$i] . '"' . $sel . '>'
            . str_repeat('&nbsp;&nbsp;', $l[$i] - 1) . $h[$i] . '</option>'
            . PHP_EOL;
    }
    $o .= '</select>' . PHP_EOL;
    return $o;
}

/**
 * Returns the mail forms administration.
 *
 * @return string (X)HTML.
 *
 * @global string The script name.
 * @global array  The localization of the core.
 * @global array  The localization of the plugins.
 */
function Advancedform_formsAdministration()
{
    global $sn, $tx, $plugin_tx;

    $ptx = $plugin_tx['advancedform'];

    $forms = Advancedform_db();
    $o = '<div id="advfrm-form-list">' . PHP_EOL
        .'<h1>' . $ptx['menu_main'] . '</h1>' . PHP_EOL;
    $href = $sn . '?advancedform&amp;admin=plugin_main&amp;action=new';
    $o .= Advancedform_toolForm('add', $href);
    $href = $sn . '?advancedform&amp;admin=plugin_main&amp;action=import&amp;form=';
    $o .= Advancedform_toolForm('import', $href, 'return advfrm_import(this)');
    $o .= '<table>' . PHP_EOL;
    foreach ($forms as $id => $form) {
        if ($id != '%VERSION%') {
            $href = $sn . '?advancedform&amp;admin=plugin_main&amp;action=%s'
                . '&amp;form=' . $id;
            $o .= '<tr>'
                . '<td class="tool">'
                . Advancedform_toolForm(
                    'delete', sprintf($href, 'delete'),
                    'return confirm(\'' . Advancedform_escapeJsString(
                        $ptx['message_confirm_delete']
                    ) . '\')'
                )
                . '</td>'
                . '<td class="tool">'
                . Advancedform_toolForm(
                    'template', sprintf($href, 'template'),
                    'return confirm(\'' . Advancedform_escapeJsString(
                        sprintf($ptx['message_confirm_template'], $form['name'])
                    ) . '\')'
                )
                . '</td>'
                . '<td class="tool">'
                . Advancedform_toolForm(
                    'copy',  sprintf($href, 'copy')
                )
                . '</td>'
                . '<td class="tool">'
                . Advancedform_toolForm(
                    'export', sprintf($href, 'export'),
                    'return confirm(\'' . Advancedform_escapeJsString(
                        sprintf($ptx['message_confirm_export'], $form['name'])
                    ) . '\')'
                )
                . '</td>'
                . '<td class="name"><a href="' . sprintf($href, 'edit') . '" title="'
                . ucfirst($tx['action']['edit']) . '">' . $id . '</a></td>'
                . '<td class="script" title="' . $ptx['message_script_code'] . '">'
                . '{{{PLUGIN:advancedform(\'' . $id . '\');}}}</td>'
                . '</tr>' . PHP_EOL;
        }
    }
    $o .= '</table>' . PHP_EOL;
    $o .= '</div>' . PHP_EOL;
    return $o;
}

/**
 * Creates a new mail form and returns the form editor.
 *
 * @return string (X)HTML.
 *
 * @global array  The configuration of the plugins.
 * @global object The CSRF protector.
 */
function Advancedform_createForm()
{
    global $plugin_cf, $_XH_csrfProtection;

    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        return Advancedform_formsAdministration();
    }
    if (isset($_XH_csrfProtection)) {
        $_XH_csrfProtection->check();
    }
    $pcf = $plugin_cf['advancedform'];
    $forms = Advancedform_db();
    $id = uniqid();
    $forms[$id] = array(
        'name' => '',
        'title' => '',
        'to_name' => $pcf['mail_to_name'],
        'to' => $pcf['mail_to'],
        'cc' => $pcf['mail_cc'],
        'bcc' => $pcf['mail_bcc'],
        'captcha' => (bool) $pcf['mail_captcha'],
        'store' => false,
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
    Advancedform_db($forms);
    return Advancedform_editForm($id);
}

/**
 * Returns the form editor.
 *
 * @param string $id A form ID.
 *
 * @return string (X)HTML.
 *
 * @global array  The paths of system files and folders.
 * @global string The script name.
 * @global array  The configuration of the plugins.
 * @global array  The localization of the core.
 * @global array  The localization of the plugins.
 * @global string The (X)HTML fragment containing error messages.
 * @global object The CSRF protector.
 */
function Advancedform_editForm($id)
{
    global $pth, $sn, $plugin_cf, $tx, $plugin_tx, $e, $_XH_csrfProtection;

    $pcf = $plugin_cf['advancedform'];
    $ptx = $plugin_tx['advancedform'];

    $forms = Advancedform_db();
    $form = $forms[$id];
    if (!isset($form)) {
        $e .= '<li><b>'
            . sprintf($plugin_tx['advancedform']['error_form_missing'], $id)
            . '</b></li>';
        return Advancedform_formsAdministration();
    }

    /*
     * general settings
     */
    $o = '<div id="advfrm-editor">' . PHP_EOL . '<h1>' . $id . '</h1>' . PHP_EOL;
    $action = $sn
        . '?advancedform&amp;admin=plugin_main&amp;action=save&amp;form=' . $id;
    $o .= '<form action="' . $action . '" method="post" accept-charset="UTF-8"'
        . ' onsubmit="return advfrm_checkForm()">' . PHP_EOL;
    $o .= '<table id="advfrm-form">' . PHP_EOL;
    $fields = array(
        'name', 'title', 'to_name', 'to', 'cc', 'bcc', 'captcha', 'store',
        'thanks_page'
    );
    foreach ($fields as $det) {
        $name = 'advfrm-' . $det;
        $o .= '<tr>'
            . '<td><label for="' . $name . '">' . $ptx['label_'.$det]
            . '</label></td>';
        switch ($det) {
        case 'captcha':
        case 'store':
            $checked = $form[$det] ? ' checked="checked"' : '';
            $o .= '<td>'
                . tag(
                    'input type="checkbox" id="' . $name . '" name="' . $name . '"'
                    . $checked
                )
                . '</td>';
            break;
        case 'thanks_page':
            $o .= '<td>' . Advancedform_pageSelect($name, $form[$det]) . '</td>';
            break;
        default:
            $o .= '<td>'
                . tag(
                    'input type="text" id="' . $name . '" name="' . $name . '"'
                    . ' value="' . Advancedform_hsc($form[$det]) . '" size="40"'
                )
                . '</td>';
        }
        $o .= '</tr>' . PHP_EOL;
    }
    $o .= '</table>' . PHP_EOL;

    /*
     * field settings
     */
    $o .= '<div class="toolbar">';
    foreach (array('add', 'delete', 'up', 'down') as $tool) {
        $o .=  '<a onclick="advfrm_' . $tool . '(\'advfrm-fields\')">'
            . Advancedform_toolIcon($tool) . '</a>' . PHP_EOL;
    }
    $o .= '</div>' . PHP_EOL;

    $o .= '<table id="advfrm-fields">' . PHP_EOL;
    $o .= '<thead><tr>'
        . '<th>' . $ptx['label_field'] . '</th>'
        . '<th>' . $ptx['label_label'] . '</th>'
        . '<th colspan="3">' . $ptx['label_type'] . '</th>'
        . '<th>' . $ptx['label_required'] . '</th>'
        . '</tr></thead>' . PHP_EOL;
    foreach ($form['fields'] as $num => $field) {
        $o .= '<tr>'
            . '<td>'
            . tag(
                'input type="text" size="10" name="advfrm-field[]"'
                . ' value="' . $field['field'] . '" class="highlightable"'
            )
            . '</td>'
            . '<td>'
            . tag(
                'input type="text" size="10" name="advfrm-label[]" value="'
                . Advancedform_hsc($field['label']) . '" class="highlightable"'
            )
            . '</td>'
            . '<td><select name="advfrm-type[]" onfocus="this.oldvalue = this.value"'
            . ' class="highlightable">';
        $types = array(
            'text', 'from_name', 'from', 'mail', 'date', 'number', 'textarea',
            'radio', 'checkbox', 'select', 'multi_select', 'password', 'file',
            'hidden', 'output', 'custom'
        );
        foreach ($types as $type) {
            $sel = ($field['type'] == $type) ? ' selected="selected"' : '';
            $o .= '<option value="' . $type . '"' . $sel . '>'
                . $ptx['field_' . $type] . '</option>';
        }
        $o .= '</select></td>'
            . '<td>'
            . tag(
                'input type="hidden" class="hidden" name="advfrm-props[]"'
                . ' value="' . Advancedform_hsc($field['props']) . '"'
            )
            . '<td><a>' . Advancedform_toolIcon('props') . '</a>' . PHP_EOL;
        $checked = $field['required'] ? ' checked="checked"' : '';
        $o .= '<td>'
            . tag(
                'input type="checkbox"' . $checked . ' onchange="this.'
                . 'nextSibling.value = this.checked ? 1 : 0"'
            )
            . tag(
                'input type="hidden" name="advfrm-required[]" value="'
                . $field['required'] . '"'
            )
            . '</td>'
            . '</tr>' . PHP_EOL;
    }
    $o .= '</table>' . PHP_EOL;
    $o .= tag(
        'input type="submit" class="submit" value="'
        . ucfirst($tx['action']['save']) . '" style="display:none"'
    );
    if (isset($_XH_csrfProtection)) {
        $o .= $_XH_csrfProtection->tokenInput();
    }
    $o .= '</form>' . PHP_EOL . '</div>' . PHP_EOL;

    /*
     * property dialogs
     */
    $o .= '<div id="advfrm-text-props" style="display:none">' . PHP_EOL
        . '<table>' . PHP_EOL;
    $properties = array('size', 'maxlength', 'default', 'constraint', 'error_msg');
    foreach ($properties as $prop) {
        $o .= '<tr id="advfrm-text-props-' . $prop . '"><td>' . $prop . '</td>'
            . '<td>' . tag('input type="text" size="30"') . '</td></tr>'
            . PHP_EOL;
    }
    $o .= '</table>' . PHP_EOL . '</div>' . PHP_EOL;

    $o .= '<div id="advfrm-select-props" style="display:none">' .  PHP_EOL;
    $o .= '<p id="advfrm-select-props-size">' . $ptx['label_size'] . ' '
        . tag('input type="text"') . '</p>' . PHP_EOL;
    $o .= '<p id="advfrm-select-props-orient">'
        . tag(
            'input type="radio" id="advrm-select-props-orient-horz"'
            . ' name="advrm-select-props-orient"'
        )
        . '<label for="advrm-select-props-orient-horz">&nbsp;'
        . $ptx['label_horizontal'] . '</label>&nbsp;&nbsp;&nbsp;'
        . tag(
            'input type="radio" id="advrm-select-props-orient-vert"'
            . ' name="advrm-select-props-orient"'
        )
        . '<label for="advrm-select-props-orient-vert">&nbsp;'
        . $ptx['label_vertical'] . '</label>'
        . '</p>' . PHP_EOL;
    $o .= '<div class="toolbar">';
    foreach (array('add', 'delete', 'up', 'down', 'clear_defaults') as $tool) {
        $o .=  '<a onclick="advfrm_' . $tool . '(\'advfrm-prop-fields\')">'
            . Advancedform_toolIcon($tool) . '</a>' . PHP_EOL;
    }
    $o .= '</div>' . PHP_EOL;
    $o .= '<table id="advfrm-prop-fields">' . PHP_EOL . '<tr>'
        . '<td>'
        . tag('input type="radio" name="advfrm-select-props-default"')
        . '</td>'
        . '<td>'
        . tag(
            'input type="text" name="advfrm-select-props-opt" size="25"'
            . ' class="highlightable"'
        )
        . '</td>'
        . '</tr>' . PHP_EOL . '</table>' . PHP_EOL . '</div>' . PHP_EOL;

    return $o;
}

/**
 * Saves the modified mail form definition. Returns the the mail form list on
 * success, or the mail form editor on failure.
 *
 * @param string $id A form ID.
 *
 * @return string (X)HTML.
 *
 * @global string The (X)HTML fragments containing error messages.
 * @global array  The localization of the plugins.
 * @global object The CSRF protector.
 */
function Advancedform_saveForm($id)
{
    global $e, $plugin_tx, $_XH_csrfProtection;

    $ptx = $plugin_tx['advancedform'];

    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        return Advancedform_formsAdministration();
    }
    if (isset($_XH_csrfProtection)) {
        $_XH_csrfProtection->check();
    }
    $forms = Advancedform_db();
    if (!isset($forms[$id])) {
        $e .= '<li><b>' . sprintf($ptx['error_form_missing'], $id) . '</b></li>';
        return Advancedform_formsAdministration();
    }
    unset($forms[$id]);
    if (!isset($forms[$_POST['advfrm-name']])) {
        $id = $_POST['advfrm-name'];
        $ok = true;
    } else {
        $_POST['advfrm-name'] = $id;
        $e .= '<li>' . $ptx['error_form_exists'] . '</li>';
        $ok = false;
    }
    $forms[$id]['captcha'] = false;
    $forms[$id]['store'] = false;
    foreach ($_POST as $key => $val) {
        $keys = explode('-', $key);
        if ($keys[0] == 'advfrm') {
            if (!is_array($val)) {
                if (in_array($keys[1], array('captcha', 'store'))) {
                    $forms[$id][$keys[1]] = true;
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
    Advancedform_db($forms);
    return $ok ? Advancedform_formsAdministration() : Advancedform_editForm($id);
}

/**
 * Deletes a form, and returns the mail form list.
 *
 * @param string $id A form ID.
 *
 * @return string (X)HTML.
 *
 * @global string The (X)HTML fragment containing error messages.
 * @global array  The localization of the plugins.
 * @global object The CSRF protector.
 */
function Advancedform_deleteForm($id)
{
    global $e, $plugin_tx, $_XH_csrfProtection;

    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        return Advancedform_formsAdministration();
    }
    if (isset($_XH_csrfProtection)) {
        $_XH_csrfProtection->check();
    }
    $forms = Advancedform_db();
    if (isset($forms[$id])) {
        unset($forms[$id]);
        Advancedform_db($forms);
    } else {
        $e .= '<li><b>'
            . sprintf($plugin_tx['advancedform']['error_form_missing'], $id)
            . '</b></li>';
    }
    return Advancedform_formsAdministration();
}

/**
 * Makes a copy of form $id. Returns the mail form editor.
 *
 * @param string $id A form ID.
 *
 * @return string (X)HTML.
 *
 * @global string The (X)HTML fragment containing error messages.
 * @global array  The localization of the plugins.
 * @global object The CSRF protector.
 */
function Advancedform_copyForm($id)
{
    global $e, $plugin_tx, $_XH_csrfProtection;

    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        return Advancedform_formsAdministration();
    }
    if (isset($_XH_csrfProtection)) {
        $_XH_csrfProtection->check();
    }
    $forms = Advancedform_db();
    if (isset($forms[$id])) {
        $form = $forms[$id];
        $form['name'] = '';
        $id = uniqid();
        $forms[$id] = $form;
        Advancedform_db($forms);
    } else {
        $e .= '<li><b>'
            . sprintf($plugin_tx['advancedform']['error_form_missing'], $id)
            . '</b></li>';
    }
    return Advancedform_editForm($id);
}

/**
 * Imports the form definition from a *.frm file. Returns the mail form
 * administration.
 *
 * @param string $id A form ID.
 *
 * @return string (X)HTML.
 *
 * @global array  The localization of the plugins.
 * @global string The (X)HTML fragment containing error messages.
 * @global object The CSRF protector.
 */
function Advancedform_importForm($id)
{
    global $plugin_tx, $e, $_XH_csrfProtection;

    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        return Advancedform_formsAdministration();
    }
    if (isset($_XH_csrfProtection)) {
        $_XH_csrfProtection->check();
    }
    $ptx = $plugin_tx['advancedform'];
    $forms = Advancedform_db();
    if (!isset($forms[$id])) {
        $fn = Advancedform_dataFolder() . $id . '.frm';
        if (($cnt = file_get_contents($fn)) !== false
            && ($form = unserialize($cnt)) !== false
            && isset($form['%VERSION%'])
            && count($form) == 2
        ) {
            if ($form['%VERSION%'] < ADVFRM_DB_VERSION) {
                $form = Advancedform_updatedDb($form);
            }
            unset($form['%VERSION%']);
            foreach ($form as $f) {
                $f['name'] = $id;
                $forms[$id] = $f;
            }
            Advancedform_db($forms);
        } else {
            e('cntopen', 'file', $fn);
        }
    } else {
        $e .= '<li><b>' . $ptx['error_form_exists'] . '</b></li>';
    }
    return Advancedform_formsAdministration();
}

/**
 * Exports the form definition to a *.frm file. Returns the mail form administration.
 *
 * @param string $id A form ID.
 *
 * @return string (X)HTML.
 *
 * @global string The (X)HTML fragment containing error messages.
 * @global array  The localization of the plugins.
 * @global object The CSRF protector.
 */
function Advancedform_exportForm($id)
{
    global $e, $plugin_tx, $_XH_csrfProtection;

    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        return Advancedform_formsAdministration();
    }
    if (isset($_XH_csrfProtection)) {
        $_XH_csrfProtection->check();
    }
    $ptx = $plugin_tx['advancedform'];
    $forms = Advancedform_db();
    if (isset($forms[$id])) {
        $form[$id] = $forms[$id];
        $form['%VERSION%'] = ADVFRM_DB_VERSION;
        $fn = Advancedform_dataFolder() . $id . '.frm';
        if (!($fh = fopen($fn, 'w')) || fwrite($fh, serialize($form)) === false) {
            e('cntwriteto', 'file', $fn);
        }
        if ($fh) {
            fclose($fh);
        }
    } else {
        $e .= '<li><b>' . sprintf($ptx['error_form_missing'], $id) . '</b></li>';
    }
    return Advancedform_formsAdministration();
}

/**
 * Creates a basic template of the form. Returns the the mail form administration.
 *
 * @param string $id A form ID.
 *
 * @return string (X)HTML.
 *
 * @global array  The configuration of the plugins.
 * @global object The CSRF protector.
 */
function Advancedform_createFormTemplate($id)
{
    global $plugin_cf, $_XH_csrfProtection;

    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        return Advancedform_formsAdministration();
    }
    if (isset($_XH_csrfProtection)) {
        $_XH_csrfProtection->check();
    }
    $forms = Advancedform_db();
    if (isset($forms[$id])) {
        $form = $forms[$id];
        $tpl = '<div id="advfrm-' . $id . '">' . PHP_EOL;
        $css = '#advfrm-' . $id . ' {}' . PHP_EOL . PHP_EOL
            . '#advfrm-' . $id . ' div.break {clear: both}' . PHP_EOL . PHP_EOL
            . '#advfrm-' . $id . ' div.float {float: left; margin-right: 1em}'
            . PHP_EOL . PHP_EOL
            . '#advfrm-' . $id . ' div.label'
            . ' {/* float: left; width: 12em; margin-bottom: 0.5em; */}' . PHP_EOL
            . '#advfrm-' . $id . ' div.field '
            . ' { margin-bottom: 0.5em; /* float: left;*/}' . PHP_EOL . PHP_EOL
            . '/* the individual fields */' . PHP_EOL . PHP_EOL;
        $first = true;
        foreach ($form['fields'] as $field) {
            if ($first) {
                $tpl .= '  <?php Advancedform_focusField(\'' . $id . '\', \'advfrm-'
                    . $field['field'] . '\')'
                    . ' // focus the first field?>' . PHP_EOL;
                $first = false;
            }
            $labelled = !in_array(
                $field['type'], array('checkbox', 'radio', 'hidden')
            );
            if (in_array($field['type'], array('hidden'))) {
                $label = '';
            } elseif (!$field['required']) {
                $label = $field['label'];
            } else {
                $label = sprintf(
                    $plugin_cf['advancedform']['required_field_mark'],
                    $field['label']
                );
            }
            if ($labelled) {
                $label = '<label for="advfrm-' . $id . '-' . $field['field'] . '">'
                    . $label . '</label>';
            }
            $tpl .= '  <div class="break">' . PHP_EOL
                . '    <div class="label">'
                . $label
                . '</div>' . PHP_EOL
                . '    <div class="field"><?field ' . $field['field'] . '?></div>'
                . PHP_EOL
                . '  </div>' . PHP_EOL;
            $css .= '#advfrm-' . $id . '-' . $field['field'] . ' {}' . PHP_EOL;
        }
        $tpl .= '  <div class="break"></div>' . PHP_EOL . '</div>' . PHP_EOL;
        $fn = Advancedform_dataFolder() . $id . '.tpl';
        if (!($fh = fopen($fn, 'w')) || fwrite($fh, $tpl) === false) {
            e('cntsave', 'file', $fn);
        }
        if ($fh) {
            fclose($fh);
        }
        $fn = Advancedform_dataFolder() . 'css/' . $id . '.css';
        if (!($fh = fopen($fn, 'w')) || fwrite($fh, $css) === false) {
            e('cntsave', 'file', $fn);
        }
        if ($fh) {
            fclose($fh);
        }
    } else {
        $e .= '<li><b>' . sprintf($ptx['error_form_missing'], $id) . '</b></li>';
    }
    return Advancedform_formsAdministration();
}

/*
 * Register the plugin menu items.
 */
if (function_exists('XH_registerStandardPluginMenuItems')) {
    XH_registerStandardPluginMenuItems(true);
}

/*
 * Handle the plugin administration.
 */
if (function_exists('XH_wantsPluginAdministration') && XH_wantsPluginAdministration('advancedform')
    || isset($advancedform) && $advancedform == 'true') {
    if (include_once $pth['folder']['plugins'] . 'jquery/jquery.inc.php') {
        include_jQuery();
        include_jQueryUI();
    }
    if (Advancedform_updateLangJs()) {
        $hjs .= PHP_EOL . '<script type="text/javascript" src="'
            . $pth['folder']['plugins'] . 'advancedform/languages/' . $sl . '.js">'
            . '</script>' . PHP_EOL;
    }
    $hjs .= '<script type="text/javascript" src="' . $pth['folder']['plugins']
        . 'advancedform/admin.js"></script>' . PHP_EOL;

    $o .= print_plugin_admin('on');
    switch ($admin) {
    case '':
        $o .= Advancedform_version() . Advancedform_systemCheck();
        break;
    case 'plugin_main':
        switch ($action) {
        case 'new':
            $o .= Advancedform_createForm();
            break;
        case 'edit':
            $o .= Advancedform_editForm($_GET['form']);
            break;
        case 'save':
            $o .= Advancedform_saveForm($_GET['form']);
            break;
        case 'delete':
            $o .= Advancedform_deleteForm($_GET['form']);
            break;
        case 'copy':
            $o .= Advancedform_copyForm($_GET['form']);
            break;
        case 'import':
            $o .= Advancedform_importForm($_GET['form']);
            break;
        case 'export':
            $o .= Advancedform_exportForm($_GET['form']);
            break;
        case 'template':
            $o .= Advancedform_createFormTemplate($_GET['form']);
            break;
        default:
            $o .= Advancedform_formsAdministration();
        }
        break;
    default:
        $o .= plugin_admin_common($action, $admin, $plugin);
    }
}

?>

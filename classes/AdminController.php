<?php

/**
 * Copyright 2005-2010 Jan Kanters
 * Copyright 2011-2021 Christoph M. Becker
 *
 * This file is part of Advancedform_XH.
 *
 * Advancedform_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Advancedform_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Advancedform_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Advancedform;

use XH\Pages;
use Fa\RequireCommand as FaRequireCommand;

class AdminController extends Controller
{
    /**
     * @var object
     */
    private $csrfProtector;

    public function __construct()
    {
        global $_XH_csrfProtection;

        parent::__construct();
        $this->csrfProtector = $_XH_csrfProtection;
    }
    /**
     * @return string
     */
    public function infoAction()
    {
        return $this->version() . $this->systemCheck();
    }

    /**
     * Returns the plugin version information.
     *
     * @return string (X)HTML
     */
    private function version()
    {
        return '<h1>Advancedform</h1>' . PHP_EOL
            . PHP_EOL
            . '<p>Version: ' . ADVANCEDFORM_VERSION . '</p>' . PHP_EOL;
    }

    /**
     * Returns requirements information.
     *
     * @return string (X)HTML
     */
    private function systemCheck()
    {
        (new FaRequireCommand)->execute();
        $ok = '<span class="fa fa-check" title="ok"></span>';
        $warn = '<span class="fa fa-exclamation" title="warning"></span>';
        $fail = '<span class="fa fa-exclamation-triangle" title="failure"></span>';
        $phpversion = '5.5.0';
        $o = '<hr>' . '<h4>' . $this->text['syscheck_title'] . '</h4>'
            . (version_compare(PHP_VERSION, $phpversion) >= 0 ? $ok : $fail)
            . '&nbsp;&nbsp;' . sprintf($this->text['syscheck_phpversion'], $phpversion)
            . '<br>' . '<br>' . PHP_EOL;
        foreach (array('ctype', 'mbstring', 'session') as $ext) {
            $o .= (extension_loaded($ext) ? $ok : $fail)
                . '&nbsp;&nbsp;' . sprintf($this->text['syscheck_extension'], $ext)
                . '<br>' . PHP_EOL;
        }
        $o .= '<br>';
        $filename = $this->pluginsFolder . 'jquery/jquery.inc.php';
        $o .= (file_exists($filename) ? $ok : $fail)
            . '&nbsp;&nbsp;' . $this->text['syscheck_jquery'] . '<br>' . PHP_EOL;
        $filename = $this->pluginsFolder
            . $this->conf['captcha_plugin'] . '/captcha.php';
        $o .= (file_exists($filename) ? $ok : $warn)
            . '&nbsp;&nbsp;' . $this->text['syscheck_captcha_plugin']
            . '<br>' . '<br>' . PHP_EOL;
        foreach (array('config/', 'css/', 'languages/') as $folder) {
            $folders[] = $this->pluginsFolder . 'advancedform/' . $folder;
        }
        $folders[] = Functions::dataFolder();
        foreach ($folders as $folder) {
            $o .= (is_writable($folder) ? $ok : $warn)
                . '&nbsp;&nbsp;' . sprintf($this->text['syscheck_writable'], $folder)
                . '<br>' . PHP_EOL;
        }
        return $o;
    }

    /**
     * Returns the mail forms administration.
     *
     * @return string (X)HTML.
     */
    public function formsAdministrationAction()
    {
        global $tx;

        (new FaRequireCommand)->execute();
        $forms = Functions::database();
        $o = '<div id="advfrm-form-list">' . PHP_EOL
            .'<h1>Advancedform – ' . $this->text['menu_main'] . '</h1>' . PHP_EOL;
        $href = $this->scriptName . '?advancedform&amp;admin=plugin_main&amp;action=new';
        $o .= $this->toolForm('add', $href);
        $href = $this->scriptName . '?advancedform&amp;admin=plugin_main&amp;action=import&amp;form=';
        $o .= $this->toolForm('import', $href, 'return advfrm_import(this)');
        $o .= '<table>' . PHP_EOL;
        foreach ($forms as $id => $form) {
            if ($id != '%VERSION%') {
                $href = $this->scriptName . '?advancedform&amp;admin=plugin_main&amp;action=%s'
                    . '&amp;form=' . $id;
                $o .= '<tr>'
                    . '<td class="tool">'
                    . $this->toolForm(
                        'delete',
                        sprintf($href, 'delete'),
                        'return confirm(\''
                        . $this->escapeJsString($this->text['message_confirm_delete'])
                        . '\')'
                    )
                    . '</td>'
                    . '<td class="tool">'
                    . $this->toolForm(
                        'template',
                        sprintf($href, 'template'),
                        'return confirm(\''
                        . $this->escapeJsString(
                            sprintf($this->text['message_confirm_template'], $form->getName())
                        )
                        . '\')'
                    )
                    . '</td>'
                    . '<td class="tool">'
                    . $this->toolForm('copy', sprintf($href, 'copy'))
                    . '</td>'
                    . '<td class="tool">'
                    . $this->toolForm(
                        'export',
                        sprintf($href, 'export'),
                        'return confirm(\''
                        . $this->escapeJsString(
                            sprintf($this->text['message_confirm_export'], $form->getName())
                        )
                        . '\')'
                    )
                    . '</td>'
                    . '<td class="name"><a href="' . sprintf($href, 'edit') . '" title="'
                    . utf8_ucfirst($tx['action']['edit']) . '">' . $id . '</a></td>'
                    . '<td class="script" title="' . $this->text['message_script_code'] . '">'
                    . '<input type="text" readonly onclick="this.select()" value="'
                    . '{{{advancedform(\'' . $id . '\')}}}"></input></td>'
                    . '</tr>' . PHP_EOL;
            }
        }
        $o .= '</table>' . PHP_EOL;
        $o .= '</div>' . PHP_EOL;
        return $o;
    }

    /**
     * Escapes a JS string.
     *
     * @param string $string A string.
     *
     * @return string
     */
    private function escapeJsString($string)
    {
        return addcslashes($string, "\t\n\r\"\'\\");
    }

    /**
     * Creates a new mail form and returns the form editor.
     *
     * @return string (X)HTML.
     */
    public function createFormAction()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return $this->formsAdministrationAction();
        }
        $this->csrfProtector->check();
        $forms = Functions::database();
        $id = uniqid();
        $forms[$id] = Form::createFromArray(array(
            'name' => '',
            'title' => '',
            'to_name' => $this->conf['mail_to_name'],
            'to' => $this->conf['mail_to'],
            'cc' => $this->conf['mail_cc'],
            'bcc' => $this->conf['mail_bcc'],
            'captcha' => (bool) $this->conf['mail_captcha'],
            'store' => false,
            'thanks_page' => $this->conf['mail_thanks_page'],
            'fields' => array(
                array(
                    'field' => '',
                    'label' => '',
                    'type' => 'text',
                    'props' => "\xC2\xA6\xC2\xA6\xC2\xA6",
                    'required' => '0'
                )
            )
        ));
        Functions::database($forms);
        return $this->editFormAction($id);
    }

    /**
     * Returns the form editor.
     *
     * @param string $id A form ID.
     *
     * @return string (X)HTML.
     */
    public function editFormAction($id)
    {
        global $tx, $e;

        (new FaRequireCommand)->execute();
        $forms = Functions::database();
        $form = $forms[$id];
        if (!isset($form)) {
            $e .= '<li><b>'
                . sprintf($this->text['error_form_missing'], $id)
                . '</b></li>';
            return $this->formsAdministrationAction();
        }

        /*
        * general settings
        */
        $o = '<div id="advfrm-editor">' . PHP_EOL . '<h1>' . $id . '</h1>' . PHP_EOL;
        $action = $this->scriptName
            . '?advancedform&amp;admin=plugin_main&amp;action=save&amp;form=' . $id;
        $o .= '<form action="' . $action . '" method="post" accept-charset="UTF-8"'
            . ' onsubmit="return advfrm_checkForm()">' . PHP_EOL
            . $this->renderEditFormTable($form);

        /*
        * field settings
        */
        $o .= '<div class="toolbar">';
        foreach (array('add', 'delete', 'up', 'down') as $tool) {
            $o .=  '<button type="button" onclick="advfrm_' . $tool . '(\'advfrm-fields\')">'
                . $this->toolIcon($tool) . '</button>' . PHP_EOL;
        }
        $o .= '</div>' . PHP_EOL;

        $o .= '<table id="advfrm-fields">' . PHP_EOL;
        $o .= '<thead><tr>'
            . '<th>' . $this->text['label_field'] . '</th>'
            . '<th>' . $this->text['label_label'] . '</th>'
            . '<th colspan="3">' . $this->text['label_type'] . '</th>'
            . '<th>' . $this->text['label_required'] . '</th>'
            . '</tr></thead>' . PHP_EOL;
        foreach ($form->getFields() as $field) {
            $o .= $this->renderEditFormField($field);
        }
        $o .= '</table>' . PHP_EOL;
        $o .= '<input type="submit" class="submit" value="'
            . utf8_ucfirst($tx['action']['save']) . '" style="display:none">';
        $o .= $this->csrfProtector->tokenInput();
        $o .= '</form>' . PHP_EOL . '</div>' . PHP_EOL;

        /*
        * property dialogs
        */
        $o .= '<div id="advfrm-text-props" style="display:none">' . PHP_EOL
            . '<table>' . PHP_EOL;
        $properties = array('size', 'maxlength', 'default', 'constraint', 'error_msg');
        foreach ($properties as $prop) {
            $o .= '<tr id="advfrm-text-props-' . $prop . '"><td>' . $prop . '</td>'
                . '<td>' . '<input type="text" size="30">' . '</td></tr>'
                . PHP_EOL;
        }
        $o .= '</table>' . PHP_EOL . '</div>' . PHP_EOL;

        $o .= '<div id="advfrm-select-props" style="display:none">' .  PHP_EOL;
        $o .= '<p id="advfrm-select-props-size">' . $this->text['label_size'] . ' '
            . '<input type="text">' . '</p>' . PHP_EOL;
        $o .= '<p id="advfrm-select-props-orient">'
            . '<input type="radio" id="advrm-select-props-orient-horz"'
            . ' name="advrm-select-props-orient">'
            . '<label for="advrm-select-props-orient-horz">&nbsp;'
            . $this->text['label_horizontal'] . '</label>&nbsp;&nbsp;&nbsp;'
            . '<input type="radio" id="advrm-select-props-orient-vert"'
            . ' name="advrm-select-props-orient">'
            . '<label for="advrm-select-props-orient-vert">&nbsp;'
            . $this->text['label_vertical'] . '</label>'
            . '</p>' . PHP_EOL;
        $o .= '<div class="toolbar">';
        foreach (array('add', 'delete', 'up', 'down', 'clear_defaults') as $tool) {
            $o .=  '<button type="button" onclick="advfrm_' . $tool . '(\'advfrm-prop-fields\')">'
                . $this->toolIcon($tool) . '</button>' . PHP_EOL;
        }
        $o .= '</div>' . PHP_EOL;
        $o .= '<table id="advfrm-prop-fields">' . PHP_EOL . '<tr>'
            . '<td>'
            . '<input type="radio" name="advfrm-select-props-default">'
            . '</td>'
            . '<td>'
            . '<input type="text" name="advfrm-select-props-opt" size="25"'
            . ' class="highlightable">'
            . '</td>'
            . '</tr>' . PHP_EOL . '</table>' . PHP_EOL . '</div>' . PHP_EOL;

        return $o;
    }

    /**
     * @return string
     */
    private function renderEditFormTable(Form $form)
    {
        $o = '<table id="advfrm-form">' . PHP_EOL;
        $fields = array(
            'name', 'title', 'to_name', 'to', 'cc', 'bcc', 'captcha', 'store',
            'thanks_page'
        );
        foreach ($fields as $det) {
            $name = 'advfrm-' . $det;
            $o .= '<tr>'
                . '<td><label for="' . $name . '">' . $this->text['label_'.$det]
                . '</label></td>';
            switch ($det) {
                case 'captcha':
                case 'store':
                    $checked = $form->{"get$det"}() ? ' checked="checked"' : '';
                    $o .= '<td>'
                        . '<input type="checkbox" id="' . $name . '" name="' . $name . '"'
                        . $checked . '>'
                        . '</td>';
                    break;
                case 'thanks_page':
                    $o .= '<td>' . $this->pageSelect($name, $form->getThanksPage()) . '</td>';
                    break;
                default:
                    $value = $det === 'to_name' ? $form->getToName() : $form->{"get$det"}();
                    $o .= '<td>'
                        . '<input type="text" id="' . $name . '" name="' . $name . '"'
                        . ' value="' . XH_hsc($value) . '" size="40">'
                        . '</td>';
            }
            $o .= '</tr>' . PHP_EOL;
        }
        $o .= '</table>' . PHP_EOL;
        return $o;
    }

    /**
     * @return string
     */
    private function renderEditFormField(Field $field)
    {
        $o = '<tr>'
            . '<td>'
            . '<input type="text" size="10" name="advfrm-field[]"'
            . ' value="' . $field->getName() . '" class="highlightable">'
            . '</td>'
            . '<td>'
            . '<input type="text" size="10" name="advfrm-label[]" value="'
            . XH_hsc($field->getLabel()) . '" class="highlightable">'
            . '</td>'
            . '<td><select name="advfrm-type[]" onfocus="this.oldvalue = this.value"'
            . ' class="highlightable">';
        $types = array(
            'text', 'from_name', 'from', 'mail', 'date', 'number', 'textarea',
            'radio', 'checkbox', 'select', 'multi_select', 'password', 'file',
            'hidden', 'output', 'custom'
        );
        foreach ($types as $type) {
            $sel = ($field->getType() == $type) ? ' selected="selected"' : '';
            $o .= '<option value="' . $type . '"' . $sel . '>'
                . $this->text['field_' . $type] . '</option>';
        }
        $o .= '</select></td>'
            . '<td>'
            . '<input type="hidden" class="hidden" name="advfrm-props[]"'
            . ' value="' . XH_hsc($field->getProps()) . '">'
            . '<td><button type="button">' . $this->toolIcon('props') . '</button>' . PHP_EOL;
        $checked = $field->getRequired() ? ' checked="checked"' : '';
        $o .= '<td>'
            . '<input type="checkbox"' . $checked . ' onchange="this.'
            . 'nextSibling.value = this.checked ? 1 : 0">'
            . '<input type="hidden" name="advfrm-required[]" value="'
            . $field->getRequired() . '">'
            . '</td>'
            . '</tr>' . PHP_EOL;
        return $o;
    }

    /**
     * Returns a selectbox with all  pages of the current language/subsite.
     *
     * @param string $name     Name and id of the select.
     * @param string $selected URL of the thanks page.
     *
     * @return string (X)HTML.
     */
    private function pageSelect($name, $selected)
    {
        $pagelist = (new Pages)->linkList();
        $o = '<select id="' . $name . '" name="' . $name . '">' . PHP_EOL;
        $sel = ($selected == '') ? ' selected="selected"' : '';
        $o .= '<option value=""' . $sel . '>' . $this->text['label_none'] . '</option>'
            . PHP_EOL;
        foreach ($pagelist as $page) {
            $sel = ($page[1] == $selected) ? ' selected="selected"' : '';
            $o .= '<option value="' . $page[1] . '"' . $sel . '>'
                . $page[0] . '</option>'
                . PHP_EOL;
        }
        $o .= '</select>' . PHP_EOL;
        return $o;
    }

    /**
     * Saves the modified mail form definition. Returns the the mail form list on
     * success, or the mail form editor on failure.
     *
     * @param string $id A form ID.
     *
     * @return string (X)HTML.
     */
    public function saveFormAction($id)
    {
        global $e;

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return $this->formsAdministrationAction();
        }
        $this->csrfProtector->check();
        $forms = Functions::database();
        if (!isset($forms[$id])) {
            $e .= '<li><b>' . sprintf($this->text['error_form_missing'], $id) . '</b></li>';
            return $this->formsAdministrationAction();
        }
        unset($forms[$id]);
        if (!isset($forms[$_POST['advfrm-name']])) {
            $id = $_POST['advfrm-name'];
            $ok = true;
        } else {
            $_POST['advfrm-name'] = $id;
            $e .= '<li>' . $this->text['error_form_exists'] . '</li>';
            $ok = false;
        }
        $forms[$id] = Form::createFromArray($this->getFormArrayFromPost());
        Functions::database($forms);
        return $ok ? $this->formsAdministrationAction() : $this->editFormAction($id);
    }

    /**
     * @return array
     */
    private function getFormArrayFromPost()
    {
        $form = [];
        $form['captcha'] = false;
        $form['store'] = false;
        foreach ($_POST as $key => $val) {
            $keys = explode('-', $key);
            if ($keys[0] == 'advfrm') {
                if (!is_array($val)) {
                    if (in_array($keys[1], array('captcha', 'store'))) {
                        $form[$keys[1]] = true;
                    } else {
                        $form[$keys[1]] = $val;
                    }
                } else {
                    foreach ($val as $num => $fieldval) {
                        $form['fields'][$num][$keys[1]] = $fieldval;
                    }
                }
            }
        }
        return $form;
    }

    /**
     * Deletes a form, and returns the mail form list.
     *
     * @param string $id A form ID.
     *
     * @return string (X)HTML.
     */
    public function deleteFormAction($id)
    {
        global $e;

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return $this->formsAdministrationAction();
        }
        $this->csrfProtector->check();
        $forms = Functions::database();
        if (isset($forms[$id])) {
            unset($forms[$id]);
            Functions::database($forms);
        } else {
            $e .= '<li><b>'
                . sprintf($this->text['error_form_missing'], $id)
                . '</b></li>';
        }
        return $this->formsAdministrationAction();
    }

    /**
     * Makes a copy of form $id. Returns the mail form editor.
     *
     * @param string $id A form ID.
     *
     * @return string (X)HTML.
     */
    public function copyFormAction($id)
    {
        global $e;

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return $this->formsAdministrationAction();
        }
        $this->csrfProtector->check();
        $forms = Functions::database();
        if (isset($forms[$id])) {
            $form = clone $forms[$id];
            $form->setName('');
            $id = uniqid();
            $forms[$id] = $form;
            Functions::database($forms);
        } else {
            $e .= '<li><b>'
                . sprintf($this->text['error_form_missing'], $id)
                . '</b></li>';
        }
        return $this->editFormAction($id);
    }

    /**
     * Imports the form definition from a *.frm file. Returns the mail form
     * administration.
     *
     * @param string $id A form ID.
     *
     * @return string (X)HTML.
     */
    public function importFormAction($id)
    {
        global $e;

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return $this->formsAdministrationAction();
        }
        $this->csrfProtector->check();
        $forms = Functions::database();
        if (!isset($forms[$id])) {
            $fn = Functions::dataFolder() . $id . '.json';
            if (($cnt = file_get_contents($fn)) !== false
                && ($form = json_decode($cnt, true)) !== false
                && isset($form['%VERSION%'])
                && count($form) == 2
            ) {
                foreach ($form as &$frm) {
                    if (is_array($frm)) {
                        $frm = Form::createFromArray($frm);
                    }
                }
                if ($form['%VERSION%'] < ADVFRM_DB_VERSION) {
                    $form = Functions::updatedDb($form);
                }
                unset($form['%VERSION%']);
                foreach ($form as $f) {
                    $f->setName($id);
                    $forms[$id] = $f;
                }
                Functions::database($forms);
            } else {
                e('cntopen', 'file', $fn);
            }
        } else {
            $e .= '<li><b>' . $this->text['error_form_exists'] . '</b></li>';
        }
        return $this->formsAdministrationAction();
    }

    /**
     * Exports the form definition to a *.frm file. Returns the mail form administration.
     *
     * @param string $id A form ID.
     *
     * @return string (X)HTML.
     */
    public function exportFormAction($id)
    {
        global $e;

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return $this->formsAdministrationAction();
        }
        $this->csrfProtector->check();
        $forms = Functions::database();
        if (isset($forms[$id])) {
            $form[$id] = $forms[$id];
            $form['%VERSION%'] = ADVFRM_DB_VERSION;
            $fn = Functions::dataFolder() . $id . '.json';
            $json = json_encode($form, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if (!($fh = fopen($fn, 'w')) || fwrite($fh, $json) === false) {
                e('cntwriteto', 'file', $fn);
            }
            if ($fh) {
                fclose($fh);
            }
        } else {
            $e .= '<li><b>' . sprintf($this->text['error_form_missing'], $id) . '</b></li>';
        }
        return $this->formsAdministrationAction();
    }

    /**
     * Creates a basic template of the form. Returns the the mail form administration.
     *
     * @param string $id A form ID.
     *
     * @return string (X)HTML.
     */
    public function createFormTemplateAction($id)
    {
        global $e;

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return $this->formsAdministrationAction();
        }
        $this->csrfProtector->check();
        $forms = Functions::database();
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
            foreach ($form->getFields() as $field) {
                if ($first) {
                    $tpl .= '  <?php Advancedform_focusField(\'' . $id . '\', \'advfrm-'
                        . $field->getName() . '\')'
                        . ' // focus the first field?>' . PHP_EOL;
                    $first = false;
                }
                $labelled = !in_array($field->getType(), array('checkbox', 'radio', 'hidden'));
                if (in_array($field->getType(), array('hidden'))) {
                    $label = '';
                } elseif (!$field->getRequired()) {
                    $label = $field->getLabel();
                } else {
                    $label = sprintf(
                        $this->conf['advancedform']['required_field_mark'],
                        $field->getLabel()
                    );
                }
                if ($labelled) {
                    $label = '<label for="advfrm-' . $id . '-' . $field->getName() . '">'
                        . $label . '</label>';
                }
                $tpl .= '  <div class="break">' . PHP_EOL
                    . '    <div class="label">'
                    . $label
                    . '</div>' . PHP_EOL
                    . '    <div class="field"><?field ' . $field->getName() . '?></div>'
                    . PHP_EOL
                    . '  </div>' . PHP_EOL;
                $css .= '#advfrm-' . $id . '-' . $field->getName() . ' {}' . PHP_EOL;
            }
            $tpl .= '  <div class="break"></div>' . PHP_EOL . '</div>' . PHP_EOL;
            $fn = Functions::dataFolder() . $id . '.tpl';
            if (file_put_contents($fn, $tpl) === false) {
                e('cntsave', 'file', $fn);
            }
            $fn = Functions::dataFolder() . 'css/' . $id . '.css';
            if (file_put_contents($fn, $css) === false) {
                e('cntsave', 'file', $fn);
            }
        } else {
            $e .= '<li><b>' . sprintf($this->text['error_form_missing'], $id) . '</b></li>';
        }
        return $this->formsAdministrationAction();
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
    private function toolForm($name, $action, $onsubmit = false)
    {
        $onsubmit = $onsubmit ? 'onsubmit="' . $onsubmit . '"' : '';
        $icon = $this->toolIcon($name);
        $tokenInput = $this->csrfProtector->tokenInput();
        return <<<EOT
<form action="$action" method="post" $onsubmit>
    <button title="{$this->text['tool_' . $name]}">$icon</button>
    $tokenInput
</form>
EOT;
    }

    /**
     * Returns FA span for the tool $name.
     *
     * @param string $name A tool's name.
     *
     * @return string (X)HTML.
     */
    private function toolIcon($name)
    {
        $title = $this->text['tool_'.$name];
        $map = array(
            'add' => 'plus',
            'clear_defaults' => 'bullseye',
            'copy' => 'clone',
            'delete' => 'trash',
            'down' => 'chevron-down',
            'export' => 'download',
            'import' => 'upload',
            'props' => 'wrench',
            'template' => 'file-text-o',
            'up' => 'chevron-up',
        );
        return '<span class="fa fa-' . $map[$name] . '" title="' . $title . '"></span>';
    }
}

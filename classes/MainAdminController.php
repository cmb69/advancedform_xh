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

class MainAdminController extends Controller
{
    /** @var FormGateway */
    private $formGateway;

    /**
     * @var object
     */
    private $csrfProtector;

    /** @var View */
    private $view;

    public function __construct(FormGateway $formGateway)
    {
        global $_XH_csrfProtection;

        parent::__construct();
        $this->formGateway = $formGateway;
        $this->csrfProtector = $_XH_csrfProtection;
        $this->view = new View();
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
        $forms = $this->formGateway->findAll();
        $bag = array(
            'title' => 'Advancedform – ' . $this->text['menu_main'],
            'add_form' => $this->toolData(
                'add',
                $this->scriptName . '?advancedform&amp;admin=plugin_main&amp;action=new'
            ),
            'import_form' => $this->toolData(
                'import',
                $this->scriptName . '?advancedform&amp;admin=plugin_main&amp;action=import&amp;form='
            ),
            'forms' => [],
            'edit_label' => utf8_ucfirst($tx['action']['edit']),
            'code_label' => $this->text['message_script_code'],
        );
        foreach ($forms as $id => $form) {
            if ($id != '%VERSION%') {
                $href = $this->scriptName . '?advancedform&amp;admin=plugin_main&amp;action=%s' . '&amp;form=' . $id;
                $bag['forms'][$id] = array(
                    'delete_form' => $this->toolData(
                        'delete',
                        sprintf($href, 'delete'),
                        'return confirm(\''
                            . $this->escapeJsString($this->text['message_confirm_delete'])
                            . '\')'
                    ),
                    'template_form' => $this->toolData(
                        'template',
                        sprintf($href, 'template'),
                        'return confirm(\''
                            . $this->escapeJsString(sprintf($this->text['message_confirm_template'], $form->getName()))
                            . '\')'
                    ),
                    'copy_form' => $this->toolData('copy', sprintf($href, 'copy')),
                    'export_form' => $this->toolData(
                        'export',
                        sprintf($href, 'export'),
                        'return confirm(\''
                            . $this->escapeJsString(sprintf($this->text['message_confirm_export'], $form->getName()))
                            . '\')'
                    ),
                    'edit_url' => sprintf($href, 'edit'),
                );
            }
        }
        return $this->view->render('forms-admin', $bag);
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
        $forms = $this->formGateway->findAll();
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
        $this->formGateway->updateAll($forms);
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
        $forms = $this->formGateway->findAll();
        if (!array_key_exists($id, $forms)) {
            $e .= '<li><b>'
                . sprintf($this->text['error_form_missing'], $id)
                . '</b></li>';
            return $this->formsAdministrationAction();
        }
        $form = $forms[$id];

        /*
        * general settings
        */
        $o = '<div id="advfrm-editor">' . PHP_EOL . '<h1>' . $id . '</h1>' . PHP_EOL;
        $action = $this->scriptName
            . '?advancedform&amp;admin=plugin_main&amp;action=save&amp;form=' . $id;
        $o .= '<form action="' . $action . '" method="post" accept-charset="UTF-8"'
            . '>' . PHP_EOL
            . $this->renderEditFormTable($form);

        /*
        * field settings
        */
        $o .= $this->view->render('toolbar', [
            'tools' => ['add', 'delete', 'up', 'down'],
            'toolIcon' => function ($tool) {
                return $this->toolIcon($tool);
            },
        ]);

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
        $o .= $this->view->render('text-props', [
            'properties' => ['size', 'maxlength', 'default', 'constraint', 'error_msg']
        ]);
        $o .= $this->view->render('select-props', [
            'tx' => $this->text,
            'tools' => ['add', 'delete', 'up', 'down', 'clear_defaults'],
            'toolIcon' => function ($tool) {
                return $this->toolIcon($tool);
            },
        ]);

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
        return $this->view->render(
            'edit-form-field',
            array(
                'name' => $field->getName(),
                'label' => XH_hsc($field->getLabel()),
                'types' => [
                    'text', 'from_name', 'from', 'mail', 'date', 'number', 'textarea',
                    'radio', 'checkbox', 'select', 'multi_select', 'password', 'file',
                    'hidden', 'output', 'custom',
                ],
                'selected' => function ($type) use ($field) {
                    return $field->getType() == $type ? ' selected="selected"' : '';
                },
                'typelabel' => function ($type) {
                    return $this->text["field_$type"];
                },
                'properties' => XH_hsc($field->getProps()),
                'toolicon' => $this->toolIcon('props'),
                'checked' => $field->getRequired() ? ' checked="checked"' : '',
                'required' => $field->getRequired(),
            )
        );
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
        $pagelist = (new Pages)->linkList('', false);
        $bag = [
            'name' => $name,
            'selected' => ($selected == '') ? ' selected="selected"' : '',
            'tx' => $this->text,
            'pages' => $pagelist,
            'page_selected' => function ($page) use ($selected) {
                return ($page[1] == $selected) ? ' selected="selected"' : '';
            },
        ];
        return $this->view->render('page-select', $bag);
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
        $forms = $this->formGateway->findAll();
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
        $this->formGateway->updateAll($forms);
        return $ok ? $this->formsAdministrationAction() : $this->editFormAction($id);
    }

    /**
     * @return array<string,(string|bool|array)>
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
                        if ($keys[1] === 'required') {
                            $form['fields'][$num][$keys[1]] = (bool) $fieldval;
                        } else {
                            $form['fields'][$num][$keys[1]] = $fieldval;
                        }
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
        $forms = $this->formGateway->findAll();
        if (isset($forms[$id])) {
            unset($forms[$id]);
            $this->formGateway->updateAll($forms);
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
        $forms = $this->formGateway->findAll();
        if (isset($forms[$id])) {
            $form = clone $forms[$id];
            $form->setName('');
            $id = uniqid();
            $forms[$id] = $form;
            $this->formGateway->updateAll($forms);
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
        $forms = $this->formGateway->findAll();
        if (!isset($forms[$id])) {
            $fn = $this->formGateway->dataFolder() . $id . '.json';
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
                if ($form['%VERSION%'] < Plugin::DB_VERSION) {
                    $form = $this->formGateway->updatedDb($form);
                }
                unset($form['%VERSION%']);
                foreach ($form as $f) {
                    $f->setName($id);
                    $forms[$id] = $f;
                }
                $this->formGateway->updateAll($forms);
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
        $forms = $this->formGateway->findAll();
        if (isset($forms[$id])) {
            $form[$id] = $forms[$id];
            $form['%VERSION%'] = Plugin::DB_VERSION;
            $fn = $this->formGateway->dataFolder() . $id . '.json';
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
        $forms = $this->formGateway->findAll();
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
                        $this->conf['required_field_mark'],
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
            $fn = $this->formGateway->dataFolder() . $id . '.tpl';
            if (file_put_contents($fn, $tpl) === false) {
                e('cntsave', 'file', $fn);
            }
            $fn = $this->formGateway->dataFolder() . 'css/' . $id . '.css';
            if (file_put_contents($fn, $css) === false) {
                e('cntsave', 'file', $fn);
            }
        } else {
            $e .= '<li><b>' . sprintf($this->text['error_form_missing'], $id) . '</b></li>';
        }
        return $this->formsAdministrationAction();
    }

    /**
     * @param string $tool
     * @param string $action
     * @param string $onsubmit
     *
     * @return array<string,string>
     */
    private function toolData($tool, $action, $onsubmit = '')
    {
        return array(
            'class' => "advfrm-$tool-form",
            'title' => $this->text['tool_' . $tool],
            'icon' => $this->toolIcon($tool),
            'action' => $action,
            'onsubmit' => $onsubmit ? 'onsubmit="' . $onsubmit . '"' : '',
            'token_input' => $this->csrfProtector->tokenInput(),
        );
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

<?php

/**
 * Copyright 2005-2010 Jan Kanters
 * Copyright 2011-2022 Christoph M. Becker
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

use XH\CSRFProtection;
use XH\Pages;

class MainAdminController
{
    /** @var FormGateway */
    private $formGateway;

    /** @var string */
    private $scriptName;

    /** @var array<string,string> */
    private $conf;

    /** @var array<string,string> */
    private $text;

    /**
     * @var object
     */
    private $csrfProtector;

    /** @var View */
    private $view;

    /**
     * @param string $scriptName
     * @param array<string,string> $conf
     * @param array<string,string> $text
     */
    public function __construct(
        FormGateway $formGateway,
        $scriptName,
        array $conf,
        array $text,
        CSRFProtection $csrfProtector,
        View $view
    ) {
        $this->formGateway = $formGateway;
        $this->scriptName = $scriptName;
        $this->conf = $conf;
        $this->text = $text;
        $this->csrfProtector = $csrfProtector;
        $this->view = $view;
    }

    /**
     * Returns the mail forms administration.
     *
     * @return string (X)HTML.
     */
    public function formsAdministrationAction()
    {
        global $tx;

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
            'name' => $id,
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
                    'required' => false
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
        global $e, $tx;

        $forms = $this->formGateway->findAll();
        if (!array_key_exists($id, $forms)) {
            $e .= '<li><b>'
                . sprintf($this->text['error_form_missing'], $id)
                . '</b></li>';
            return $this->formsAdministrationAction();
        }
        $form = $forms[$id];
        $thanks_page = $form->getThanksPage();
        return $this->view->render('edit-form', [
            'id' => $id,
            'action' => $this->scriptName . '?advancedform&amp;admin=plugin_main&amp;action=save&amp;form=' . $id,
            'form' => $form,
            'captcha_checked' => $form->getCaptcha() ? 'checked' : '',
            'store_checked' => $form->getStore() ? 'checked' : '',
            'thanks_page_select' => [
                'name' => 'advfrm-thanks_page',
                'selected' => ($thanks_page == '') ? ' selected="selected"' : '',
                'pages' => (new Pages())->linkList('', false),
                'page_selected' => function ($page) use ($thanks_page) {
                    return ($page[1] == $thanks_page) ? ' selected="selected"' : '';
                },
            ],
            'tools' => ['add', 'delete', 'up', 'down'],
            'property_tools' => ['add', 'delete', 'up', 'down', 'clear_defaults'],
            'toolIcon' => function ($tool) {
                return $this->toolIcon($tool);
            },
            'fields' => array_map(function ($field) {
                return [
                    'name' => $field->getName(),
                    'label' => XH_hsc($field->getLabel()),
                    'selected' => function ($type) use ($field) {
                        return $field->getType() == $type ? ' selected="selected"' : '';
                    },
                    'properties' => XH_hsc($field->getProps()),
                    'checked' => $field->getRequired() ? ' checked="checked"' : '',
                    'required' => $field->getRequired(),
                ];
            }, $form->getFields()),
            'field_types' => [
                'text', 'from_name', 'from', 'mail', 'date', 'number', 'textarea',
                'radio', 'checkbox', 'select', 'multi_select', 'password', 'file',
                'hidden', 'output', 'custom',
            ],
            'field_typelabel' => function ($type) {
                return $this->text["field_$type"];
            },
            'label_save' => utf8_ucfirst($tx['action']['save']),
            'csrf_token_input' => $this->csrfProtector->tokenInput(),
            'text_properties' => ['size', 'maxlength', 'default', 'constraint', 'error_msg'],
            'text' => $this->text,
        ]);
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
     * @return FormArray
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
            $id = uniqid();
            $form->setName($id);
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
            if (
                ($cnt = file_get_contents($fn)) !== false
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
            $tpl = '<div id="advfrm-' . $id . '">' . "\n";
            $css = '#advfrm-' . $id . ' {}' . "\n" . "\n"
                . '#advfrm-' . $id . ' div.break {clear: both}' . "\n" . "\n"
                . '#advfrm-' . $id . ' div.float {float: left; margin-right: 1em}'
                . "\n" . "\n"
                . '#advfrm-' . $id . ' div.label'
                . ' {/* float: left; width: 12em; margin-bottom: 0.5em; */}' . "\n"
                . '#advfrm-' . $id . ' div.field '
                . ' { margin-bottom: 0.5em; /* float: left;*/}' . "\n" . "\n"
                . '/* the individual fields */' . "\n" . "\n";
            $first = true;
            foreach ($form->getFields() as $field) {
                if ($first) {
                    $tpl .= '  <?php Advancedform_focusField(\'' . $id . '\', \'advfrm-'
                        . $field->getName() . '\')'
                        . ' // focus the first field?>' . "\n";
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
                $tpl .= '  <div class="break">' . "\n"
                    . '    <div class="label">'
                    . $label
                    . '</div>' . "\n"
                    . '    <div class="field"><?field ' . $field->getName() . '?></div>'
                    . "\n"
                    . '  </div>' . "\n";
                $css .= '#advfrm-' . $id . '-' . $field->getName() . ' {}' . "\n";
            }
            $tpl .= '  <div class="break"></div>' . "\n" . '</div>' . "\n";
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
     * Returns the SVG icon for the tool $name.
     *
     * @param string $name A tool's name.
     *
     * @return string (X)HTML.
     */
    private function toolIcon($name)
    {
        $map = array(
            'add' => 'plus',
            'clear_defaults' => 'bullseye',
            'copy' => 'clone',
            'delete' => 'trash',
            'down' => 'chevron-down',
            'export' => 'download',
            'import' => 'upload',
            'props' => 'wrench',
            'template' => 'file-lines',
            'up' => 'chevron-up',
        );
        $svgs = [
            'plus' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="1em" height="1em" fill="currentColor"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 144L48 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l144 0 0 144c0 17.7 14.3 32 32 32s32-14.3 32-32l0-144 144 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-144 0 0-144z"/></svg>',
            'bullseye' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="1em" height="1em" fill="currentColor"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M448 256A192 192 0 1 0 64 256a192 192 0 1 0 384 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm256 80a80 80 0 1 0 0-160 80 80 0 1 0 0 160zm0-224a144 144 0 1 1 0 288 144 144 0 1 1 0-288zM224 256a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z"/></svg>',
            'clone' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="1em" height="1em" fill="currentColor"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M288 448L64 448l0-224 64 0 0-64-64 0c-35.3 0-64 28.7-64 64L0 448c0 35.3 28.7 64 64 64l224 0c35.3 0 64-28.7 64-64l0-64-64 0 0 64zm-64-96l224 0c35.3 0 64-28.7 64-64l0-224c0-35.3-28.7-64-64-64L224 0c-35.3 0-64 28.7-64 64l0 224c0 35.3 28.7 64 64 64z"/></svg>',
            'trash' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="1em" height="1em" fill="currentColor"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M135.2 17.7L128 32 32 32C14.3 32 0 46.3 0 64S14.3 96 32 96l384 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-96 0-7.2-14.3C307.4 6.8 296.3 0 284.2 0L163.8 0c-12.1 0-23.2 6.8-28.6 17.7zM416 128L32 128 53.2 467c1.6 25.3 22.6 45 47.9 45l245.8 0c25.3 0 46.3-19.7 47.9-45L416 128z"/></svg>',
            'chevron-down' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="1em" height="1em" fill="currentColor"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z"/></svg>',
            'download' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="1em" height="1em" fill="currentColor"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M288 32c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 242.7-73.4-73.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l128 128c12.5 12.5 32.8 12.5 45.3 0l128-128c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L288 274.7 288 32zM64 352c-35.3 0-64 28.7-64 64l0 32c0 35.3 28.7 64 64 64l384 0c35.3 0 64-28.7 64-64l0-32c0-35.3-28.7-64-64-64l-101.5 0-45.3 45.3c-25 25-65.5 25-90.5 0L165.5 352 64 352zm368 56a24 24 0 1 1 0 48 24 24 0 1 1 0-48z"/></svg>',
            'upload' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="1em" height="1em" fill="currentColor"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M288 109.3L288 352c0 17.7-14.3 32-32 32s-32-14.3-32-32l0-242.7-73.4 73.4c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3l128-128c12.5-12.5 32.8-12.5 45.3 0l128 128c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L288 109.3zM64 352l128 0c0 35.3 28.7 64 64 64s64-28.7 64-64l128 0c35.3 0 64 28.7 64 64l0 32c0 35.3-28.7 64-64 64L64 512c-35.3 0-64-28.7-64-64l0-32c0-35.3 28.7-64 64-64zM432 456a24 24 0 1 0 0-48 24 24 0 1 0 0 48z"/></svg>',
            'wrench' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="1em" height="1em" fill="currentColor"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M352 320c88.4 0 160-71.6 160-160c0-15.3-2.2-30.1-6.2-44.2c-3.1-10.8-16.4-13.2-24.3-5.3l-76.8 76.8c-3 3-7.1 4.7-11.3 4.7L336 192c-8.8 0-16-7.2-16-16l0-57.4c0-4.2 1.7-8.3 4.7-11.3l76.8-76.8c7.9-7.9 5.4-21.2-5.3-24.3C382.1 2.2 367.3 0 352 0C263.6 0 192 71.6 192 160c0 19.1 3.4 37.5 9.5 54.5L19.9 396.1C7.2 408.8 0 426.1 0 444.1C0 481.6 30.4 512 67.9 512c18 0 35.3-7.2 48-19.9L297.5 310.5c17 6.2 35.4 9.5 54.5 9.5zM80 408a24 24 0 1 1 0 48 24 24 0 1 1 0-48z"/></svg>',
            'file-lines' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="1em" height="1em" fill="currentColor"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M64 464c-8.8 0-16-7.2-16-16L48 64c0-8.8 7.2-16 16-16l160 0 0 80c0 17.7 14.3 32 32 32l80 0 0 288c0 8.8-7.2 16-16 16L64 464zM64 0C28.7 0 0 28.7 0 64L0 448c0 35.3 28.7 64 64 64l256 0c35.3 0 64-28.7 64-64l0-293.5c0-17-6.7-33.3-18.7-45.3L274.7 18.7C262.7 6.7 246.5 0 229.5 0L64 0zm56 256c-13.3 0-24 10.7-24 24s10.7 24 24 24l144 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-144 0zm0 96c-13.3 0-24 10.7-24 24s10.7 24 24 24l144 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-144 0z"/></svg>',
            'chevron-up' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="1em" height="1em" fill="currentColor"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M233.4 105.4c12.5-12.5 32.8-12.5 45.3 0l192 192c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L256 173.3 86.6 342.6c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3l192-192z"/></svg>',

        ];
        return $svgs[$map[$name]];
    }
}

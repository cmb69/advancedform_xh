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

use Plib\Codec;
use Plib\CsrfProtector;
use Plib\Random;
use Plib\Request;
use Plib\Response;
use Plib\View;
use XH\Pages;

class MainAdminController
{
    /** @var FormGateway */
    private $formGateway;

    /** @var array<string,string> */
    private $conf;

    /** @var array<string,string> */
    private $text;

    /** @var CsrfProtector */
    private $csrfProtector;

    /** @var Pages */
    private $pages;

    /** @var Random */
    private $random;

    /** @var View */
    private $view;

    /**
     * @param array<string,string> $conf
     * @param array<string,string> $text
     */
    public function __construct(
        FormGateway $formGateway,
        array $conf,
        array $text,
        CsrfProtector $csrfProtector,
        Pages $pages,
        Random $random,
        View $view
    ) {
        $this->formGateway = $formGateway;
        $this->conf = $conf;
        $this->text = $text;
        $this->csrfProtector = $csrfProtector;
        $this->pages = $pages;
        $this->random = $random;
        $this->view = $view;
    }

    public function __invoke(Request $request): Response
    {
        switch ($request->post("action") ?? $request->get("action")) {
            case 'new':
                return $this->createFormAction($request);
            case 'edit':
                return $this->editFormAction($request);
            case 'save':
                return $this->saveFormAction($request);
            case 'delete':
                return $this->deleteFormAction($request);
            case 'copy':
                return $this->copyFormAction($request);
            case 'import':
                return $this->importFormAction($request);
            case 'export':
                return $this->exportFormAction($request);
            case 'template':
                return $this->createFormTemplateAction($request);
            default:
                return $this->formsAdministrationAction($request);
        }
    }

    private function formsAdministrationAction(Request $request): Response
    {
        global $tx;

        $forms = $this->formGateway->findAll();
        $bag = array(
            'title' => 'Advancedform – ' . $this->text['menu_main'],
            'add_form' => $this->toolData(
                'add',
                $request->url()->with("action", "new")->relative()
            ),
            'import_form' => $this->toolData(
                'import',
                $request->url()->with("action", "import")->with("form", "PLACEHOLDER")->relative()
            ),
            'forms' => [],
            'edit_label' => utf8_ucfirst($tx['action']['edit']),
            'code_label' => $this->text['message_script_code'],
        );
        foreach ($forms as $id => $form) {
            if ($id != '%VERSION%') {
                $url = $request->url()->with("form", $id);
                $bag['forms'][$id] = array(
                    'delete_form' => $this->toolData(
                        'delete',
                        $url->with("action", "delete")->relative(),
                        'return confirm(\''
                            . $this->escapeJsString($this->text['message_confirm_delete'])
                            . '\')'
                    ),
                    'template_form' => $this->toolData(
                        'template',
                        $url->with("action", "template")->relative(),
                        'return confirm(\''
                            . $this->escapeJsString(sprintf($this->text['message_confirm_template'], $form->getName()))
                            . '\')'
                    ),
                    'copy_form' => $this->toolData('copy', $url->with("action", "copy")->relative()),
                    'export_form' => $this->toolData(
                        'export',
                        $url->with("action", "export")->relative(),
                        'return confirm(\''
                            . $this->escapeJsString(sprintf($this->text['message_confirm_export'], $form->getName()))
                            . '\')'
                    ),
                    'edit_url' => $url->with("action", "edit")->relative(),
                );
            }
        }
        return Response::create($this->view->render('forms-admin', $bag));
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

    private function createFormAction(Request $request): Response
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return $this->formsAdministrationAction($request);
        }
        if (!$this->csrfProtector->check($_POST["advancedform_token"])) {
            return Response::create("nope"); // TODO
        }
        $forms = $this->formGateway->findAll();
        $id = Codec::encodeBase32hex($this->random->bytes(15));
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
        if (!$this->formGateway->updateAll($forms)) {
            return Response::create($this->view->message("fail", "error_save"));
        }
        $url = $request->url()->with("action", "edit")->with("form", $id);
        return Response::redirect($url->absolute());
    }

    private function editFormAction(Request $request): Response
    {
        $id = $request->get("form");
        $forms = $this->formGateway->findAll();
        if (!array_key_exists($id, $forms)) {
            return Response::create($this->view->message("fail", "error_form_missing", $id));
        }
        $form = $forms[$id];
        return Response::create($this->renderEditForm($request, $id, $form));
    }

    private function renderEditForm(Request $request, string $id, Form $form): string
    {
        global $tx;
        $thanks_page = $form->getThanksPage();
        return $this->view->render('edit-form', [
            'id' => $id,
            'action' => $request->url()->with("action", "save")->with("form", $id)->relative(),
            'form' => $form,
            'captcha_checked' => $form->getCaptcha() ? 'checked' : '',
            'store_checked' => $form->getStore() ? 'checked' : '',
            'thanks_page_select' => [
                'name' => 'advfrm-thanks_page',
                'selected' => ($thanks_page == '') ? ' selected="selected"' : '',
                'pages' => $this->pages->linkList('', false),
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
            'csrf_token' => $this->csrfProtector->token(),
            'text_properties' => ['size', 'maxlength', 'default', 'constraint', 'error_msg'],
            'text' => $this->text,
        ]);
    }

    private function saveFormAction(Request $request): Response
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return $this->formsAdministrationAction($request);
        }
        if (!$this->csrfProtector->check($_POST["advancedform_token"])) {
            return Response::create("nope"); // TODO
        }
        $id = $request->get("form");
        $forms = $this->formGateway->findAll();
        if (!isset($forms[$id])) {
            return Response::create($this->view->message("fail", "error_form_missing", $id));
        }
        unset($forms[$id]);
        if (array_key_exists($_POST['advfrm-name'], $forms)) {
            return Response::create($this->view->message("fail", "error_form_exists")
                . $this->renderEditForm($request, $id, Form::createFromArray($this->getFormArrayFromPost())));
        }
        $id = $_POST['advfrm-name'];
        $forms[$id] = Form::createFromArray($this->getFormArrayFromPost());
        if (!$this->formGateway->updateAll($forms)) {
            return Response::create($this->view->message("fail", "error_save")
                . $this->renderEditForm($request, $id, Form::createFromArray($this->getFormArrayFromPost())));
        }
        $url = $request->url()->with("action", "plugin_text")->without("form");
        return Response::redirect($url->absolute());
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

    private function deleteFormAction(Request $request): Response
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return $this->formsAdministrationAction($request);
        }
        if (!$this->csrfProtector->check($_POST["advancedform_token"])) {
            return Response::create("nope"); // TODO
        }
        $id = $request->get("form");
        $forms = $this->formGateway->findAll();
        if (!array_key_exists($id, $forms)) {
            return Response::create($this->view->message("fail", "error_form_missing", $id));
        }
        unset($forms[$id]);
        if (!$this->formGateway->updateAll($forms)) {
            return Response::create($this->view->message("fail", "error_save"));
        }
        $url = $request->url()->with("action", "plugin_text")->without("form");
        return Response::redirect($url->absolute());
    }

    private function copyFormAction(Request $request): Response
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return $this->formsAdministrationAction($request);
        }
        if (!$this->csrfProtector->check($_POST["advancedform_token"])) {
            return Response::create("nope"); // TODO
        }
        $id = $request->get("form");
        $forms = $this->formGateway->findAll();
        if (!array_key_exists($id, $forms)) {
            return Response::create($this->view->message("fail", "error_form_missing", $id));
        }
        $form = clone $forms[$id];
        $id = Codec::encodeBase32hex($this->random->bytes(15));
        $form->setName($id);
        $forms[$id] = $form;
        if (!$this->formGateway->updateAll($forms)) {
            return Response::create($this->view->message("fail", "error_save"));
        }
        $url = $request->url()->with("action", "edit")->with("form", $id);
        return Response::redirect($url->absolute());
    }

    private function importFormAction(Request $request): Response
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return $this->formsAdministrationAction($request);
        }
        if (!$this->csrfProtector->check($_POST["advancedform_token"])) {
            return Response::create("nope"); // TODO
        }
        $id = $request->get("form");
        $forms = $this->formGateway->findAll();
        if (isset($forms[$id])) {
            return Response::create($this->view->message("fail", "error_form_exists"));
        }
        $fn = $this->formGateway->dataFolder() . $id . '.json';
        if (
            !(
            ($cnt = @file_get_contents($fn)) !== false
            && ($form = json_decode($cnt, true)) !== false
            && isset($form['%VERSION%'])
            && count($form) == 2)
        ) {
            return Response::create($this->view->message("fail", "error_import", $fn));
        }
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
        if (!$this->formGateway->updateAll($forms)) {
            return Response::create($this->view->message("fail", "error_save"));
        }
        $url = $request->url()->with("action", "plugin_text")->without("form");
        return Response::redirect($url->absolute());
    }

    private function exportFormAction(Request $request): Response
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return $this->formsAdministrationAction($request);
        }
        if (!$this->csrfProtector->check($_POST["advancedform_token"])) {
            return Response::create("nope"); // TODO
        }
        $id = $request->get("form");
        $forms = $this->formGateway->findAll();
        if (!isset($forms[$id])) {
            return Response::create($this->view->message("fail", "error_form_missing", $id));
        }
        $form[$id] = $forms[$id];
        $form['%VERSION%'] = Plugin::DB_VERSION;
        $fn = $this->formGateway->dataFolder() . $id . '.json';
        $json = json_encode($form, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $fail = (!($fh = fopen($fn, 'w')) || fwrite($fh, $json) !== strlen($json));
        if ($fh) {
            fclose($fh);
        }
        if ($fail) {
            return Response::create($this->view->message("fail", "error_export", $fn));
        }
        $url = $request->url()->with("action", "plugin_text")->without("form");
        return Response::redirect($url->absolute());
    }

    private function createFormTemplateAction(Request $request): Response
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return $this->formsAdministrationAction($request);
        }
        if (!$this->csrfProtector->check($_POST["advancedform_token"])) {
            return Response::create("nope"); // TODO
        }
        $id = $request->get("form");
        $forms = $this->formGateway->findAll();
        if (!isset($forms[$id])) {
            return Response::create($this->view->message("fail", "error_form_missing", $id));
        }
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
        if (@file_put_contents($fn, $tpl) !== strlen($tpl)) {
            return Response::create($this->view->message("fail", "error_template", $fn));
        }
        $fn = $this->formGateway->dataFolder() . 'css/' . $id . '.css';
        if (@file_put_contents($fn, $css) !== strlen($css)) {
            return Response::create($this->view->message("fail", "error_template", $fn));
        }
        $url = $request->url()->with("action", "plugin_text")->without("form");
        return Response::redirect($url->absolute());
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
            'token' => $this->csrfProtector->token(),
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

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

class MailFormController
{
    /** @var FormGateway */
    private $formGateway;

    /** @var FieldRenderer */
    private $fieldRenderer;

    /** @var string */
    private $scriptName;

    /** @var string */
    private $pluginsFolder;

    /** @var array<string,string> */
    private $conf;

    /** @var array<string,string> */
    private $text;

    /** @var bool */
    private $adm;

    /** @var MailService */
    private $mailService;

    /** @var View */
    private $view;

    /**
     * @param string $scriptName
     * @param string $pluginsFolder
     * @param array<string,string> $conf
     * @param array<string,string> $text
     * @param bool $adm
     */
    public function __construct(
        FormGateway $formGateway,
        FieldRenderer $fieldRenderer,
        $scriptName,
        $pluginsFolder,
        array $conf,
        array $text,
        $adm,
        MailService $mailService,
        View $view
    ) {
        $this->formGateway = $formGateway;
        $this->fieldRenderer = $fieldRenderer;
        $this->scriptName = $scriptName;
        $this->pluginsFolder = $pluginsFolder;
        $this->conf = $conf;
        $this->text = $text;
        $this->adm = $adm;
        $this->mailService = $mailService;
        $this->view = $view;
    }

    /**
     * Main plugin call.
     *
     * @param string $id A form ID.
     *
     * @return string (X)HTML.
     */
    public function main($id)
    {
        global $e;

        $hooks = $this->formGateway->dataFolder() . $id . '.inc'
            . ($this->conf['php_extension'] ? '.php' : '');
        if (file_exists($hooks)) {
            include $hooks;
        }

        $forms = $this->formGateway->findAll();
        if (!isset($forms[$id])) {
            $e .= '<li>' . sprintf($this->text['error_form_missing'], $id) . '</li>' . "\n";
            return '';
        }
        $form = $forms[$id];

        if ($form->getCaptcha()) {
            $fn = $this->pluginsFolder . $this->conf['captcha_plugin'] . '/captcha.php';
            if (!is_file($fn) || !include_once $fn) {
                e('cntopen', 'file', $fn);
                return '';
            }
        }

        if (isset($_POST['advfrm']) && $_POST['advfrm'] == $id) {
            $validator = new Validator($this->conf, $this->text);
            if ($validator->check($form)) {
                if ($form->getStore()) {
                    $this->appendCsv($form);
                }
                if (!$this->mail($form, false)) {
                    return $this->formView($form);
                }
                if (function_exists('advfrm_custom_thanks_page')) {
                    $fields = Plugin::fields();
                    $thanks = advfrm_custom_thanks_page($id, $fields);
                }
                if (empty($thanks)) {
                    $thanks = $form->getThanksPage();
                }
                if (!empty($thanks)) {
                    if ($this->conf['mail_confirmation'] && !$this->mail($form, true)) {
                        return $this->formView($form);
                    }
                    header('Location: ' . $this->scriptName . '?' . $thanks);
                    // FIXME: exit()?
                } else {
                    return $this->mailService->mailInfo($form, false, true);
                }
            } else {
                Plugin::focusField(...$validator->focusField);
                $o = '<ul class="advfrm-error">';
                foreach ($validator->errors as $error) {
                    $o .= '<li>' . $error . '</li>' . "\n";
                }
                $o .= '</ul>';
                return $o . $this->formView($form);
            }
        }
        return $this->formView($form);
    }

    /**
     * Returns the view of the form.
     *
     * @return string (X)HTML.
     */
    private function formView(Form $form)
    {
        global $su, $f;

        $id = $form->getName();
        return $this->view->render('mail-form', [
            'id' => $id,
            'url' => $this->scriptName . '?' . ($f === 'mailform' ? '&mailform' : $su),
            'required_message' => sprintf(
                $this->text['message_required_fields'],
                sprintf($this->conf['required_field_mark'], $this->text['message_required_field'])
            ),
            'inner_view' => file_exists($this->formGateway->dataFolder() . $id . '.tpl')
                ? $this->templateView($form)
                : $this->defaultView($form),
            'captcha' => $form->getCaptcha() ? call_user_func($this->conf['captcha_plugin'] . '_captcha_display') : '',
            'tx' => $this->text,
        ]);
    }

    /**
     * Returns the default view of the form.
     *
     * @return string (X)HTML.
     */
    private function defaultView(Form $form)
    {
        $id = $form->getName();
        $bag = [
            'fields' => [],
        ];
        foreach ($form->getFields() as $field) {
            $labeled = !in_array($field->getType(), ['checkbox', 'radio', 'output']);
            $bag['fields'][] = [
                'label' => $field->getRequired()
                    ? sprintf($this->conf['required_field_mark'], XH_hsc($field->getLabel()))
                    : XH_hsc($field->getLabel()),
                'hidden' => $field->getType() == 'hidden',
                'class' => $field->getType() == 'hidden' ? ' class="hidden"' : '',
                'field_id' => 'advfrm-' . $id . '-' . $field->getName(),
                'labeled' => $labeled,
                'inner_view' => $this->fieldRenderer->render($field),
            ];
            if ($this->conf['focus_form'] && $field->getType() !== 'output') {
                Plugin::focusField($id, 'advfrm-' . $field->getName());
            }
        }
        return $this->view->render('mail-form-default-view', $bag);
    }

    /**
     * Returns the view of a form by instatiating the template.
     *
     * @return string (X)HTML.
     */
    private function templateView(Form $form)
    {
        global $hjs;

        $id = $form->getName();
        $fn = $this->formGateway->dataFolder() . 'css/' . $id . '.css';
        if (file_exists($fn)) {
            $hjs .= '<link rel="stylesheet" href="' . $fn . '" type="text/css">'
            . "\n";
        }
        $fn = $this->formGateway->dataFolder() . 'js/' . $id . '.js';
        if (file_exists($fn)) {
            $hjs .= '<script src="' . $fn . '"></script>'
                . "\n";
        }
        $fn = $this->formGateway->dataFolder() . $id . '.tpl'
            . ($this->conf['php_extension'] ? '.php' : '');
        $advfrm_script = file_get_contents($fn);
        foreach ($form->getFields() as $field) {
            $advfrm_script = str_replace(
                '<?field ' . $field->getName() . '?>',
                $this->fieldRenderer->render($field),
                $advfrm_script
            );
        }
        extract($GLOBALS);
        ob_start();
        eval('?>' . $advfrm_script);
        return ob_get_clean();
    }

    /**
     * Appends the posted record to csv file.
     *
     * @return void
     */
    private function appendCsv(Form $form)
    {
        $id = $form->getName();
        $fields = array();
        foreach ($form->getFields() as $field) {
            if ($field->getType() != 'output') {
                $name = $field->getName();
                $val = ($field->getType() == 'file')
                    ? $_FILES['advfrm-' . $name]['name']
                    : $_POST['advfrm-' . $name];
                $fields[] = is_array($val)
                    ? implode("\xC2\xA6", $val)
                    : $val;
            }
        }
        $fields = array_map(
            function ($field) {
                return str_replace("\0", "", $field);
            },
            $fields
        );
        if ($this->conf['csv_separator'] != '') {
            $separator = $this->conf['csv_separator'][0];
        } else {
            $separator = "\t";
        }
        $fn = $this->formGateway->dataFolder() . $id . '.csv';
        if (
            ($fh = fopen($fn, 'a')) === false
            || fputcsv($fh, $fields, $separator, '"', "\0") === false
        ) {
            e('cntwriteto', 'file', $fn);
        }
        if ($fh !== false) {
            fclose($fh);
        }
    }

    /**
     * Sends the mail and returns whether that was successful.
     *
     * @param bool $confirmation Whether to send the confirmation mail.
     *
     * @return bool
     */
    private function mail(Form $form, $confirmation)
    {
        global $e;

        $id = $form->getName();
        $type = strtolower($this->conf['mail_type']);
        $from = '';
        $from_name = '';
        foreach ($form->getFields() as $field) {
            if ($field->getType() == 'from_name') {
                $from_name = $_POST['advfrm-' . $field->getName()];
            } elseif ($field->getType() == 'from') {
                $from = $_POST['advfrm-' . $field->getName()];
            }
        }
        if ($confirmation && empty($from)) {
            $e .= '<li>' . $this->text['error_missing_sender'] . '</li>' . "\n";
            return false;
        }

        $debug = "";
        $res = $this->mailService->sendMail($form, $from, $from_name, $type, $confirmation, $debug);
        $ok = $res === true;

        if (!$confirmation) {
            if (!$ok) {
                $message = !empty($res)
                    ? ($this->adm && $debug ? '<pre style="white-space: pre-wrap">' . $debug . '</pre>' : XH_hsc($res))
                    : $this->text['error_mail'];
                $e .= '<li>' . $message . '</li>' . "\n";
            }
            $type = $ok ? 'info' : 'error';
            $message = $ok ? $this->text['log_success'] : $this->text['log_error'];
            $message = sprintf($message, $from);
            XH_logMessage($type, 'Advancedform', $id, $message);
        }

        return $ok;
    }
}

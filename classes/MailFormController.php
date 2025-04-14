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

use Advancedform\Infra\CaptchaWrapper;
use Advancedform\Infra\HooksWrapper;
use Advancedform\Infra\Logger;
use Plib\Request;
use Plib\Response;
use Plib\View;

class MailFormController
{
    /** @var FormGateway */
    private $formGateway;

    /** @var FieldRenderer */
    private $fieldRenderer;

    /** @var Validator */
    private $validator;

    /** @var CaptchaWrapper */
    private $captchaWrapper;

    /** @var HooksWrapper */
    private $hooksWrapper;

    /** @var array<string,string> */
    private $conf;

    /** @var MailService */
    private $mailService;

    /** @var Logger */
    private $logger;

    /** @var View */
    private $view;

    /** @param array<string,string> $conf */
    public function __construct(
        FormGateway $formGateway,
        FieldRenderer $fieldRenderer,
        Validator $validator,
        CaptchaWrapper $captchaWrapper,
        HooksWrapper $hooksWrapper,
        array $conf,
        MailService $mailService,
        Logger $logger,
        View $view
    ) {
        $this->formGateway = $formGateway;
        $this->fieldRenderer = $fieldRenderer;
        $this->validator = $validator;
        $this->captchaWrapper = $captchaWrapper;
        $this->hooksWrapper = $hooksWrapper;
        $this->conf = $conf;
        $this->mailService = $mailService;
        $this->logger = $logger;
        $this->view = $view;
    }

    public function main(string $id, Request $request): Response
    {
        $this->hooksWrapper->include($id);
        if (($form = $this->formGateway->find($id)) === null) {
            return Response::create($this->view->message("fail", "error_form_missing", $id));
        }
        if ($form->getCaptcha() && !$this->captchaWrapper->include()) {
            return Response::create($this->view->message("fail", "error_captcha"));
        }
        if ($request->post("advfrm") !== $id) {
            return Response::create($this->formView($request, $form));
        }
        if (!$this->validator->check($form)) {
            return Response::create($this->renderValidationErrors() . $this->formView($request, $form));
        }
        if ($form->getStore() && !$this->appendCsv($form)) {
            return Response::create($this->view->message("fail", "error_csv") . $this->formView($request, $form));
        }
        if (!$this->mail($form, false)) {
            return Response::create($this->formView($request, $form));
        }
        if (($thanks = $this->thanksPage($id, $form)) === null) {
            return Response::create($this->mailService->mailInfo($form, false, true));
        }
        if ($this->conf['mail_confirmation'] && !$this->mail($form, true)) {
            return Response::create($this->formView($request, $form));
        }
        return Response::redirect($request->url()->page($thanks)->absolute());
    }

    /**
     * Returns the view of the form.
     *
     * @return string (X)HTML.
     */
    private function formView(Request $request, Form $form)
    {
        global $f;

        $id = $form->getName();
        return $this->view->render('mail-form', [
            'id' => $id,
            'url' =>  $request->url()->page($f === 'mailform' ? '&mailform' : $request->selected())->relative(),
            'required_message' => $this->view->plain(
                "message_required_fields",
                sprintf($this->conf["required_field_mark"], $this->view->plain("message_required_field"))
            ),
            'inner_view' => file_exists($this->formGateway->dataFolder() . $id . '.tpl')
                ? $this->templateView($form)
                : $this->defaultView($form),
            'captcha' => $form->getCaptcha() ? $this->captchaWrapper->display() : "",
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

    private function renderValidationErrors(): string
    {
        Plugin::focusField(...$this->validator->focusField());
        $o = '<ul class="advfrm-error">';
        foreach ($this->validator->errors() as $error) {
            $o .= '<li>' . $error . '</li>' . "\n";
        }
        $o .= '</ul>';
        return $o;
    }

    private function appendCsv(Form $form): bool
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
        if ($this->conf['csv_separator'] != '') {
            $separator = $this->conf['csv_separator'][0];
        } else {
            $separator = "\t";
        }
        return $this->formGateway->appendCsv($id, $fields, $separator);
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
            $e .= '<li>' . $this->view->plain("error_missing_sender") . '</li>' . "\n";
            return false;
        }

        $res = $this->mailService->sendMail($form, $from, $from_name, $type, $confirmation);
        $ok = $res === true;

        if (!$confirmation) {
            if (!$ok) {
                $message = !empty($res)
                    ? XH_hsc($res)
                    : $this->view->plain("error_mail");
                $e .= '<li>' . $message . '</li>' . "\n";
            }
            $type = $ok ? 'info' : 'error';
            $message = $ok ? $this->view->plain("log_success", $from) : $this->view->plain("log_error", $from);
            $this->logger->log($type, 'Advancedform', $id, $message);
        }

        return $ok;
    }

    private function thanksPage(string $id, Form $form): ?string
    {
        $thanks = $this->hooksWrapper->thanksPage($id, Plugin::fields());
        if (empty($thanks)) {
            $thanks = $form->getThanksPage();
        }
        return $thanks ?: null;
    }
}

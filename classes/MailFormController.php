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

use PHPMailer\PHPMailer\PHPMailer;

class MailFormController extends Controller
{
    /** @var FormGateway */
    private $formGateway;

    public function __construct(FormGateway $formGateway)
    {
        parent::__construct();
        $this->formGateway = $formGateway;
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
            $e .= '<li>' . sprintf($this->text['error_form_missing'], $id) . '</li>' . PHP_EOL;
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
            if (($res = $this->check($id)) === true) {
                if ($form->getStore()) {
                    $this->appendCsv($id);
                }
                if (!$this->mail($id, false)) {
                    return $this->formView($id);
                }
                if (function_exists('advfrm_custom_thanks_page')) {
                    Advancedform_fields($fields);
                    $thanks = advfrm_custom_thanks_page($id, $fields);
                }
                if (empty($thanks)) {
                    $thanks = $form->getThanksPage();
                }
                if (!empty($thanks)) {
                    if ($this->conf['mail_confirmation'] && !$this->mail($id, true)) {
                        return $this->formView($id);
                    }
                    header('Location: ' . $this->scriptName . '?' . $thanks);
                    // FIXME: exit()?
                } else {
                    return $this->mailInfo($id, false, true);
                }
            } else {
                return $res . $this->formView($id);
            }
        }
        return $this->formView($id);
    }

    /**
     * Returns the view of the form.
     *
     * @param string $id A form ID.
     *
     * @return string (X)HTML.
     */
    private function formView($id)
    {
        global $su, $f;

        $forms = $this->formGateway->findAll();
        $form = $forms[$id];
        $o = '';
        $url = $this->scriptName . '?' . ($f === 'mailform' ? '&mailform' : $su);
        $o .= '<div class="advfrm-mailform">' . PHP_EOL
            . '<form name="' . $id . '" action="' . $url . '" method="post"'
            . ' enctype="multipart/form-data" accept-charset="UTF-8">' . PHP_EOL
            . '<input type="hidden" name="advfrm" value="'.$id.'">' . PHP_EOL
            . '<div class="required">'
            . sprintf(
                $this->text['message_required_fields'],
                sprintf($this->conf['required_field_mark'], $this->text['message_required_field'])
            )
            . '</div>' . PHP_EOL;
        if (file_exists($this->formGateway->dataFolder() . $id . '.tpl')) {
            $o .= $this->templateView($id);
        } else {
            $o .= $this->defaultView($id);
        }
        if ($form->getCaptcha()) {
            $o .= call_user_func($this->conf['captcha_plugin'] . '_captcha_display');
        }
        $o .= '<div class="buttons">'
            . '<input type="submit" class="submit" value="'.$this->text['button_send'].'">'
            . '&nbsp;'
            . '<input type="reset" class="submit" value="'.$this->text['button_reset'].'">'
            . '</div>' . PHP_EOL;
        $o .= '</form>' . PHP_EOL . '</div>' . PHP_EOL;
        return $o;
    }

    /**
     * Returns the default view of the form.
     *
     * @param string $id A form ID.
     *
     * @return string (X)HTML.
     */
    private function defaultView($id)
    {
        $forms = $this->formGateway->findAll();
        $form = $forms[$id];

        $o = '';
        $o .= '<div style="overflow:auto">' . PHP_EOL . '<table>' . PHP_EOL;
        foreach ($form->getFields() as $field) {
            $label = XH_hsc($field->getLabel());
            $label = $field->getRequired()
                ? sprintf($this->conf['required_field_mark'], $label)
                : $label;
            $hidden = $field->getType() == 'hidden';
            $class = $hidden ? ' class="hidden"' : '';
            $field_id = 'advfrm-' . $id . '-' . $field->getName();
            $labelled = !in_array($field->getType(), array('checkbox', 'radio', 'output'));
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
            $o .= $this->displayField($id, $field);
            $o .= '</td></tr>' . PHP_EOL;
            if ($labelled && $this->conf['focus_form']) {
                Functions::focusField($id, 'advfrm-' . $field->getName());
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
     */
    private function templateView($id)
    {
        global $hjs;

        $forms = $this->formGateway->findAll();
        $fn = $this->formGateway->dataFolder() . 'css/' . $id . '.css';
        if (file_exists($fn)) {
            $hjs .= '<link rel="stylesheet" href="' . $fn . '" type="text/css">'
            . PHP_EOL;
        }
        $fn = $this->formGateway->dataFolder() . 'js/' . $id . '.js';
        if (file_exists($fn)) {
            $hjs .= '<script src="' . $fn . '"></script>'
                . PHP_EOL;
        }

        $form = $forms[$id];
        $fn = $this->formGateway->dataFolder() . $id . '.tpl'
            . ($this->conf['php_extension'] ? '.php' : '');
        $advfrm_script = file_get_contents($fn);
        foreach ($form->getFields() as $field) {
            $advfrm_script = str_replace(
                '<?field ' . $field->getName() . '?>',
                $this->displayField($id, $field),
                $advfrm_script
            );
        }
        extract($GLOBALS);
        ob_start();
        eval('?>' . $advfrm_script);
        return ob_get_clean();
    }

    /**
     * Returns the view of a form field.
     *
     * @param string $form_id A form ID.
     *
     * @return string (X)HTML.
     */
    private function displayField($form_id, Field $field)
    {
        $o = '';
        $name = 'advfrm-' . $field->getName();
        $id = 'advfrm-' . $form_id . '-' . $field->getName();
        $props = explode("\xC2\xA6", $field->getProps());
        $is_real_select = $field->isRealSelect();
        $is_multi = $field->isMulti();
        if ($field->isSelect()) {
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
            foreach ($props as $opt) {
                $opt = explode("\xE2\x97\x8F", $opt);
                if (count($opt) > 1) {
                    $f = true;
                    $opt = $opt[1];
                } else {
                    $f = false;
                    $opt = $opt[0];
                }
                if (function_exists('advfrm_custom_field_default')) {
                    $cust_f = advfrm_custom_field_default($form_id, $field->getName(), $opt, isset($_POST['advfrm']));
                }
                if (isset($cust_f)) {
                    $f = $cust_f;
                } else {
                    $f = isset($_POST['advfrm']) && isset($_POST[$name])
                        && ($is_multi
                            ? in_array($opt, $_POST[$name])
                            : $_POST[$name] == $opt)
                        || !isset($_POST['advfrm']) && $f;
                }
                $sel = $f
                    ? ($is_real_select ? ' selected="selected"' : ' checked="checked"')
                    : '';
                if ($is_real_select) {
                    $o .= '<option' . $sel . '>' . XH_hsc($opt) . '</option>';
                } else {
                    $o .= '<div class="' . $orient . '"><label>'
                        . '<input type="'.$field->getType() . '" name="' . $name
                        . $brackets . '" value="' . XH_hsc($opt) . '"'
                        . $sel . '>'
                        . '&nbsp;' . XH_hsc($opt)
                        . '</label></div>';
                }
            }
            if ($is_real_select) {
                $o .= '</select>';
            }
        } else {
            $type = in_array($field->getType(), array('file', 'password', 'hidden', 'date'))
                ? $field->getType()
                : 'text';
            if (function_exists('advfrm_custom_field_default')) {
                $val = advfrm_custom_field_default($form_id, $field->getName(), null, isset($_POST['advfrm']));
            }
            if (!isset($val)) {
                $val =  isset($_POST[$name])
                    ? $_POST[$name]
                    : $props[ADVFRM_PROP_DEFAULT];
            }
            if ($field->getType() == 'textarea') {
                $cols = empty($props[ADVFRM_PROP_COLS]) ? 40 : $props[ADVFRM_PROP_COLS];
                $rows = empty($props[ADVFRM_PROP_ROWS]) ? 4 : $props[ADVFRM_PROP_ROWS];
                $o .= '<textarea id="' . $id . '" name="' . $name . '" cols="' . $cols
                    . '" rows="' . $rows . '">'
                    . XH_hsc($val) . '</textarea>';
            } elseif ($field->getType() == 'output') {
                $o .= $val;
            } else {
                if ($field->getType() == 'date') {
                    $placeholder = '2019-03-24';
                }
                $size = $field->getType() == 'hidden' || empty($props[ADVFRM_PROP_SIZE])
                    ? ''
                    : ' size="' . $props[ADVFRM_PROP_SIZE] . '"';
                $maxlen = in_array($field->getType(), array('hidden', 'file'))
                    || empty($props[ADVFRM_PROP_MAXLEN])
                    ? ''
                    : ' maxlength="' . $props[ADVFRM_PROP_MAXLEN] . '"';
                if ($field->getType() == 'file' && !empty($props[ADVFRM_PROP_MAXLEN])) {
                    $o .= '<input type="hidden" name="MAX_FILE_SIZE" value="'
                        . $props[ADVFRM_PROP_MAXLEN] . '">';
                }
                if ($field->getType() == 'file') {
                    $value = '';
                    $accept = ' accept="'
                        . XH_hsc($this->prefixFileExtensionList($val))
                        . '"';
                } else {
                    $value = ' value="' . XH_hsc($val) . '"';
                    $accept = '';
                }
                $o .= '<input type="' . $type . '" id="' . $id . '" name="' . $name
                    . '"' . $value . $accept . $size . $maxlen
                    . (isset($placeholder) ? (' placeholder="' . $placeholder . '"') : '')
                    . '>';
            }
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
    private function prefixFileExtensionList($list)
    {
        $extensions = explode(',', $list);
        $func = function ($x) {
            return '.' . trim($x);
        };
        $extensions = array_map($func, $extensions);
        $list = implode(',', $extensions);
        return $list;
    }

    /**
     * Checks sent form. Returns true on success, an (X)HTML error message on failure.
     *
     * @param string $id A form ID.
     *
     * @return mixed
     */
    private function check($id)
    {
        $o = '';
        $forms = $this->formGateway->findAll();
        $form = $forms[$id];
        foreach ($form->getFields() as $field) {
            $name = 'advfrm-' . $field->getName();
            if ($field->getType() != 'file' && $field->getType() != 'multi_select'
                && (!isset($_POST[$name]) || $_POST[$name] == '')
                || $field->getType() == 'file' && empty($_FILES[$name]['name'])
                || $field->getType() == 'multi_select'
                && (!isset($_POST[$name])
                || count($_POST[$name]) == 1 && empty($_POST[$name][0]))
            ) {
                if ($field->getRequired()) {
                    $o .= '<li>'
                        . sprintf(
                            $this->text['error_missing_field'],
                            XH_hsc($field->getLabel())
                        )
                        . '</li>' . PHP_EOL;
                    Functions::focusField($id, $name);
                }
            } else {
                switch ($field->getType()) {
                    case 'from':
                    case 'mail':
                        if (!preg_match($this->conf['mail_regexp'], $_POST[$name])) {
                            $o .= '<li>'
                                . sprintf(
                                    $this->text['error_invalid_email'],
                                    XH_hsc($field->getLabel())
                                )
                                . '</li>' . PHP_EOL;
                            Functions::focusField($id, $name);
                        }
                        break;
                    case 'date':
                        $pattern = '/^([0-9]+)-([0-9]+)-([0-9]+)$/';
                        $matched = preg_match($pattern, $_POST[$name], $matches);
                        if (count($matches) == 4) {
                            $year = $matches[1];
                            $month = $matches[2];
                            $day = $matches[3];
                        }
                        if (!$matched || !checkdate($month, $day, $year)) {
                            $o .= '<li>'
                                . sprintf(
                                    $this->text['error_invalid_date'],
                                    XH_hsc($field->getLabel())
                                )
                                .'</li>' . PHP_EOL;
                            Functions::focusField($id, $name);
                        }
                        break;
                    case 'number':
                        if (!ctype_digit($_POST[$name])) {
                            $o .= '<li>'
                                . sprintf(
                                    $this->text['error_invalid_number'],
                                    XH_hsc($field->getLabel())
                                )
                                . '</li>' . PHP_EOL;
                            Functions::focusField($id, $name);
                        }
                        break;
                    case 'file':
                        $props = explode("\xC2\xA6", $field->getProps());
                        switch ($_FILES[$name]['error']) {
                            case UPLOAD_ERR_OK:
                                if (!empty($props[ADVFRM_PROP_MAXLEN])
                                    && $_FILES[$name]['size'] > $props[ADVFRM_PROP_MAXLEN]
                                ) {
                                    $o .= '<li>'
                                        . sprintf(
                                            $this->text['error_upload_too_large'],
                                            XH_hsc($field->getLabel())
                                        )
                                        . '</li>' . PHP_EOL;
                                    Functions::focusField($id, $name);
                                }
                                break;
                            case UPLOAD_ERR_INI_SIZE:
                            case UPLOAD_ERR_FORM_SIZE:
                                $o .= '<li>'
                                    . sprintf(
                                        $this->text['error_upload_too_large'],
                                        XH_hsc($field->getLabel())
                                    )
                                    . '</li>' . PHP_EOL;
                                Functions::focusField($id, $name);
                                break;
                            default:
                                $o .= '<li>'
                                    . sprintf(
                                        $this->text['error_upload_general'],
                                        XH_hsc($field->getLabel())
                                    )
                                    . '</li>' . PHP_EOL;
                                Functions::focusField($id, $name);
                        }
                        $ext = pathinfo($_FILES[$name]['name'], PATHINFO_EXTENSION);
                        if (!$this->isFileTypeAllowed($ext, $props)) {
                            $o .= '<li>'
                                . sprintf(
                                    $this->text['error_upload_illegal_ftype'],
                                    XH_hsc($field->getLabel()),
                                    XH_hsc($ext)
                                )
                                . '</li>' . PHP_EOL;
                            Functions::focusField($id, $name);
                        }
                        break;
                    case 'custom':
                        $props = explode("\xC2\xA6", $field->getProps());
                        $pattern = $props[ADVFRM_PROP_CONSTRAINT];
                        if (!empty($pattern)
                            && !preg_match($pattern, $_POST[$name])
                        ) {
                            $msg = empty($props[ADVFRM_PROP_ERROR_MSG])
                                ? $this->text['error_invalid_custom']
                                : $props[ADVFRM_PROP_ERROR_MSG];
                            $o .= '<li>' . sprintf($msg, $field->getLabel()) . '</li>'
                                . PHP_EOL;
                            Functions::focusField($id, $name);
                        }
                }
                if (function_exists('advfrm_custom_valid_field')) {
                    $value = $field->getType() == 'file'
                        ? $_FILES[$name]
                        : $_POST[$name];
                    $valid = advfrm_custom_valid_field($id, $field->getName(), $value);
                    if ($valid !== true) {
                        $o .= '<li>' . $valid . '</li>' . PHP_EOL;
                        Functions::focusField($id, $name);
                    }
                }
            }
        }
        if ($form->getCaptcha()) {
            if (!call_user_func($this->conf['captcha_plugin'] . '_captcha_check')) {
                $o .= '<li>' . $this->text['error_captcha_code'] . '</li>' . PHP_EOL;
                Functions::focusField($id, 'advancedform-captcha');
            }
        }
        return $o == ''
            ? true
            : '<ul class="advfrm-error">' . PHP_EOL . $o . '</ul>' . PHP_EOL;
    }

    /**
     * @param string $extension
     * @return bool
     */
    private function isFileTypeAllowed($extension, array $properties)
    {
        if (trim($properties[ADVFRM_PROP_FTYPES]) === '') {
            return false;
        }
        $types = explode(',', $properties[ADVFRM_PROP_FTYPES]);
        foreach ($types as $type) {
            if (!strcasecmp($extension, trim($type))) {
                return true;
            };
        }
        return false;
    }

    /**
     * Appends the posted record to csv file.
     *
     * @param string $id A form ID.
     *
     * @return void
     */
    private function appendCsv($id)
    {
        $forms = $this->formGateway->findAll();
        $fields = array();
        foreach ($forms[$id]->getFields() as $field) {
            if ($field->getType() != 'output') {
                $name = $field->getName();
                $val = ($field->getType() == 'file')
                    ? $_FILES['advfrm-'.$name]['name']
                    : $_POST['advfrm-'.$name];
                $fields[] = is_array($val)
                    ? implode("\xC2\xA6", $val)
                    : $val;
            }
        }
        if ($this->conf['csv_separator'] != '') {
            $fields = array_map([$this, 'escapeCsvField'], $fields);
            $separator = $this->conf['csv_separator'];
        } else {
            $separator = "\t";
        }
        $fn = $this->formGateway->dataFolder() . $id . '.csv';
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
     * Escapes a field value for use in a CSV file.
     *
     * @param string $field A field value.
     *
     * @return string
     */
    private function escapeCsvField($field)
    {
        $specialChars = "\"\r\n" . $this->conf['csv_separator'];
        $specialChars = preg_quote($specialChars, '/');
        if (preg_match('/[' . $specialChars . ']/', $field)) {
            $field = str_replace('"', '""', $field);
            $field = '"' . $field . '"';
        }
        return $field;
    }

    /**
     * Sends the mail and returns whether that was successful.
     *
     * @param string $id           A form ID.
     * @param bool   $confirmation Whether to send the confirmation mail.
     *
     * @return bool
     */
    private function mail($id, $confirmation)
    {
        global $sl, $e;

        include_once "{$this->pluginsFolder}advancedform/phpmailer/PHPMailer.php";
        include_once "{$this->pluginsFolder}advancedform/phpmailer/Exception.php";
        $forms = $this->formGateway->findAll();
        $form = $forms[$id];
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
            $e .= '<li>' . $this->text['error_missing_sender'] . '</li>' . PHP_EOL;
            return false;
        }

        $mail = new PHPMailer();
        $mail->set('CharSet', 'UTF-8');
        $mail->SetLanguage(
            $sl,
            $this->pluginsFolder . 'advancedform/phpmailer/language/'
        );
        $mail->set('WordWrap', 72);
        if ($confirmation) {
            $mail->set('From', $form->getTo());
            $mail->set('FromName', $form->getToName());
            $mail->AddAddress($from, $from_name);
        } else {
            $mail->set('From', $form->getTo());
            $mail->set('FromName', $form->getToName());
            $mail->AddReplyTo($from, $from_name);
            $mail->AddAddress($form->getTo(), $form->getToName());
            foreach (explode(';', $form->getCc()) as $cc) {
                if (trim($cc) != '') {
                    $mail->AddCC($cc);
                }
            }
            foreach (explode(';', $form->getBcc()) as $bcc) {
                if (trim($bcc) != '') {
                    $mail->AddBCC($bcc);
                }
            }
        }
        if ($confirmation) {
            $mail->set(
                'Subject',
                sprintf($this->text['mail_subject_confirmation'], $form->getTitle(), $_SERVER['SERVER_NAME'])
            );
        } else {
            $mail->set(
                'Subject',
                sprintf($this->text['mail_subject'], $form->getTitle(), $_SERVER['SERVER_NAME'])
            );
        }
        $mail->IsHtml($type != 'text');
        if ($type == 'text') {
            $mail->set('Body', $this->mailBody($id, !$confirmation, false));
        } else {
            $body = $this->mailBody($id, !$confirmation, true);
            $mail->MsgHTML($body);
            $mail->set('AltBody', $this->mailBody($id, !$confirmation, false));
        }
        if (!$confirmation) {
            foreach ($form->getFields() as $field) {
                if ($field->getType() == 'file') {
                    $name = 'advfrm-' . $field->getName();
                    if ($_FILES[$name]['error'] === UPLOAD_ERR_OK) {
                        $mail->AddAttachment($_FILES[$name]['tmp_name'], $_FILES[$name]['name']);
                    }
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
                    ? XH_hsc($mail->ErrorInfo)
                    : $this->text['error_mail'];
                $e .= '<li>' . $message . '</li>' . PHP_EOL;
            }
            $type = $ok ? 'info' : 'error';
            $message = $ok ? $this->text['log_success'] : $this->text['log_error'];
            $message = sprintf($message, $from);
            XH_logMessage($type, 'Advancedform', $id, $message);
        }

        return $ok;
    }

    /**
     * Returns the body of the mail.
     *
     * @param string $id          A form ID.
     * @param bool   $show_hidden Whether to include hidden fields.
     * @param bool   $html        Whether to return (X)HTML.
     *
     * @return string
     */
    private function mailBody($id, $show_hidden, $html)
    {
        $o = '';
        if ($html) {
            $o .= '<!DOCTYPE html>' . PHP_EOL;
            $o .= '<head>' . PHP_EOL . '<style type="text/css">' . PHP_EOL;
            $o .= $this->mailCss(
                $this->pluginsFolder . 'advancedform/css/stylesheet.css'
            );
            $fn = $this->formGateway->dataFolder() . 'css/' . $id . '.css';
            if (file_exists($fn)) {
                $o .= $this->mailCss($fn);
            }
            $o .= '</style>' . PHP_EOL . '</head>' . PHP_EOL . '<body>' . PHP_EOL;
        }
        $o .= $this->mailInfo($id, $show_hidden, $html);
        if ($html) {
            $o .= '</body>' . PHP_EOL . '</html>' . PHP_EOL;
        }
        return $o;
    }

    /**
     * Returns the information sent/to send.
     *
     * @param string $id          A form ID.
     * @param bool   $show_hidden Whether to include hidden fields.
     * @param bool   $html        Whether to return (X)HTML.
     *
     * @return string
     */
    private function mailInfo($id, $show_hidden, $html)
    {
        $forms = $this->formGateway->findAll();
        $form = $forms[$id];
        $o = '';
        if ($html) {
            $o .= '<div class="advfrm-mailform">' . PHP_EOL;
        }
        if (!$show_hidden) {
            $o .= $html
                ? '<p>' . $this->text['message_sent_info'] . '</p>' . PHP_EOL
                : strip_tags($this->text['message_sent_info']) . PHP_EOL . PHP_EOL;
        }
        if ($html) {
            $o .= '<table>' . PHP_EOL;
        }
        foreach ($form->getFields() as $field) {
            $o .= $this->mailFieldInfo($field, $show_hidden, $html);
        }
        if ($html) {
            $o .= '</table>' . PHP_EOL . '</div>' . PHP_EOL;
        }
        return $o;
    }

    /**
     * @return string
     */
    private function mailFieldInfo(Field $field, $show_hidden, $html)
    {
        $o = '';
        if (($field->getType() != 'hidden' || $show_hidden)
            && $field->getType() != 'output'
        ) {
            $name = 'advfrm-' . $field->getName();
            if ($html) {
                $o .= '<tr><td class="label">' . XH_hsc($field->getLabel())
                    . '</td><td class="field">';
            } else {
                $o .= $field->getLabel() . PHP_EOL;
            }
            if (isset($_POST[$name])) {
                if (is_array($_POST[$name])) {
                    foreach ($_POST[$name] as $val) {
                        $o .= $html
                            ? '<div>' . XH_hsc($val) . '</div>'
                            : '  ' . $val . PHP_EOL;
                    }
                } else {
                    $val = $_POST[$name];
                    if ($field->getType() === 'date') {
                        $val = $this->formatDate($val);
                    }
                    $o .= $html
                        ? nl2br(XH_hsc($val))
                        : '  ' . $this->indent($val) . PHP_EOL;
                }
            } elseif (isset($_FILES[$name])) {
                $o .= $html
                    ? $_FILES[$name]['name']
                    : '  ' . $_FILES[$name]['name'] . PHP_EOL;
            }
            if ($html) {
                $o .= '</td></tr>' . PHP_EOL;
            }
        }
        return $o;
    }

    /**
     * @param string $date ISO-8061
     * @return string
     */
    private function formatDate($date)
    {
        if ($date) {
            list($year, $month, $day) = explode('-', $date);
            $timestamp = mktime(null, null, null, $month, $day, $year);
            return date($this->text['date_format'], $timestamp);
        } else {
            return '';
        }
    }

    /**
     * Returns string with two spaces inserted after all linebreaks.
     *
     * @param string $string A string.
     *
     * @return string
     */
    private function indent($string)
    {
        return preg_replace('/(\r\n|\n\r|\n|\r)/su', '$1  ', $string);
    }

    /**
     * Returns the top of a CSS file, i.e. everything above the comment line:
     * <i>END OF MAIL CSS</i>. If the file couldn't be read, returns an empty string.
     *
     * @param string $fn A CSS file name.
     *
     * @return string
     */
    private function mailCss($fn)
    {
        if (($css = file_get_contents($fn)) !== false) {
            $css = explode('/* END OF MAIL CSS */', $css);
            return $css[0];
        } else {
            return '';
        }
    }
}

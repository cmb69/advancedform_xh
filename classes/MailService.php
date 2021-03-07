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

class MailService
{
    /** @var string */
    private $dataFolder;

    /** @var string */
    private $pluginsFolder;

    /** @var array<string,string> */
    private $text;

    /**
     * @param string $dataFolder
     * @param string $pluginsFolder
     * @param array<string,string> $text
     */
    public function __construct($dataFolder, $pluginsFolder, array $text)
    {
        $this->dataFolder = $dataFolder;
        $this->pluginsFolder = $pluginsFolder;
        $this->text = $text;
    }

    /**
     * @param string $from
     * @param string $from_name
     * @param string $type
     * @param bool $confirmation
     * @return bool|string true on success, error info text in case of failure
     */
    public function sendMail(Form $form, $from, $from_name, $type, $confirmation)
    {
        global $sl;

        include_once "{$this->pluginsFolder}advancedform/phpmailer/PHPMailer.php";
        include_once "{$this->pluginsFolder}advancedform/phpmailer/Exception.php";
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
            $mail->set('Body', $this->mailBody($form, !$confirmation, false));
        } else {
            $body = $this->mailBody($form, !$confirmation, true);
            $mail->MsgHTML($body);
            $mail->set('AltBody', $this->mailBody($form, !$confirmation, false));
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
            if (advfrm_custom_mail($form->getName(), $mail, $confirmation) === false) {
                return true;
            }
        }

        return $mail->Send()
            ? true
            : $mail->ErrorInfo;
    }

    /**
     * @param bool $show_hidden
     * @param bool $html
     * @return string
     */
    private function mailBody(Form $form, $show_hidden, $html)
    {
        $o = '';
        if ($html) {
            $o .= '<!DOCTYPE html>' . PHP_EOL;
            $o .= '<head>' . PHP_EOL . '<style type="text/css">' . PHP_EOL;
            $o .= $this->mailCss(
                $this->pluginsFolder . 'advancedform/css/stylesheet.css'
            );
            $fn = $this->dataFolder . 'css/' . $form->getName() . '.css';
            if (file_exists($fn)) {
                $o .= $this->mailCss($fn);
            }
            $o .= '</style>' . PHP_EOL . '</head>' . PHP_EOL . '<body>' . PHP_EOL;
        }
        $o .= $this->mailInfo($form, $show_hidden, $html);
        if ($html) {
            $o .= '</body>' . PHP_EOL . '</html>' . PHP_EOL;
        }
        return $o;
    }

    /**
     * Returns the top of a CSS file, i.e. everything above the comment line:
     * <i>END OF MAIL CSS</i>. If the file couldn't be read, returns an empty string.
     *
     * @param string $fn     *
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

    /**
     * Returns the information sent/to send.
     *
     * @param bool $show_hidden
     * @param bool $html
     * @return string
     */
    public function mailInfo(Form $form, $show_hidden, $html)
    {
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
     * @param bool $show_hidden
     * @param bool $html
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
            $timestamp = mktime(0, 0, 0, (int) $month, (int) $day, (int) $year);
            return date($this->text['date_format'], $timestamp);
        } else {
            return '';
        }
    }

    /**
     * Returns string with two spaces inserted after all linebreaks.
     *
     * @param string $string
     * @return string
     */
    private function indent($string)
    {
        return preg_replace('/(\r\n|\n\r|\n|\r)/su', '$1  ', $string);
    }
}

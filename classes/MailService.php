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

use Advancedform\PHPMailer\PHPMailer;

class MailService
{
    /** @var string */
    private $dataFolder;

    /** @var string */
    private $pluginFolder;

    /** @var array<string,string> */
    private $text;

    /** @var PHPMailer */
    private $mailer;

    /**
     * @param string $dataFolder
     * @param string $pluginFolder
     * @param array<string,string> $text
     */
    public function __construct($dataFolder, $pluginFolder, array $text, PHPMailer $mailer)
    {
        $this->dataFolder = $dataFolder;
        $this->pluginFolder = $pluginFolder;
        $this->text = $text;
        $this->mailer = $mailer;
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
        $this->mailer->set('CharSet', 'UTF-8');
        $this->mailer->set('WordWrap', 72);
        if ($confirmation) {
            $this->mailer->set('From', $form->getTo());
            $this->mailer->set('FromName', $form->getToName());
            $this->mailer->AddAddress($from, $from_name);
        } else {
            $this->mailer->set('From', $form->getTo());
            $this->mailer->set('FromName', $form->getToName());
            $this->mailer->AddReplyTo($from, $from_name);
            $this->mailer->AddAddress($form->getTo(), $form->getToName());
            foreach (explode(';', $form->getCc()) as $cc) {
                if (trim($cc) != '') {
                    $this->mailer->AddCC($cc);
                }
            }
            foreach (explode(';', $form->getBcc()) as $bcc) {
                if (trim($bcc) != '') {
                    $this->mailer->AddBCC($bcc);
                }
            }
        }
        if ($confirmation) {
            $this->mailer->set(
                'Subject',
                sprintf($this->text['mail_subject_confirmation'], $form->getTitle(), $_SERVER['SERVER_NAME'])
            );
        } else {
            $this->mailer->set(
                'Subject',
                sprintf($this->text['mail_subject'], $form->getTitle(), $_SERVER['SERVER_NAME'])
            );
        }
        $this->mailer->IsHtml($type != 'text');
        if ($type == 'text') {
            $this->mailer->set('Body', $this->mailBody($form, !$confirmation, false));
        } else {
            $body = $this->mailBody($form, !$confirmation, true);
            $this->mailer->MsgHTML($body);
            $this->mailer->set('AltBody', $this->mailBody($form, !$confirmation, false));
        }
        if (!$confirmation) {
            foreach ($form->getFields() as $field) {
                if ($field->getType() == 'file') {
                    $name = 'advfrm-' . $field->getName();
                    if (array_key_exists($name, $_FILES) && $_FILES[$name]['error'] === UPLOAD_ERR_OK) {
                        $this->mailer->AddAttachment($_FILES[$name]['tmp_name'], $_FILES[$name]['name']);
                    }
                }
            }
        }

        if (function_exists('advfrm_custom_mail')) {
            if (advfrm_custom_mail($form->getName(), $this->mailer, $confirmation) === false) {
                return true;
            }
        }

        return $this->mailer->Send()
            ? true
            : $this->mailer->ErrorInfo;
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
            $o .= '<!DOCTYPE html>' . "\n";
            $o .= '<head>' . "\n" . '<style type="text/css">' . "\n";
            $o .= $this->mailCss(
                $this->pluginFolder . 'css/stylesheet.css'
            );
            $fn = $this->dataFolder . 'css/' . $form->getName() . '.css';
            if (file_exists($fn)) {
                $o .= $this->mailCss($fn);
            }
            $o .= '</style>' . "\n" . '</head>' . "\n" . '<body>' . "\n";
        }
        $o .= $this->mailInfo($form, $show_hidden, $html);
        if ($html) {
            $o .= '</body>' . "\n" . '</html>' . "\n";
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
            $o .= '<div class="advfrm-mailform">' . "\n";
        }
        if (!$show_hidden) {
            $o .= $html
                ? '<p>' . $this->text['message_sent_info'] . '</p>' . "\n"
                : strip_tags($this->text['message_sent_info']) . "\n" . "\n";
        }
        if ($html) {
            $o .= '<table>' . "\n";
        }
        foreach ($form->getFields() as $field) {
            $o .= $this->mailFieldInfo($field, $show_hidden, $html);
        }
        if ($html) {
            $o .= '</table>' . "\n" . '</div>' . "\n";
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
        if (
            ($field->getType() != 'hidden' || $show_hidden)
            && $field->getType() != 'output'
        ) {
            $name = 'advfrm-' . $field->getName();
            if ($html) {
                $o .= '<tr><td class="label">' . XH_hsc($field->getLabel())
                    . '</td><td class="field">';
            } else {
                $o .= $field->getLabel() . "\n";
            }
            if (isset($_POST[$name])) {
                if (is_array($_POST[$name])) {
                    foreach ($_POST[$name] as $val) {
                        $o .= $html
                            ? '<div>' . XH_hsc($val) . '</div>'
                            : '  ' . $val . "\n";
                    }
                } else {
                    $val = $_POST[$name];
                    if ($field->getType() === 'date') {
                        $val = $this->formatDate($val);
                    }
                    $o .= $html
                        ? nl2br(XH_hsc($val))
                        : '  ' . $this->indent($val) . "\n";
                }
            } elseif (isset($_FILES[$name])) {
                $o .= $html
                    ? $_FILES[$name]['name']
                    : '  ' . $_FILES[$name]['name'] . "\n";
            }
            if ($html) {
                $o .= '</td></tr>' . "\n";
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

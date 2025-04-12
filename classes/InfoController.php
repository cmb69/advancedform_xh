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

class InfoController
{
    /** @var FormGateway */
    private $formGateway;

    /** @var string */
    private $pluginsFolder;

    /** @var array<string,string> */
    private $conf;

    /** @var array<string,string> */
    private $text;

    /**
     * @param string $pluginsFolder
     * @param array<string,string> $conf
     * @param array<string,string> $text
     */
    public function __construct(FormGateway $formGateway, $pluginsFolder, array $conf, array $text)
    {
        $this->formGateway = $formGateway;
        $this->pluginsFolder = $pluginsFolder;
        $this->conf = $conf;
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function infoAction()
    {
        return '<h1>Advancedform ' . Plugin::VERSION . '</h1>' . "\n"
            . $this->systemCheck();
    }

    /**
     * Returns requirements information.
     *
     * @return string (X)HTML
     */
    private function systemCheck()
    {
        $o = '<h2>' . $this->text['syscheck_title'] . '</h2>';
        $phpversion = '7.1.0';
        $o .= XH_message($this->checkPhpVersion($phpversion), $this->text['syscheck_phpversion'], $phpversion);
        foreach (array('ctype', 'filter', 'hash') as $ext) {
            $o .= XH_message($this->checkExtension($ext), $this->text['syscheck_extension'], $ext);
        }
        $xhversion = '1.7.0';
        $o .= XH_message($this->checkXhVersion($xhversion), $this->text['syscheck_xhversion'], $xhversion);
        foreach (array('fa', 'jquery') as $plugin) {
            $o .= XH_message($this->checkPlugin($plugin), $this->text['syscheck_plugin'], ucfirst($plugin));
        }
        $o .= XH_message($this->checkCaptchaPlugin(), $this->text['syscheck_captcha_plugin']);
        $o .= XH_message($this->checkCaptchaKey(), $this->text['syscheck_captcha_key']);
        foreach (array('config/', 'css/', 'languages/') as $folder) {
            $folders[] = $this->pluginsFolder . 'advancedform/' . $folder;
        }
        $folders[] = $this->formGateway->dataFolder();
        foreach ($folders as $folder) {
            $o .= XH_message($this->checkWritability($folder), $this->text['syscheck_writable'], $folder);
        }
        return $o;
    }

    /**
     * @param string $version
     * @return string
     */
    private function checkPhpVersion($version)
    {
        return version_compare(PHP_VERSION, $version) >= 0 ? 'success' : 'fail';
    }

    /**
     * @param string $extension
     * @return string
     */
    private function checkExtension($extension)
    {
        return extension_loaded($extension) ? 'success' : 'fail';
    }

    /**
     * @param string $version
     * @return string
     */
    private function checkXhVersion($version)
    {
        return version_compare(CMSIMPLE_XH_VERSION, "CMSimple_XH $version") >= 0 ? 'success' : 'fail';
    }

    /**
     * @param string $plugin
     * @return string
     */
    private function checkPlugin($plugin)
    {
        $filename = $this->pluginsFolder . $plugin;
        return is_dir($filename) ? 'success' : 'fail';
    }

    /**
     * @return string
     */
    private function checkCaptchaPlugin()
    {
        $filename = $this->pluginsFolder
            . $this->conf['captcha_plugin'] . '/captcha.php';
        return is_file($filename) ? 'success' : 'warning';
    }

    /**
     * @return string
     */
    private function checkCaptchaKey()
    {
        return !empty($this->conf['captcha_key']) ? 'success' : 'warning';
    }

    /**
     * @param string $folder
     * @return string
     */
    private function checkWritability($folder)
    {
        return is_writable($folder) ? 'success' : 'warning';
    }
}

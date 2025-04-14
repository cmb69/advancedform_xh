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

use Plib\Response;
use Plib\SystemChecker;
use Plib\View;

class InfoController
{
    /** @var FormGateway */
    private $formGateway;

    /** @var string */
    private $pluginsFolder;

    /** @var array<string,string> */
    private $conf;

    /** @var SystemChecker */
    private $systemChecker;

    /** @var View */
    private $view;

    /** @param array<string,string> $conf */
    public function __construct(
        FormGateway $formGateway,
        string $pluginsFolder,
        array $conf,
        SystemChecker $systemChecker,
        View $view
    ) {
        $this->formGateway = $formGateway;
        $this->pluginsFolder = $pluginsFolder;
        $this->conf = $conf;
        $this->systemChecker = $systemChecker;
        $this->view = $view;
    }

    public function __invoke(): Response
    {
        return Response::create('<h1>Advancedform ' . Plugin::VERSION . '</h1>' . "\n"
            . $this->systemCheck());
    }

    private function systemCheck(): string
    {
        $o = "<h2>" . $this->view->text("syscheck_title") . "</h2>\n";
        $phpversion = '7.1.0';
        $o .= $this->view->message($this->checkPhpVersion($phpversion), "syscheck_phpversion", $phpversion);
        foreach (array('ctype', 'filter', 'hash') as $ext) {
            $o .= $this->view->message($this->checkExtension($ext), "syscheck_extension", $ext);
        }
        $xhversion = '1.7.0';
        $o .= $this->view->message($this->checkXhVersion($xhversion), "syscheck_xhversion", $xhversion);
        foreach (array('jquery') as $plugin) {
            $o .= $this->view->message($this->checkPlugin($plugin), "syscheck_plugin", ucfirst($plugin));
        }
        $o .= $this->view->message($this->checkCaptchaPlugin(), "syscheck_captcha_plugin");
        $o .= $this->view->message($this->checkCaptchaKey(), "syscheck_captcha_key");
        foreach (array('config/', 'css/', 'languages/') as $folder) {
            $folders[] = $this->pluginsFolder . 'advancedform/' . $folder;
        }
        $folders[] = $this->formGateway->dataFolder();
        foreach ($folders as $folder) {
            $o .= $this->view->message($this->checkWritability($folder), "syscheck_writable", $folder);
        }
        return $o;
    }

    private function checkPhpVersion(string $version): string
    {
        return $this->systemChecker->checkVersion(PHP_VERSION, $version) ? "success" : "fail";
    }

    private function checkExtension(string $extension): string
    {
        return $this->systemChecker->checkExtension($extension) ? "success" : "fail";
    }

    private function checkXhVersion(string $version): string
    {
        return $this->systemChecker->checkVersion(CMSIMPLE_XH_VERSION, "CMSimple_XH $version") ? "success" : "fail";
    }

    private function checkPlugin(string $plugin): string
    {
        return $this->systemChecker->checkPlugin($plugin) ? "success" : "fail";
    }

    private function checkCaptchaPlugin(): string
    {
        $filename = $this->pluginsFolder
            . $this->conf['captcha_plugin'] . '/captcha.php';
        return is_file($filename) ? 'success' : 'warning';
    }

    private function checkCaptchaKey(): string
    {
        return !empty($this->conf['captcha_key']) ? 'success' : 'warning';
    }

    private function checkWritability(string $folder): string
    {
        return $this->systemChecker->checkWritability($folder) ? "success" : "warning";
    }
}

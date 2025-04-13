<?php

require_once "../../cmsimple/classes/CSRFProtection.php";
require_once "../../cmsimple/classes/Pages.php";
require_once "../../cmsimple/functions.php";
require_once "../../cmsimple/utf8.php";

require_once "../plib/classes/Codec.php";
require_once "../plib/classes/CsrfProtector.php";
require_once "../plib/classes/Random.php";
require_once "../plib/classes/Request.php";
require_once "../plib/classes/Response.php";
require_once "../plib/classes/SystemChecker.php";
require_once "../plib/classes/Url.php";
require_once "../plib/classes/View.php";
require_once "../plib/classes/FakeRequest.php";
require_once "../plib/classes/FakeSystemChecker.php";

require_once "./classes/infra/Logger.php";
require_once "./classes/Captcha.php";
require_once "./classes/Dic.php";
require_once "./classes/Field.php";
require_once "./classes/FieldRenderer.php";
require_once "./classes/Form.php";
require_once "./classes/FormGateway.php";
require_once "./classes/InfoController.php";
require_once "./classes/MailFormController.php";
require_once "./classes/MailService.php";
require_once "./classes/MainAdminController.php";
require_once "./classes/Plugin.php";
require_once "./classes/Validator.php";

const CMSIMPLE_XH_VERSION = "CMSimple_XH 1.7.6";

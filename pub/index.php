<?php
date_default_timezone_set('Europe/Amsterdam');
define('APP_ROOT', realpath(dirname(__DIR__)));
define('APP_CONF', APP_ROOT . '/etc/app.ini');
define('LIB_ROOT', APP_ROOT . '/lib');
define('USR_ROOT', APP_ROOT . '/usr');
set_include_path(LIB_ROOT . ':' . USR_ROOT);

spl_autoload_extensions('.class.php');
spl_autoload_register();

new \app\app();

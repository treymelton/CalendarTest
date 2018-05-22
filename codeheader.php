<?php
session_start();
error_reporting (E_ALL ^ E_WARNING ^ E_PARSE ^ E_COMPILE_ERROR ^ E_NOTICE);
ini_set('log_errors', 1);
ini_set('display_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/Logs/error.log');
header( "Expires: Mon, 20 Dec 1998 01:00:00 GMT" );
header( "Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT" );
header( "Cache-Control: no-cache, must-revalidate" );
header( "Pragma: no-cache" );
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'BaseClass.php');
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'Calendar.php');
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'HTMLHelper.php');
?>
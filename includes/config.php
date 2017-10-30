<?php
error_reporting(E_ERROR | E_PARSE);
session_start();
header("Access-Control-Allow-Origin: *");

// Application Variables
$isWindows = false;
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $isWindows = true;
}

$away_timeout = 20;

$bin = '/var/www/html/whoishome/bin/';
$data = '/var/www/html/whoishome/data/';

$smtp_host = "mail.domain.com";
$smtp_user = "security@domain.com";
$smtp_pass = "password";

$alert_from = "security@domain.com";
$alert_email = "1112223333@vtext.com";  // Verizon Email to SMS gateway

$debug = @$_GET['debug'];
?>

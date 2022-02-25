<?php


require_once("vendor/autoload.php");
require_once("stdlib.php");

error_reporting(0);
if ($_COOKIE['debug'] == 'true') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(-1);
}

spl_autoload_register(function ($class_name) {
    if ($class_name != 'EC2RoleForAWSCodeDeploy') {
        /** @noinspection PhpIncludeInspection */
        include 'classes/' . $class_name . '.php';
    }
});

// Get sensitive values
$ini = parse_ini_file("config.ini", true)["mi"];

try {
    $pdo = new PDO(
        'mysql:host=' . $ini['db_ip'] . ';dbname=Michael;charset=utf8mb4',
        $ini['db_username'],
        $ini['db_password'],
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
            PDO::ATTR_PERSISTENT => false
        )
    );
} catch (Exception $e) {
    http_response_code(500);
    exit($e->getMessage());
}

$config = array(
    'dbo' => $pdo,
    'appName' => 'Michael',
    'pageTitles' => 'Michael - Personal CRM',
    'baseAuthURL' => $ini['link']
);

if (devEnv()) {
    putenv('HOME=/Users/ryan');

    $config['aws'] = array(
        'region'  => 'us-east-1',
        'version' => 'latest',
        'profile' => 'personal');
} else {
    $config['aws'] = array(
        'region'  => 'us-east-1',
        'version' => 'latest'
    );
}

$errors = array();

date_default_timezone_set("America/New_York");

// Start session if not already created
if (session_status() == PHP_SESSION_NONE) {
    session_name("mi");
    session_start();
}

$errors = array();
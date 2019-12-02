<?php
/**
 * @Author: Ben
 * @Date: 2016-11-16 14:42:55
 * @Project: CodeSet
 * @File Name: config.php
 * @Last Modified by:   Ben
 * @Last Modified time: 2017-08-24 11:52:00
**/

/*SERVER SETUP*/

    $DIR = $_SERVER['DOCUMENT_ROOT'] . '/';
    $PUBLIC_DIR = "/";

    //Define the database details
    $dbs = array(
        "CODESET_AUTH" => array(
            "dbname" => "***********",
            "username" => "***********",
            "password" => "***********",
            "host" => "***********"
        ),
        "CODESET_CLASSES" => array(
            "dbname" => "***********",
            "username" => "***********",
            "password" => "***********",
            "host" => "***********"
        ),
        "CODESET_THESTRAL" => array(
            "dbname" => "***********",
            "username" => "***********",
            "password" => "***********",
            "host" => "***********"
        ),
        "CODESET_NEWS" => array(
            "dbname" => "***********",
            "username" => "***********",
            "password" => "***********",
            "host" => "***********"
        ),
        "CODESET_CONTACT" => array(
            "dbname" => "***********",
            "username" => "***********",
            "password" => "***********",
            "host" => "***********"
        )
    );

    //Set default timezone for all date/time functions
    date_default_timezone_set('Europe/London');

/*INCLUDES SETUP*/

    //Set up composer
    require_once $DIR.'vendor/autoload.php';

    //Set up CodeSet Mail
    require_once $DIR.'private_server/includes/CodeSetMail/class.mail.php';
    $codesetMail = new CodeSet\SendMail('587', 'server@codeset.co.uk', '***********');
    
    //Set up CodeSet Logs
    require_once $DIR.'private_server/includes/CodeSetLogs/class.logs.php';
    $codesetLogs = new CodeSet\Logs($DIR."CodeSetLog.txt", $codesetMail);

    //Set up PHPAuth system
    $authDBH = new PDO("mysql:host=".$dbs['CODESET_AUTH']['host'].";dbname=".$dbs['CODESET_AUTH']['dbname'], $dbs['CODESET_AUTH']['username'], $dbs['CODESET_AUTH']['password']);
    $config = new PHPAuth\Config($authDBH);
    $auth = new PHPAuth\Auth($authDBH, $config, "en_GB", $codesetMail);

    //Set up CodeSet Classes
    require_once $DIR.'private_server/includes/CodeSetClasses/class.classes.php';
    $classesDBH = new PDO("mysql:host=".$dbs['CODESET_CLASSES']['host'].";dbname=".$dbs['CODESET_CLASSES']['dbname'], $dbs['CODESET_CLASSES']['username'], $dbs['CODESET_CLASSES']['password']);
    $codesetClasses = new CodeSet\Classes($classesDBH);

    //Set up CodeSet Thestral
    require_once $DIR.'private_server/includes/CodeSetThestral/class.thestral.php';
    $thestralDBH = new PDO("mysql:host=".$dbs['CODESET_THESTRAL']['host'].";dbname=".$dbs['CODESET_THESTRAL']['dbname'], $dbs['CODESET_THESTRAL']['username'], $dbs['CODESET_THESTRAL']['password']);
    $codesetThestral = new CodeSet\Thestral($thestralDBH);

    //Set up CodeSet News
    require_once $DIR.'private_server/includes/CodeSetNews/class.news.php';
    $newsDBH = new PDO("mysql:host=".$dbs['CODESET_NEWS']['host'].";dbname=".$dbs['CODESET_NEWS']['dbname'], $dbs['CODESET_NEWS']['username'], $dbs['CODESET_NEWS']['password']);
    $codesetNews = new CodeSet\News($newsDBH);


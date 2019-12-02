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
            "dbname" => "db657046199",
            "username" => "dbo657046199",
            "password" => "CODESET_AUTH",
            "host" => "db657046199.db.1and1.com"
        ),
        "CODESET_CLASSES" => array(
            "dbname" => "db662374861",
            "username" => "dbo662374861",
            "password" => "CODESET_CLASSES",
            "host" => "db662374861.db.1and1.com"
        ),
        "CODESET_THESTRAL" => array(
            "dbname" => "db668273832",
            "username" => "dbo668273832",
            "password" => "CODESET_THESTRAL",
            "host" => "db668273832.db.1and1.com"
        ),
        "CODESET_NEWS" => array(
            "dbname" => "db658087996",
            "username" => "dbo658087996",
            "password" => "CODESET_NEWS",
            "host" => "db658087996.db.1and1.com"
        ),
        "CODESET_CONTACT" => array(
            "dbname" => "db686001594",
            "username" => "dbo686001594",
            "password" => "CODESET_CONTACT",
            "host" => "db686001594.db.1and1.com"
        )
    );

    //Set default timezone for all date/time functions
    date_default_timezone_set('Europe/London');

/*INCLUDES SETUP*/

    //Set up composer
    require_once $DIR.'vendor/autoload.php';

    //Set up CodeSet Mail
    require_once $DIR.'private_server/includes/CodeSetMail/class.mail.php';
    $codesetMail = new CodeSet\SendMail('587', 'server@codeset.co.uk', 'CodeSetServer1');
    
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


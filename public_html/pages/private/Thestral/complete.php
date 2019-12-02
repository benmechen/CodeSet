<?php
/**
 * @Author: Ben
 * @Date: 2016-11-19 22:25:05
 * @Project: codeset.co.uk
 * @File Name: complete.php
 * @Last Modified by: Ben
 * @Last Modified time: 2017-07-18 21:04:33
**/

if (empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] !== "on") {
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}

include "../../../../config.php";

$uid = $auth->getUIDFromHash($auth->getSessionHash())['uid'];

$user = $auth->getUser($uid);

$codesetLogs->log('landed_completed', $user['id']);

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>CodeSet - Course Complete!</title>
    <link rel="stylesheet" type="text/css" href="/public_html/assets/css/main.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
</head>
<body>
    <header class="signin-header">
        <h2 class="signin-header-title">Course Complete!</h1>
    </header>
    <main class="signin-main">
        Well done, you've completed all <?= $codesetThestral->getLastQuestionID() ?> questions - you're officially a Python wizard!
        <br>
        <br>
        <br>
        <img src="/public_html/assets/images/wizard.gif">
        <br>
        <br>
        <br>
        <br>
        <a href="../">Go back to the Dashboard</a>
    </main>
</body>
</html>
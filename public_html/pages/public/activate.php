<?php
/**
 * @Author: Ben
 * @Date: 2016-11-19 22:25:05
 * @Project: codeset.co.uk
 * @File Name: activate.php
 * @Last Modified by: Ben
 * @Last Modified time: 2017-03-19 13:33:54
**/

if (empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] !== "on") {
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}

include "../../../config.php";

$codesetLogs->log('landed_activate');

$activate = array('error' => TRUE, 'message' => "");

if(isset($_POST['activate-submit'])) {
    $activate = $auth->activate($_POST['activate-key']);
    if ($activate['error'] == FALSE) {
        $codesetLogs->log('user_account_activated', $_POST['activate-key']);   
    } else {
        if (strpos($activate['message'], 'system error')) {
            $codesetLogs->log('user_account_activate_error', $activate['message']);   
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>CodeSet - Activate Account</title>
    <link rel="stylesheet" type="text/css" href="/public_html/assets/css/main.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
</head>
<body>
    <header class="signin-header">
        <h2 class="signin-header-title">Activate your CodeSet account</h1>
        <a href="<?= $PUBLIC_DIR ?>">Or go back to register an account</a>
    </header>
    <main class="signin-main">
        <h4><?= $activate['message'] ?></h4>
        <? if($activate['error'] == True) { ?>
            <form action="" method="POST">
                <input type="text" name="activate-key" placeholder="Enter your activation key"><br>
                <input type="submit" name="activate-submit" value="Activate" style="width: 103.5%;">
            </form>
            <? } else { ?>
                <a href="signin.php">Log in to your account</a>
            <? } ?>
    </main>
</body>
</html>
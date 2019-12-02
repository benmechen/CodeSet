<?php
/**
 * @Author: Ben
 * @Date: 2016-11-20 10:04:55
 * @Project: codeset.co.uk
 * @File Name: requestreset.php
 * @Last Modified by: Ben
 * @Last Modified time: 2017-03-19 13:33:58
**/

if (empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] !== "on") {
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}

include "../../../config.php";

$codesetLogs->log('landed_resetrequest');

$reset = array('error' => TRUE, 'message' => "");

if(isset($_POST['reset-submit'])) {
    $reset = $auth->requestReset($_POST['reset-email']);
    $codesetLogs->log('user_reset_request', $_POST['reset-email']);     
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>CodeSet - Reset Password</title>
    <link rel="stylesheet" type="text/css" href="/public_html/assets/css/main.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
</head>
<body>
    <header class="signin-header">
        <h2 class="signin-header-title">Reset your CodeSet account password</h1>
        <a href="<?= $PUBLIC_DIR ?>">Or go back to log into your account</a>
    </header>
    <main class="signin-main">
        <h4><?= $reset['message'] ?></h4>
        <? if($reset['error'] == TRUE) { ?>
            <form action="" method="POST">
                <input type="email" name="reset-email" placeholder="Enter your email"><br>
                <input type="submit" name="reset-submit" value="Send Email" style="width: 103.5%;">
            </form>
        <? } ?>
    </main>
</body>
</html>
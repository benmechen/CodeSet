<?php
/**
 * @Author: Ben
 * @Date: 2016-11-20 10:04:55
 * @Project: codeset.co.uk
 * @File Name: reset.php
 * @Last Modified by: Ben
 * @Last Modified time: 2017-03-19 13:34:01
**/

if (empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] !== "on") {
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}

include "../../../config.php";

$codesetLogs->log('landed_resetpass');

$reset = array('error' => TRUE, 'message' => "");

if(isset($_POST['reset-submit'])) {
    $reset = $auth->resetPass($_POST['reset-key'], $_POST['reset-password'], $_POST['reset-repeatpassword']);
    if($reset['error'] == FALSE) {
        $signin = $auth->login($reset['user']['email'], $_POST['reset-password']);
        if ($signin['error'] == FALSE) {
            setcookie($auth->config->cookie_name, $signin['hash'], $signin['expire'], $auth->config->cookie_path, $auth->config->cookie_domain, $auth->config->cookie_secure, $auth->config->cookie_http);
            $codesetLogs->log('user_signed_in', $reset['user']['email']);
            header("Location: ../private/index.php");
        } else {
            if (strpos($signin['message'], 'system error')) {
                $codesetLogs->log('user_sign_in_error', $signin['message']);
            }
        }
    } else {
        if (strpos($reset['message'], 'system error')) {
            $codesetLogs->log('user_password_reset_error', $reset['message']);
        }
    }
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
        <form action="" method="POST" autocomplete="off">
            <input type="text" name="reset-key" placeholder="Enter your reset key" autocomplete="off"><br>
            <input type="password" name="reset-password" placeholder="Enter your new password"><br>
            <input type="password" name="reset-repeatpassword" placeholder="Enter your password again"><br>
            <input type="submit" name="reset-submit" value="Change Password" style="width: 103.5%;">
        </form>
    </main>
</body>
</html>
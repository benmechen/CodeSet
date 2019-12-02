<?php
/**
 * @Author: Ben
 * @Date: 2016-11-19 22:25:05
 * @Project: codeset.co.uk
 * @File Name: signin.php
 * @Last Modified by: Ben
 * @Last Modified time: 2017-03-19 13:34:05
**/

if (empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] !== "on") {
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}

include "../../../config.php";

$codesetLogs->log('landed_signin');

$signin = array('error' => TRUE, 'message' => "");

if(isset($_POST['signin-submit'])) {
    $signin = $auth->login($_POST['signin-email'], $_POST['signin-password']);
    if ($signin['error'] == FALSE) {
        setcookie($auth->config->cookie_name, $signin['hash'], $signin['expire'], $auth->config->cookie_path, $auth->config->cookie_domain, $auth->config->cookie_secure, $auth->config->cookie_http);
        $codesetLogs->log('user_signed_in', $_POST['signin-email']);
        header("Location: ../private/index.php");
    } else {
        if (strpos($signin['message'], "system error")) {
            $codesetLogs->log('user_signed_in_error', $signin['message']);
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>CodeSet - Sign In</title>
    <link rel="stylesheet" type="text/css" href="/public_html/assets/css/main.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
</head>
<body>
    <header class="signin-header">
        <h2 class="signin-header-title">Sign into your CodeSet account</h1>
        <a href="<?= $PUBLIC_DIR ?>">Or go back to register an account</a>
    </header>
    <main class="signin-main">
        <h4><?= $signin['message'] ?></h4>
        <form action="" method="POST">
            <input type="email" name="signin-email" placeholder="Enter your email"><br>
            <input type="password" name="signin-password" placeholder="Enter your password"><br>
            <input type="submit" name="signin-submit" value="Log In" style="width: 103.5%;">
        </form>
        <a href="requestreset.php"><h4>Forgot Password?</h4></a>
    </main>
</body>
</html>
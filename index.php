<?php
/**
 * @Author: Ben
 * @Date: 2016-11-16 14:40:38
 * @Project: CodeSet
 * @File Name: index.php
 * @Last Modified by:   Ben
 * @Last Modified time: 2017-08-30 19:43:01
*/

if (empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] !== "on") {
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}

include "config.php";

$codesetLogs->log('landed_home');

//Set registered to true so the form shows
$register = array('error' => TRUE, 'message' => "Create a teacher account");

//If sign up form is submitted, register account
if(isset($_POST['register-submit'])) {
    if(strlen($_POST['register-name'])) {
        $register = $auth->register($_POST['register-name'], $_POST['register-email'], $_POST['register-password'], $_POST['register-repassword'], NULL, NULL, TRUE);
        if ($register['error'] == FALSE) {
            $codesetLogs->log('user_signup_teacher', $_POST['register-email']);
        } else {
            $codesetLogs->log('user_signup_teacher_error', $register['message']);
        }
    } else {
        $register = array('error' => True);
        $register['message'] = "Please enter a name";
    }
    header("Location: ../?".http_build_query(array('register' => $register)));
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>CodeSet | The platform for teaching code in school</title>
    <link rel="stylesheet" type="text/css" href="public_html/assets/css/main.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <script type="text/javascript" src="public_html/assets/js/scroll.js"></script>
</head>
<body>
    <header>
        <a href="public_html/pages/public/signin.php" class="front-link">Sign In</a>
        <div class="expanding-line" data-ix="expanding-line-starts-hidden" style="width: 100%; transition: width 350ms ease-in-out; -webkit-transition: width 350ms ease-in-out;"></div>
    </header>
    <main>
        <div class="front-main">
            <div class="front-logo">
                <img src="public_html/assets/images/front-logo/text.png" id="front-logo-text">
                <img src="public_html/assets/images/front-logo/block.png" id="front-logo-block" class="flicker">
            </div>
            <div class="front-about">
                <p>Welcome to CodeSet! CodeSet is a platform to help teachers develop their students’ Python skills, keeping track of their progress along the way</p>
            </div>
            <div class="front-arrow">
                <a href="#login"></a>
            </div>
        </div>
        <div class="front-login" id="login">
            <h1>Get Started With CodeSet</h1>
            <div class="front-login-left">
                <p>
                    Teaching code in classes is hard.<br><br>
                    CodeSet allows teachers to help their students learn to code, by teaching Python in an interactive way, with instant code feedback, class leaderboards and milestones. Teachers can create and manage student's accounts, so that they can easily keep track of their progress and help them when they get stuck.
                </p>
            </div>
            <div class="front-login-right">
                <?php
                if (isset($_GET['register'])) {
                    $register = $_GET['register'];
                }

                if($register['error'] == 0) {
                ?>
                    <h4 style="max-width: 40vw;">Account created. Check your email to verify your account.</h4>

                <?php
                } else {
                ?>
                    <h4><?= $register['message'] ?></h4>
                    <form action="#login" method="POST">
                        <input type="text" name="register-name" placeholder="Enter your first name"><br>
                        <input type="email" name="register-email" placeholder="Enter your email"><br>
                        <input type="password" name="register-password" placeholder="Enter your password"><br>
                        <input type="password" name="register-repassword" placeholder="Renter your password"><br>
                        <input type="submit" name="register-submit" value="Register">
                    </form>
                <?php
                }
                ?>
            </div>
        </div>
    </main>
    <footer>
        <h5>&copy; CodeSet 2017</h5>
    </footer>
</body>
</html>

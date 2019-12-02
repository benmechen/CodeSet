<?php
/**
 * @Author: Ben
 * @Date: 2016-11-19 23:11:21
 * @Project: codeset.co.uk
 * @File Name: account.php
 * @Last Modified by:   Ben
 * @Last Modified time: 2017-08-30 20:59:40
**/

if (empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] !== "on") {
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}

if (isset($_GET['redirect'])) {
    header('Location: account.php');
}

include '../../../config.php';

$codesetLogs->log('landed_private_account');

if (!$auth->isLogged()) {
    $codesetLogs->log('user_wrong_permission');
    header("Location: ../public/signin.php");
}

$uid = $auth->getUIDFromHash($auth->getSessionHash())['uid'];

$user = $auth->getUser($uid);

if (isset($_POST['change-name-submit'])) {
    if (!empty($_POST['change-name-new'])) {
        if ($auth->changeName($uid, $_POST['change-name-new'])) {
            $codesetLogs->log('user_account_change_name');
            header('Location: account.php');
        } else {
            $codesetLogs->log('user_account_change_name_error');
            echo "<script>alert('Could not change name. Please try again'); window.location.reload();</script>";
        }
    } else{
        echo "<script>alert('Please enter a name'); window.location.reload();</script>";
    }
}

$passwordMessage = "";
if (isset($_POST['change-password-submit'])) {
    if (!empty($_POST['change-password-old']) && !empty($_POST['change-password-new'] && $_POST['change-password-newrepeat'])) {
        $password = $auth->changePassword($uid, $_POST['change-password-old'], $_POST['change-password-new'], $_POST['change-password-newrepeat']);
        if ($password['error'] == False) {
            $passwordMessage = "Password changed";
            $codesetLogs->log('user_password_reset');
            header('Location: account.php');
        } else {
            $codesetLogs->log('user_password_reset_error');
            echo "<script>alert('".$password['message']."'); window.location.reload();</script>";

        }
    } else {
        echo "<script>alert('Could not change password. Please try again'); window.location.reload();</script>";
    }
}

if (isset($_POST['delete-account-submit'])) {
    if ($user['profile'] != "user-default.png" && file_exists('../../assets/images/profiles/' . $user['profile'])) {
        unlink('../../assets/images/profiles/' . $user['profile']);
    }
    if ($codesetClasses->deleteUser($codesetClasses->getUsersClass($uid), $uid)) {
        if ($auth->deleteUser($uid)) {
            $codesetLogs->log('user_delete_account', $user['id']);
            header('Location: ../../../');
        } else {
            $codesetLogs->log('user_delete_account_error');
            echo "<script>alert('Could not delete account. Please try again'); window.location.reload();</script>";
        }
    }
    echo "<script>alert('Could not delete account. Please try again'); window.location.reload();</script>";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CodeSet - Account</title>
    <link rel="stylesheet" type="text/css" href="/public_html/assets/css/main.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <script src="https://cdn.rawgit.com/asvd/dragscroll/master/dragscroll.js"></script>
</head>
<body>
    <header class="dashboard-header">
        <nav>
            <a href="../private/"><img src="/public_html/assets/images/logo-black.png"></a>
            <ul>
                <a href="../private/"><li>Dashboard</li></a>
                <a href="Griffin/"><li>Python Editor</li></a>
                <? if ($user['permission'] == 1){?><a href="classes/"><li>Manage Classes</li></a><? } ?>
                <a href="account.php"><img src="/public_html/assets/images/profiles/<?= $user['profile'] ?>" class="profile"></a>
                <a href="account.php" id="dashboard-header-name" class="active"><li><?= $user['name'] ?></li></a>
                <a href="signout.php" <? if ($user['permission'] == 0){?> style="margin-right: -2.5vw;" <?} ?>>Sign Out</a>
            </ul>
        </nav>
        <div class="dashboard-progress" style="cursor: auto;">
            <br>
            <h4 style="position: absolute;"><?= $user['name'] ?></h4>
            <div class="account-profile-container">
                <img src="/public_html/assets/images/profiles/<?= $user['profile'] ?>" class="account-profile">
                <form action="/private_server/includes/CodeSetProfileUpload/handler.php" method="post" enctype="multipart/form-data" id="account-profile-upload">
                    <!-- <input type="file" id="account-profile-file" name="account-profile-file" onchange="this.form.submit()"> -->
                    <input type="file" id="account-profile-file" name="account-profile-file" onchange="this.form.submit()">
                    <input type="button" value="Change" id="account-profile-change" name="account-profile-change-submit">
                </form>
            </div>
            <br>
            <? if ($user['permission'] == 0) {
                echo "<b>Student</b><br><br>";
            } elseif ($user['permission'] == 1) {
                echo "<b>Teacher</b><br><br>";
            } ?>
            Account created on <?= date('l jS \of F Y', strtotime($user['dt'])) ?>
        </div>
    </header>
    <div class="popup-overlay" id="change-name-overlay">
        <div class="popup" id="change-name-popup">
                <span class="popup-close" id="change-name-close" style="cursor: pointer;">x</span>
                <form method="post" action="">
                    <input type="text" name="change-name-new" placeholder="Name" autofocus>
                    <br>
                    <input type="submit" name="change-name-submit">
                </form>
        </div>
    </div>
    <div class="popup-overlay" id="change-password-overlay">
        <div class="popup" id="change-password-popup">
                <span class="popup-close" id="change-password-close" style="cursor: pointer;">x</span>
                <form method="post" action="" autocomplete="off">
                    <input type="password" name="change-password-old" placeholder="Old Password" autofocus>
                    <input type="password" name="change-password-new" placeholder="New Password">
                    <input type="password" name="change-password-newrepeat" placeholder="Repeat New Password">
                    <br>
                    <input type="submit" name="change-password-submit">
                </form>
        </div>
    </div>
    <div class="popup-overlay" id="delete-account-overlay">
        <div class="popup" id="delete-account-popup">
                <span class="popup-close" id="delete-account-close" style="cursor: pointer;">x</span>
                <form method="post" action="" autocomplete="off">
                    <h4 style="text-align: center;">Are you sure you want to delete your account?</h4>
                    <br>
                    <input type="submit" name="delete-account-submit" value="Delete Account">
                </form>
        </div>
    </div>
    <main class="dashboard-main account-main">
        <h1>Your Account</h1>
        <br>
        <div class="account-info">
            <h3>Basic Info</h3>
            <br>
            <ul>
                <li><b>Name:</b> <?= $user['name'] ?> <span class="account-info-change" id="change-name">CHANGE</span></li>
                <li><b>Email:</b> <?= $user['email'] ?></li>
                <span style="color: red;"><?= $passwordMessage ?></span>
                <li><a id="change-password"><span class="account-info-change">CHANGE PASSWORD</span></li></a></li>
                <li><a id="delete-account"><span class="account-info-change">DELETE ACCOUNT</span></li></a></li>
            </ul>
            <br>
            <br>
            <!-- <h3>Mail Info</h3> -->
        </div>
    </main>
    <footer>
        <h5>&copy; CodeSet 2016</h5>
    </footer>
</body>
<script>
    var form = document.getElementById('account-profile-upload');
    var fileSelect = document.getElementById('account-profile-file');
    var uploadButton = document.getElementById('account-profile-change');

    $(form).bind('submit', function (event) {
        event.preventDefault();
    });

    $('#account-profile-change').click(function(){
        $('#account-profile-file').click();
    });

    // form.submit();
    // $(form).submit(function(event) {
    //     console.log("meow2");
    //     event.preventDefault();
    //     $("body").css({"cursor": "progress"});

    //     var files = fileSelect.files;

    //     var formData = new FormData();

    //     for (var i = 0; i < files.length; i++) {
    //         var file = files[i];

    //         // Check the file type.
    //         if (!file.type.match('image.*')) {
    //             continue;
    //         }

    //         // Add the file to the request.
    //         formData.append('account-profile-file', file, file.name);
    //     }

    //     // Create new XMLHttpRequest object
    //     var xhr = new XMLHttpRequest();

    //     xhr.open('POST', '/private_server/includes/CodeSetProfileUpload/handler.php', true);

    //     xhr.onload = function () {
    //         if (xhr.status === 200) {
    //             // File(s) uploaded.
    //             uploadButton.innerHTML = 'Upload';
    //             console.log(xhr.responseText);
    //         } else {
    //             alert('An error occurred!');
    //         }
    //     };

    //     // Send the Data.
    //     xhr.send(formData);
    // });

    // Show change name pop up
    $("#change-name").click(function() {
        $("#change-name-overlay").fadeIn();
    });
    // Close change name pop up
    $("#change-name-close").click(function() {
        $("#change-name-overlay").fadeOut();
    });
    $(document).keyup(function(e) {
        if (e.keyCode == 27) {
            $("#change-name-overlay").fadeOut();
        }
    });
    $(document).mouseup(function (e) {
        var popup = $("#change-name-popup");
        if (!$('#change-name').is(e.target) && !popup.is(e.target) && popup.has(e.target).length == 0) {
            $("#change-name-overlay").fadeOut();
        }
    });

    // Show change password pop up
    $("#change-password").click(function() {
        $("#change-password-overlay").fadeIn();
    });
    // Close change password pop up
    $("#change-password-close").click(function() {
        $("#change-password-overlay").fadeOut();
    });
    $(document).keyup(function(e) {
        if (e.keyCode == 27) {
            $("#change-password-overlay").fadeOut();
        }
    });
    $(document).mouseup(function (e) {
        var popup = $("#change-password-popup");
        if (!$('#change-password').is(e.target) && !popup.is(e.target) && popup.has(e.target).length == 0) {
            $("#change-password-overlay").fadeOut();
        }
    });

    // Show delete account pop up
    $("#delete-account").click(function() {
        $("#delete-account-overlay").fadeIn();
    });
    // Close delete account pop up
    $("#delete-account-close").click(function() {
        $("#delete-account-overlay").fadeOut();
    });
    $(document).keyup(function(e) {
        if (e.keyCode == 27) {
            $("#delete-account-overlay").fadeOut();
        }
    });
    $(document).mouseup(function (e) {
        var popup = $("#delete-account-popup");
        if (!$('#delete-account').is(e.target) && !popup.is(e.target) && popup.has(e.target).length == 0) {
            $("#delete-account-overlay").fadeOut();
        }
    });
</script>
</html>

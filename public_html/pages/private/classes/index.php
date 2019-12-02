<?php
/**
 * @Author: Ben
 * @Date: 2016-11-26 13:37:06
 * @Project: codeset.co.uk
 * @File Name: index.php
 * @Last Modified by:   Ben
 * @Last Modified time: 2017-08-25 19:05:31
**/

if (empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] !== "on") {
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}

include '../../../../config.php';

$codesetLogs->log('landed_private_classes');

if (!$auth->isLogged()) {
    $codesetLogs->log('user_wrong_permission');
    header("Location: ../../public/signin.php");
}

$uid = $auth->getUIDFromHash($auth->getSessionHash())['uid'];

$user = $auth->getUser($uid);

if ($user['permission'] < 1) {
    $codesetLogs->log('user_wrong_permission');
    header('Location: ../../private/');
}

// Create new class
if (isset($_POST['create-class-submit'])) {
    if (strlen($_POST['create-class-name']) > 0) {
        if ($codesetClasses->create($_POST['create-class-name'], $uid)) {
            // Class created
            $codesetLogs->log('class_created', $_POST['create-class-name']);
        } else {
            $codesetLogs->log('class_created_error');
            echo "<script type='text/javascript'>alert('Could not create class. Please try again');</script>";
        }
    } else {
        echo "<script type='text/javascript'>alert('Please enter a name for the class');</script>";
    }
    header("Location: ../classes/");
}

// Delete a class
if (isset($_POST['delete-class-submit'])) {
    $delete = $codesetClasses->delete($_POST['delete-class-id'], $uid);
    if ($delete) {
        // Class deleted
        foreach ($codesetNews->getClassDeadlines($_POST['delete-class-id']) as $deadline) {
            $codesetNews->deleteClassDeadline($deadline['id']);
        }
        foreach ($codesetNews->getClassNews($_POST['delete-class-id']) as $news) {
            $codesetNews->deleteClassNews($news['id']);
        }
        $codesetLogs->log('class_deleted', $_POST['delete-class-id']);
    } else {
        $codesetLogs->log('class_deleted_error');
        echo "<script type='text/javascript'>alert('$delete');</script>";
    }
    header("Location: ../classes/");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CodeSet - Manage Classes</title>
    <link rel="stylesheet" type="text/css" href="/public_html/assets/css/main.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
</head>

<body>
    <header class="dashboard-header">
        <nav>
            <a href="../"><img src="/public_html/assets/images/logo-black.png"></a>
            <ul>
                <a href="../"><li>Dashboard</li></a>
                <a href="../Griffin/"><li>Python Editor</li></a>
                <? if ($user['permission'] == 1){?><a href="../classes/"><li class="active">Manage Classes</li></a><? } ?>
                <a href="account.php"><img src="/public_html/assets/images/profiles/<?= $user['profile'] ?>" class="profile"></a>
                <a href="../account.php" id="dashboard-header-name"><li><?= $user['name'] ?></li></a>
                <a href="../signout.php">Sign Out</a>
            </ul>
        </nav>
        <div class="dashboard-progress classes-progress" style="cursor: auto;">
            <?
                $classesProgress = $codesetClasses->getClassesLeaderboard($uid, $authDBH, $codesetThestral->getLastQuestionID());

                foreach ($classesProgress as $classProgress) { ?>
                    <div class="classes-progress-class">
                        <div class="classes-progress-bar" style="height: calc(35vh - <?= 100-$classProgress[0] ?>%);"></div>
                        <span class="classes-progress-name"><?= $classProgress[1] ?></span>
                    </div>
                <?}
            ?>
        </div>
    </header>
    <main class="dashboard-main">
        <h1>Your Classes</h1>
        <br>
        <div class="dashboard-classes">
            <div class="popup-overlay" id="create-class">
                <div class="popup" id="create-class-popup">
                    <span class="popup-close" id="create-class-close" style="cursor: pointer;">x</span>
                    <form method="post" action="">
                        <input type="text" name="create-class-name" placeholder="Class Name" autofocus>
                        <br>
                        <input type="submit" name="create-class-submit">
                    </form>
                </div>
            </div>
            <div id="create-class-button" class="dashboard-class-tile" style="background: url('/public_html/assets/images/plus.png') no-repeat center #0c4e73; background-size: 50%; cursor: pointer;">
                <div class="class-tile-content">
                    <div class="class-tile-content-table-hover">
                        <h4 style="margin-top: 80%;">Create New Class</h4>
                    </div>
                </div>
            </div>
            <?
                $classes = $codesetClasses->getClasses($uid);
                foreach ($classes as $class) {
            ?>
                    <div class="dashboard-class-tile">
                        <div class="class-tile-content">
                            <a href="class.php?class=<?= $class['id'] ?>"><div class="class-tile-content-table">
                                <h4><?= $class['name'] ?></h4>
                            </div></a>
                            <div class="class-tile-content-table-hover">
                                <form style="width: 45%; margin: 0 auto; margin-top: -30%;" method="post" action="">
                                    <input type="hidden" name="delete-class-id" value="<?= $class['id'] ?>">
                                    <input type="submit" name="delete-class-submit" value="Delete" style="height: 2em; font-size: 1.5em;">
                                </form>
                            </div>
                        </div>
                    </div>
            <?
                }
            ?>
        </div>
    </main>
    <!-- <footer>
        <h5>&copy; CodeSet 2016</h5>
    </footer> -->
</body>
<script type="text/javascript">
    // Show new class pop up
    $("#create-class-button").click(function() {
        $("#create-class").fadeIn();
    });
    // Close new class pop up
    $("#create-class-close").click(function() {
        $("#create-class").fadeOut();
    });
    $(document).keyup(function(e) {
        if (e.keyCode == 27) {
            $("#create-class").fadeOut();
        }
    });
    $(document).mouseup(function (e) {
        var popup = $("#create-class-popup");
        if (!$('#create-class-button').is(e.target) && !popup.is(e.target) && popup.has(e.target).length == 0) {
            $("#create-class").fadeOut();
        }
    });
</script>
</html>

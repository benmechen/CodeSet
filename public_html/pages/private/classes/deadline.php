<?php
/**
 * @Author: Ben
 * @Date: 2016-11-26 13:37:06
 * @Project: codeset.co.uk
 * @File Name: deadline.php
 * @Last Modified by:   Ben
 * @Last Modified time: 2017-08-21 14:24:59
**/

if (empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] !== "on") {
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}

include '../../../../config.php';

$codesetLogs->log('landed_private_class', $_GET['class']);

if (!$auth->isLogged()) {
    $codesetLogs->log('user_wrong_permission');
    header("Location: ../../public/signin.php");
}

$uid = $auth->getUIDFromHash($auth->getSessionHash())['uid'];

$user = $auth->getUser($uid);

$class = $codesetClasses->getClass($codesetNews->getClassIdFromDeadlineId($_GET['id']));

$deadline = $codesetNews->getDeadlineInfo($_GET['id']);

if ($class['author_id'] != $uid) {
    $codesetLogs->log('user_wrong_permission');
    header("Location: ../classes/");
}

// Delete a deadline
if (isset($_POST['delete-deadline-submit'])) {
    if ($codesetNews->deleteClassDeadline($_POST['delete-deadline-id'])) {
        header('Location: class.php?class='.$class["id"]);
    } else {
        echo "<script type='text/javascript'>alert('Could not delete deadline. Please try again'); window.location.reload();</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CodeSet - <?= $class['name'] ?> Deadline</title>
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
                $studentsProgress = $codesetClasses->getClassLeaderboard($class['id'], $authDBH, $deadline['point']);
                foreach ($studentsProgress as $studentProgress) {
                    if ($studentProgress[0] > 100) {
                        $studentProgress[0] = 100;
                    }
                ?>
                    <div class="classes-progress-class">
                        <div class="classes-progress-bar" style="height: calc(35vh - <?= 100-$studentProgress[0] ?>%);"></div>
                        <span class="classes-progress-name"><?= $studentProgress[1] ?></span>
                    </div>
                <?}
            ?>
        </div>
    </header>
    <main class="dashboard-main">
        <h1><a href="class.php?class=<?= $class['id'] ?>" style="color: #0f5d8a">&larr;<?= $class['name'] ?></a></h1>
        <br>
        <div class="dashboard-classes">
            <div class="dashboard-sidebar" style="border-left: 2.5px solid #0a3f5e; padding: 1.25%;">   <? $date = new DateTime($deadline['expiration']); ?>
                <h3>Deadline: Reach question <?= $deadline['point'] ?> by <?= $date->format("d/m/Y") ?></h3>
                <br>
                <br>
                <?  $countComplete = 0;
                    $students = $codesetClasses->getStudents($class['id']);
                    foreach ($students as $student) {
                        if ($auth->getUser($student['user_id'])['progress'] >= $deadline['point']) {
                            $countComplete++;
                        }
                    }
                ?>
                <? if ($countComplete == 1) { ?>
                    <?= $countComplete ?> student out of <?= sizeof($students) ?> have reached the deadline.
                <? } else { ?>
                    <?= $countComplete ?> students out of <?= sizeof($students) ?> have reached the deadline.
                <? } ?>
                <br>
                <br>
                <form method="post" class="class-edit-button" style="width: 90%;">
                    <input type="hidden" name="delete-deadline-id" value="<?= $deadline['id'] ?>">
                    <input type="submit" name="delete-deadline-submit" value="Delete">
                </form>
            </div>
            <div>
                <ul class="class-students">
                <?
                    foreach ($students as $student) {
                ?>
                        <li style="list-style-type: disc;">
                            <span style="font-weight: 500;">Name:</span> <?= $auth->getUser($student['user_id'])['name'] ?>
                            <span style="margin-left: 1em; font-weight: 500;">Deadline Progress:</span> <?= $auth->getUser($student['user_id'])['progress'] ?> / <?= $deadline['point'] ?>
                            <? if ($auth->getUser($student['user_id'])['progress'] >= $deadline['point']) { ?>
                                <span style="font-size: 0.8em; float: right; margin-top: 0.4%;">COMPLETE</span>
                            <? } ?>
                            <!-- <button id="delete-student-submit">Edit</button> -->
                        </li>
                <?
                    }
                ?>
                </ul>
            </div>
        </div>
    </main>
    <!-- <footer>
        <h5>&copy; CodeSet 2016</h5>
    </footer> -->
</body>
<script type="text/javascript">
    // Show and hide rename class form{}
    var clicked = false;
    $("#rename-class-button").click(function() {
        if(clicked) {
            $("#rename-class-button").css("background-color", "white");
            $("#rename-class-button").css("color", "#0a3f5e");
            $("#rename-class-form").slideUp("fast");
            clicked = false;
        } else {
            $("#rename-class-button").css("background-color", "#0a3f5e");
            $("#rename-class-button").css("color", "white");
            $("#rename-class-form").slideDown("fast");
            clicked = true;
        }
    });
</script>
</html>

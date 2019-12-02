<?php
/**
 * @Author: Ben
 * @Date: 2016-11-26 13:37:06
 * @Project: codeset.co.uk
 * @File Name: class.php
 * @Last Modified by:   Ben
 * @Last Modified time: 2017-09-07 19:14:59
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

$class = $codesetClasses->getClass($_GET['class']);

if ($class['author_id'] != $uid) {
    $codesetLogs->log('user_wrong_permission');
    header("Location: ../classes/");
}

// Delete a class
if (isset($_POST['delete-class-submit'])) {
    $delete = $codesetClasses->delete($_POST['delete-class-id'], $uid);
    if ($delete) {
        foreach ($codesetNews->getClassDeadlines($_POST['delete-class-id']) as $deadline) {
            $codesetNews->deleteClassDeadline($deadline['id']);
        }
        foreach ($codesetNews->getClassNews($_POST['delete-class-id']) as $news) {
            $codesetNews->deleteClassNews($news['id']);
        }
        $codesetLogs->log('class_deleted', $_POST['delete-class-id']);
        header("Location: ../classes/");
    } else {
        $codesetLogs->log('class_deleted_error');
        echo "<script type='text/javascript'>alert('$delete'); window.location.reload();</script>";
    }
}

// Rename a class
if (isset($_POST['rename-class-submit'])) {
    if (strlen($_POST['rename-class-newname']) > 0) {
        $rename = $codesetClasses->rename($_POST['rename-class-newname'], $_POST['rename-class-id'], $uid);
        if ($rename) {
            $codesetLogs->log('class_renamed', $_POST['rename-class-id']);
            header('Location: '.$_SERVER['REQUEST_URI']);
        } else {
            $codesetLogs->log('class_renamed_error');
            echo "<script type='text/javascript'>alert('$rename'); window.location.reload();</script>";
        }
    } else {
        echo "<script type='text/javascript'>alert('Please enter a name for the class'); window.location.reload();</script>";
    }
}

// Add a user
if (isset($_POST['new-student-submit'])) {
    if (strlen($_POST['new-student-name']) > 0 && strlen($_POST['new-student-email']) > 0) {
        $newstudent = $auth->registerStudent($_POST['new-student-name'], $_POST['new-student-email']);
        if ($newstudent['error'] == false) {
            $studentUID = $auth->getUID($_POST['new-student-email']);
            if ($codesetClasses->addUser($class['id'], $studentUID, 0)) {
                header('Location: class.php?class='.$_GET['class']);
            } else {
                $password = $auth->getUser($studentUID);
                $auth->deleteUser($studentUID, $password);
                echo "<script type='text/javascript'>alert('Could not add user to class. Please try again' window.location.reload(););</script>";
            }
        } else {
            $message = $newstudent['message'];
            echo "<script type='text/javascript'>alert('$message'); window.location.reload();</script>";
        }
    } else {
        echo "<script type='text/javascript'>alert('Please enter a name and email for the student'); window.location.reload();</script>";
    }
}

// Delete a student
if (isset($_POST['delete-student-submit'])) {
    $delete = $auth->deleteStudent($_POST['delete-student-id']);
    if ($delete) {
        $codesetClasses->deleteUser($class['id'], $_POST['delete-student-id']);
        header('Location: class.php?class='.$_GET['class']);
    } else {
        echo "<script type='text/javascript'>alert('$delete'); window.location.reload();</script>";
    }
}

// Set a deadline
if (isset($_POST['new-deadline-submit'])) {
    if (!empty($_POST['new-deadline-question']) && !empty($_POST['new-deadline-date'])) {
        $deadline = $codesetNews->setClassDeadline($class['id'], $_POST['new-deadline-question'], $_POST['new-deadline-date'], $codesetThestral->getLastQuestionID());
        if ($deadline['error'] == false) {
            header('Location: class.php?class='.$_GET['class']);
        } else {
            $message = $deadline['message'];
            echo "<script type='text/javascript'>alert('$message'); window.location.reload();</script>";
        }
    } else {
        echo "<script type='text/javascript'>alert('Question to reach and date cannot be empty'); window.location.reload();</script>";
    }
}

// Delete a deadline
if (isset($_POST['delete-deadline-submit'])) {
    if ($codesetNews->deleteClassDeadline($_POST['delete-deadline-id'])) {
        header('Location: class.php?class='.$_GET['class']);
    } else {
        echo "<script type='text/javascript'>alert('Could not delete deadline. Please try again'); window.location.reload();</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CodeSet - <?= $class['name'] ?></title>
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
        <div class="dashboard-progress classes-progress dragscroll" style="cursor: auto;">
            <?
                $studentsProgress = $codesetClasses->getClassLeaderboard($class['id'], $authDBH, $codesetThestral->getLastQuestionID());
                foreach ($studentsProgress as $studentProgress) {
                    // $studentProgress[1] = preg_replace("/\r|\n/", "<br>", $studentProgress[1]);
                    ?>
                    <div class="classes-progress-class" style="word-wrap: break-word;">
                        <div class="classes-progress-bar" style="height: calc(35vh - <?= 100-$studentProgress[0] ?>%);"></div>
                        <!-- <span class="classes-progress-name student-progress-name" style="width: 5vw; word-wrap: break-word;"><?= str_replace(' ', '<br>', $studentProgress[1]) ?> (<?= $studentProgress[3] ?>)</span> -->
                        <span class="classes-progress-name" style="width: 5vw; word-wrap: break-word;"><?= $studentProgress[1] ?> (<?= $studentProgress[3] ?>)</span>
                    </div>
                <?}
            ?>
        </div>
    </header>
    <div class="popup-overlay" id="email-students-sent-overlay">
        <div class="popup" id="email-students-sent-popup">
                <span class="popup-close" id="email-students-sent-close" style="cursor: pointer;">x</span>
                <h4 style="text-align: center;">An email containing their CodeSet username and password has been sent to all the students of this class</h4>
        </div>
    </div>
    <div class="popup-overlay" id="email-students-overlay">
        <div class="popup" id="email-students-popup">
                <span class="popup-close" id="email-students-close" style="cursor: pointer;">x</span>
                <h4 style="text-align: center;">Click below to send an email to the students in the class their username and password:</h4>
                <div style="width: 50%; margin: 0 auto;">
                    <input type="submit" name="email-students-send" id="email-students-send" value="Send">
                </div>
        </div>
    </div>
    <main class="dashboard-main">
        <h1><?= $class['name'] ?></h1>
        <br>
        <div class="dashboard-classes">
            <div class="dashboard-sidebar" style="border-left: 2.5px solid #0a3f5e; padding: 1.25%;">
                <h3><?= $class['name'] ?></h3>
                <br>
                <div class="class-sidebar">
                    Created <?= $class['date'] ?>
                    <br><br>
                    <div class="sidebar-class-edit">
                        <form method="post" class="class-edit-button">
                            <input type="hidden" name="delete-class-id" value="<?= $class['id'] ?>">
                            <input type="submit" name="delete-class-submit" value="Delete">
                        </form>
                        <input type="button" id="rename-class-button" value="Rename" class="class-edit-button">
                        <br><br>
                        <form method="post" id="rename-class-form">
                            <input type="hidden" name="rename-class-id" value="<?= $class['id'] ?>">
                            <input type="text" name="rename-class-newname" placeholder="New Name" style="color: #0a3f5e; width: 95%;">
                            <input type="submit" name="rename-class-submit" value="Rename" style="width: 108.5%;">
                        </form>
                    </div>
                    <br>
                    <br>
                    <br>
                    <a style="color: #0f5d8a; cursor: pointer;" id="email-students">Email the class's students their username and password</a>
                    <br>
                    <br>
                    <div class="sidebar-class-news">
                        <h4>News</h4>
                        <br>
                        <br>
                        <h5>Deadlines:</h5>
                        <br>
                        <!-- Current Deadlines displayed as tiles -->
                        Create New Deadline:
                        <form method="post">
                            <ul class="new-deadline-list">
                                <li>Question number to reach (out of <?= $codesetThestral->getLastQuestionID() ?>):<input type="number" name="new-deadline-question" style="margin-left: 1%; width: 10%; height: 100%; font-size: 1em; display: inline; color: #0a3f5e;" min="1" max="<?= $codesetThestral->getLastQuestionID() ?>"></li><br>
                                <li>Completed by midnight on:<input type="date" name="new-deadline-date" placeholder="Date: yyyy-mm-dd" style="width: 40%; display: inline; color: #0a3f5e;" min="<?= date('Y-m-d') ?>"></li>
                                <input type="submit" name="new-deadline-submit">
                            </ul>
                        </form>
                        <br>
                        <br>
                        All Class Deadlines:
                        <br>
                        <?
                            $deadlines = $codesetNews->getClassDeadlines($class['id']);
                            if ($deadlines != False) {
                                foreach ($deadlines as $deadline) {
                                    $date = new DateTime($deadline['expiration']);
                                    if ($date < new DateTime()) {
                                        $expired = "deadline-card-expired";
                                    } else {
                                        $expired = "";
                                    }
                                    ?>
                                    <div class="deadline-card <?= $expired ?>">
                                        <a class="deadline-card-text" href="deadline.php?id=<?= $deadline['id'] ?>"><h5>Reach question <?= $deadline['point'] ?> by <?= $date->format("d/m/Y") ?></h5></a>
                                        <form action="" method="post">
                                            <input type="hidden" name="delete-deadline-id" value="<?= $deadline['id'] ?>">
                                            <button id="delete-deadline-submit" name="delete-deadline-submit">Delete</button>
                                        </form>
                                    </div>
                            <?  }
                            } else {
                                echo "<span style='margin-left: 1em; font-size: 75%;'>Nothing here</span>";
                            } ?>
                    </div>
                </div>
            </div>
            <div>
                <ul class="class-students">
                <?
                    $students = $codesetClasses->getStudents($class['id']);
                    foreach ($students as $student) {
                ?>
                        <li style="list-style-type: disc;">
                            <span style="font-weight: 500;">Name:</span> <?= $auth->getUser($student['user_id'])['name'] ?> <span style="font-weight: 500;">Email:</span> <?= $auth->getUser($student['user_id'])['email'] ?> <span style="font-weight: 500;">Generated Password:</span> <?= $auth->getUser($student['user_id'])['generated_password'] ?>
                            <span style="font-weight: 500;">Progress:</span> <?= $auth->getUser($student['user_id'])['progress'] ?>
                            <span class="space"> </span>
                            <span class="space"> </span>
                            <form action="" method="post">
                                <input type="hidden" name="delete-student-id" value="<?= $student['user_id'] ?>">
                                <button id="delete-student-submit" name="delete-student-submit">Delete</button>
                            </form>
                        </li>
                <?
                    }
                ?>  <br>
                    <li id="new-student-li">
                        <form method="post" id="new-student-form">
                            <input type="text" name="new-student-name" placeholder="Student Name" autofocus>
                            <input type="email" name="new-student-email" placeholder="Student Email">
                            <input type="submit" name="new-student-submit" value="Create Student Account">
                        </form>
                    </li>
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

    // Show email students pop up
    $("#email-students").click(function() {
        $("#email-students-overlay").fadeIn();
    });

    $("#email-students-send").click(function() {
        $("#email-students-overlay").fadeOut();
        var classID = "<?= $class['id'] ?>";
        $.ajax({
              url: 'email_students.php',
              type: 'post',
              data: {'classID': classID},
              success: function(data) {
                $("#email-students-sent-overlay").fadeIn();
              },
              error: function(data) {
                alert("Could not email students. Please try again.");
              }
            });
    });
    // Close email students pop up
    $("#email-students-close").click(function() {
        $("#email-students-overlay").fadeOut();
    });
    $(document).keyup(function(e) {
        if (e.keyCode == 27) {
            $("#email-students-overlay").fadeOut();
        }
    });
    $(document).mouseup(function (e) {
        var popup = $("#email-students-popup");
        if (!$('#email-students').is(e.target) && !popup.is(e.target) && popup.has(e.target).length == 0) {
            $("#email-students-overlay").fadeOut();
        }
    });

    // Close email students sent pop up
    $("#email-students-sent-close").click(function() {
        $("#email-students-sent-overlay").fadeOut();
    });
    $(document).keyup(function(e) {
        if (e.keyCode == 27) {
            $("#email-students-sent-overlay").fadeOut();
        }
    });
    $(document).mouseup(function (e) {
        var popup = $("#email-students-sent-popup");
        if (!$('#email-students-sent').is(e.target) && !popup.is(e.target) && popup.has(e.target).length == 0) {
            $("#email-students-sent-overlay").fadeOut();
        }
    });
</script>
</html>

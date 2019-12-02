<?php
/**
 * @Author: Ben
 * @Date: 2016-11-19 23:11:21
 * @Project: codeset.co.uk
 * @File Name: index.php
 * @Last Modified by:   Ben
 * @Last Modified time: 2017-08-21 17:02:42
**/

if (empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] !== "on") {
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}

include '../../../config.php';

$codesetLogs->log('landed_private');

if (!$auth->isLogged()) {
    $codesetLogs->log('user_wrong_permission');
    header("Location: ../public/signin.php");
}

$uid = $auth->getUIDFromHash($auth->getSessionHash())['uid'];

$user = $auth->getUser($uid);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CodeSet - <?= $user['name'] ?></title>
    <link rel="stylesheet" type="text/css" href="/public_html/assets/css/main.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <script src="https://cdn.rawgit.com/asvd/dragscroll/master/dragscroll.js"></script>
    <script src="//cdn.jsdelivr.net/jquery.scrollto/2.1.2/jquery.scrollTo.min.js"></script>
</head>
<body>
    <header class="dashboard-header">
        <nav>
            <a href=""><img src="/public_html/assets/images/logo-black.png"></a>
            <ul>
                <a href="../private/"><li class="active">Dashboard</li></a>
                <a href="Griffin/"><li>Python Editor</li></a>
                <? if ($user['permission'] == 1){?><a href="classes/"><li>Manage Classes</li></a><? } ?>
                <a href="account.php"><img src="/public_html/assets/images/profiles/<?= $user['profile'] ?>" class="profile"></a>
                <a href="account.php" id="dashboard-header-name"><li><?= $user['name'] ?></li></a>
                <a href="signout.php" <? if ($user['permission'] == 0){?> style="margin-right: -2.5vw;" <?} ?>>Sign Out<span class="space"> </span></a>
            </ul>
        </nav>
        <div <? if ($user['progress'] == 0) { ?> class="dashboard-progress" style="cursor: default;"<? } else { ?> class="dashboard-progress dragscroll" id="dashboard-progress"> <? } ?>
            <br>
            <h4>Python Course</h4>
            <? if ($user['progress'] == 0) { ?>
                <button class="dashboard-progress-start button" onclick="location.href='Thestral/?id=1';">Start</button>
            <? } else {
                for ($i = 1; $i <= $codesetThestral->getLastQuestionID(); $i++) {
                    if ($i > $user['progress']) { ?>
                        <a href="Thestral/?id=<?= $i ?>"><span class="dashboard-progress-circle dashboard-progress-circle-uncomplete"><?= $i ?></span></a>
                        <? if ($i < $codesetThestral->getLastQuestionID()) { ?>
                            <span class="dashboard-progress-line"></span>
                        <? } ?>
                    <? } elseif ($i == $user['progress']-3) { ?>
                        <a href="Thestral/?id=<?= $i ?>"><span class="dashboard-progress-circle" id="dashboard-progress-circle-current"><?= $i ?></span></a>
                        <? if ($i < $codesetThestral->getLastQuestionID()) { ?>
                            <span class="dashboard-progress-line"></span>
                        <? } ?>
                    <? } else { ?>
                        <a href="Thestral/?id=<?= $i ?>"><span class="dashboard-progress-circle"><?= $i ?></span></a>
                        <? if ($i < $codesetThestral->getLastQuestionID()) { ?>
                            <span class="dashboard-progress-line"></span>
                        <? }
                    }
                }
            } ?>
        </div>
    </header>
    <main class="dashboard-main">
        <h1>Hello <?= $user['name'] ?></h1>
        <br>
        <div class="dashboard-news">
            <h3>News</h3>
            <br>
            <!-- Active Deadlines -->
            <?
                if ($user['permission'] != 1) {
                    $activeDeadlines = $codesetNews->getActiveDeadlines($codesetClasses->getUsersClass($uid));
                    foreach ($activeDeadlines as $activeDeadline) {
                        if ($user['progress'] >= $activeDeadline['point']) {
                            $expired = 'dashboard-news-card-expired';
                        } else {
                            $expired = "";
                        }
                        $date = new DateTime($activeDeadline['expiration']);
                        $dateSet = new DateTime($activeDeadline['date']);
                        ?>
                        <div class="dashboard-news-card dashboard-news-card-active <?= $expired ?>">
                            <h4 class="dashboard-news-card-title">Deadline: Reach question <?= $activeDeadline['point'] ?> by <?= $date->format('d/m/Y') ?></h4>
                            <? if ($user['progress'] >= $activeDeadline['point']) { ?>
                                <p>You have reached this deadline</p>
                            <? } else { ?>
                                <p>You are on question <?= $user['progress']?> out of <?= $activeDeadline['point']?></p>
                            <? } ?>
                            <i style="font-size: 0.75em;"><?= $dateSet->format('d/m/Y') ?></i>
                        </div>
                    <? }
                } else {
                    foreach ($codesetClasses->getClasses($uid) as $classID) {
                        $classID = $classID["id"];
                        $activeDeadlines = $codesetNews->getActiveDeadlines($classID);
                        foreach ($activeDeadlines as $activeDeadline) {
                            $date = new DateTime($activeDeadline['expiration']);
                            $dateSet = new DateTime($activeDeadline['date']);
                            ?>
                            <div class="dashboard-news-card dashboard-news-card-active">
                                <h4 class="dashboard-news-card-title"><a href="classes/deadline.php?id=<?= $activeDeadline['id'] ?>" style="color: #0a3f5e;">Deadline: Reach question <?= $activeDeadline['point'] ?> by <?= $date->format('d/m/Y') ?></a></h4>
                                You set this on:
                                <i style="font-size: 0.75em;"><?= $dateSet->format('d/m/Y') ?></i>
                            </div>
                        <? }
                    }
                }?>
            <hr>
            <?  $classIDs = $codesetClasses->getUsersClass($uid, True);
                foreach ($classIDs as $classID) {
                    $news = $codesetNews->getClassNews($classID);
                    foreach ($news as $new) {
                        $date = new DateTime($new['date']);
                    ?>
                        <div class="dashboard-news-card">
                            <h4 class="dashboard-news-card-title"><?= $new['title'] ?></h4>
                            <p><?= $new['body'] ?></p>
                            <i style="font-size: 0.75em;"><?= $date->format("d/m/Y") ?></i>
                        </div>
                <?  }
                } ?>
            <!-- <div class="dashboard-news-card">
                <h4 class="dashboard-news-card-title">Title</h4>
                <p>Body</p>
                <i>Date</i>
            </div> -->
        </div>
        <div class="dashboard-leaderboard">

            <? if ($user['permission'] == 0) { ?>
                <h3>Class Leaderboard</h3>
                <? $leaderboard = $codesetClasses->getClassLeaderboard($codesetClasses->getUsersClass($uid), $authDBH, $codesetThestral->getLastQuestionID());
                ?>
                <ol>
                    <? foreach ($leaderboard as $student) {
                        ?>
                        <li style="margin-top: 5%;"><img style="border-radius: 100%; width: 10%; margin-bottom: -3%; padding-right: 1%;" src="/public_html/assets/images/profiles/<?= $student[2] ?>"><?= $student[1] ?> - <?= $student[0] ?>% complete</li>
                    <? } ?>
                </ol>
            <? } elseif ($user['permission'] == 1) { ?>
                <h3>Classes Leaderboard</h3>
                <? $leaderboard = $codesetClasses->getClassesLeaderboard($uid, $authDBH, $codesetThestral->getLastQuestionID()); ?>
                <ol>
                    <? foreach ($leaderboard as $class) {
                        ?> <li><?= $class[1] ?> - <?= $class[0] ?>% complete</li>
                    <? } ?>
                </ol>
            <? } ?>
        </div>
    </main>
    <footer>
        <h5>&copy; CodeSet 2016</h5>
    </footer>
</body>
<script type="text/javascript">
    console.log("<?= $codesetThestral->getLastQuestionID() ?>");
</script>
<script type="text/javascript">
    // // var totalWidth = (circle + line) * <?= $codesetThestral->getLastQuestionID() ?> + circle;
    // // var totalWidth = $( document ).width() * 7
    // var lastBarrier = 0;
    // var barriers = [];
    // var n = 1;
    // for (var i = 1; i <= <?= $codesetThestral->getLastQuestionID() + 2 ?>; i++) {
    //     if (i % 7 == 0 ) {
    //         barriers.push([lastBarrier + 1,  7 * n]);
    //         lastBarrier += 7;
    //         n++;
    //     }
    // }
    // var section = null;
    // for (var i = 0; i < barriers.length; i++) {
    //     if (<?= $user['progress'] ?> >= barriers[i][0] && <?= $user['progress'] ?> <= barriers[i][1]) {
    //             section = i;
    //     }
    // }
    // // var left = left/$('#dashboard-progress').width() * 100;
    // $('#dashboard-progress').scrollLeft($( document ).width() * section);
    (function($) {
        $.fn.goTo = function() {
            $('html, body').animate({
                scrollTop: $(this).offset().top + 'px'
            }, 'fast');
            return this; // for chaining...
        }
    })(jQuery);
    $('.dashboard-progress').scrollTo('#dashboard-progress-circle-current');
    // $('#dashboard-progress').scrollRight($( document ).width() / 2);
</script>
</html>

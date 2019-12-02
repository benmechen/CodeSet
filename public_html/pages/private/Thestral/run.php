<?php
/**
 * @Author: Ben
 * @Date: 2017-02-01 18:28:41
 * @Project: codeset.co.uk
 * @File Name: run.php
 * @Last Modified by:   Ben
 * @Last Modified time: 2017-09-07 18:04:25
**/

ini_set('memory_limit','500M');

include $_SERVER['DOCUMENT_ROOT'].'/config.php';

$uid = $auth->getUIDFromHash($auth->getSessionHash())['uid'];

$user = $auth->getUser($uid);

if (isset($_POST['code']) && isset($_POST['userID']) && isset($_POST['questionID'])) {
    if (isset($_POST['inputs'])) {
        $output = $codesetThestral->run($_POST['code'], $_POST['userID'], $_POST['questionID'], $_SERVER['DOCUMENT_ROOT']."/private_server/guard/", $_POST['inputs']);
    } else {
        $output = $codesetThestral->run($_POST['code'], $_POST['userID'], $_POST['questionID'], $_SERVER['DOCUMENT_ROOT']."/private_server/guard/");
    }

    $check = $codesetThestral->check($_POST['questionID'], $output['message'], $_POST['code']);
    // $output['message'] = $auth->getProgress($uid);
    if ($check['correct']) {
        if ($auth->getProgress($uid) < $_POST['questionID']) {
            $update = $auth->setProgress($uid, $_POST['questionID']);
        }

        $percentage = round(($_POST['questionID'] / $codesetThestral->getLastQuestionID()) * 100);
        $previousPercentage = round((($_POST['questionID']-1) / $codesetThestral->getLastQuestionID()) * 100);
        $nextPercentage = round((($_POST['questionID']+1) / $codesetThestral->getLastQuestionID()) * 100);

        $output['user_code'] = $check['CODE1'];
        $output['question_code'] = $check['CODE2'];

        if ($previousPercentage < 25 && $nextPercentage > 25) {
            $codesetNews->addClassNews($codesetClasses->getUsersClass($uid), $user['name']." has reached a milestone in the course", $user['name']." is 25% of the way through the course.");
        } elseif ($previousPercentage < 50 && $nextPercentage > 50) {
            $codesetNews->addClassNews($codesetClasses->getUsersClass($uid), $user['name']." has reached a milestone in the course", $user['name']." is 50% of the way through the course.");
        } elseif ($previousPercentage < 75 && $nextPercentage > 75) {
            $codesetNews->addClassNews($codesetClasses->getUsersClass($uid), $user['name']." has reached a milestone in the course", $user['name']." is 75% of the way through the course.");
        } elseif ($percentage == 100) {
            $codesetNews->addClassNews($codesetClasses->getUsersClass($uid), $user['name']." has reached a milestone in the course", $user['name']." has completed the course!");
        }
    }

    $output['message'] = nl2br(htmlspecialchars($output['message']));
    $json = array($output, $check);
    echo json_encode($json);

} elseif (isset($_POST['userID']) && isset($_POST['questionID'])) {
    echo $codesetThestral->deleteSavedCode($_POST['userID'], $_POST['questionID']);
}

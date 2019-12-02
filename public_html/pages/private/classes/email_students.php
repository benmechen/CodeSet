<?php
/**
 * @Author: Ben
 * @Date: 2017-06-29 09:55:38
 * @Project: codeset.co.uk
 * @File Name: email_students.php
 * @Last Modified by: Ben
 * @Last Modified time: 2017-06-29 12:07:18
**/
include '../../../../config.php';

if (isset($_POST['classID'])) {
    $students = $codesetClasses->getStudents($_POST['classID']);

    foreach ($students as $student) {
        $studentDetails = $auth->getUser($student['user_id']);
        $email = $codesetClasses->composeStudentLoginEmail($studentDetails['name'], $studentDetails['email'], $studentDetails['email'], $studentDetails['generated_password']);
        $codesetMail->Send("CodeSet <server@codeset.co.uk>", $email['email'], $email['subject'], $email['body']);
    }
}
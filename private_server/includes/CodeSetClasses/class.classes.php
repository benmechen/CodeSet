<?php
/**
 * @Author: Ben
 * @Date: 2016-12-23 19:17:17
 * @Project: codeset.co.uk
 * @File Name: class.classes.php
 * @Last Modified by:   Ben
 * @Last Modified time: 2017-08-25 20:26:31
**/

namespace CodeSet;

/**
 * Classes class
 * Manage CodeSet classes
 */

class Classes
{

    private $dbh;

    public function __construct(\PDO $dbh)
    {
        $this->dbh = $dbh;
    }

    /**
     * Retrieves all user's classes
     * @param string $userID
     * @return array $data
     */
    public function getClasses($userID)
    {
        $query = $this->dbh->prepare("SELECT * FROM classes WHERE author_id = ?");
        $query->execute(array($userID));

        if ($query->rowCount() == 0) {
            return false;
        }

        $data = $query->fetchAll(\PDO::FETCH_ASSOC);

        if (!$data) {
            return false;
        }

        return $data;
    }

    /**
     * Gets class user is in
     * @param string $userID
     * @param bool $teacher
     * @return int $id
     */
    public function getUsersClass($userID, $teacher = false)
    {
        $query = $this->dbh->prepare("SELECT `class_id` FROM `membership` WHERE `user_id` = ?");
        $query->execute(array($userID));

        if ($query->rowCount() == 0) {
            return false;
        }

        if ($teacher) {
            $id = $query->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            $id = $query->fetch(\PDO::FETCH_ASSOC);
        }

        if (!$id) {
            return false;
        }

        if ($teacher) {
            $ids_final = array();
            foreach ($id as $_id) {
                array_push($ids_final, $_id['class_id']);
            }
            return $ids_final;
        }

        return $id['class_id'];
    }

    /**
     * Retrieves class details
     * @param string $classID
     * @return array $data
     */
    public function getClass($classID)
    {
        $query = $this->dbh->prepare("SELECT * FROM classes WHERE id = ?");
        $query->execute(array($classID));

        if ($query->rowCount() == 0) {
            return false;
        }

        $data = $query->fetch(\PDO::FETCH_ASSOC);

        if (!$data) {
            return false;
        }

        return $data;
    }

    /**
     * Gets a class leaderboard
     * @param string $classID
     * @param object $auth
     * @param int $questions
     * @return array $leaderboard
     */
    public function getClassLeaderboard($classID, $auth, $questions)
    {
        $students = $this->getStudents($classID);

        $ids = array();
        foreach ($students as $student) {
            if ($student['type'] != 1) {
                array_push($ids, intval($student['user_id']));
            }
        }

        $ids = implode(',', $ids);

        $query = $auth->prepare("SELECT * FROM users WHERE id IN ($ids) ORDER BY progress DESC");
        $query->execute();

        if ($query->rowCount() == 0) {
            return false;
        }

        $students = $query->fetchAll(\PDO::FETCH_ASSOC);

        if (!$students) {
            return false;
        }

        $leaderboard = array();

        foreach ($students as $student) {
            $progress = $this->percentScore(array($student['progress']), $questions);
            array_push($leaderboard, array($progress, $student['name'], $student['profile'], $student['progress']));
        }

        return $leaderboard;
    }

     /**
     * Returns leaderboard for user's classes based on total score
     * @param string $userID
     * @param object $auth
     * @return array $data
     */
    public function getClassesLeaderboard($userID, $auth, $questions)
    {
        $query = $this->dbh->prepare("SELECT * FROM classes WHERE author_id = ?");
        $query->execute(array($userID));

        if ($query->rowCount() == 0) {
            return false;
        }

        $classes = $query->fetchAll(\PDO::FETCH_ASSOC);

        if (!$classes) {
            return false;
        }

        $averages = array();

        foreach ($classes as $class) {
            $query = $this->dbh->prepare("SELECT user_id FROM membership WHERE class_id = ? AND type != 1");
            $query->execute(array($class['id']));

            if ($query->rowCount() != 0) {
                $students = $query->fetchAll(\PDO::FETCH_ASSOC);

                if (!$students) {
                    return false;
                }

                $scores = array();
                $total = 0;

                foreach ($students as $student) {
                    $query = $auth->prepare("SELECT progress FROM users WHERE id = ?");
                    $query->execute(array($student['user_id']));

                    if ($query->rowCount() == 0) {
                        return false;
                    }

                    $progress = $query->fetch(\PDO::FETCH_ASSOC);

                    if (!$progress) {
                        return false;
                    }
                    array_push($scores, $progress['progress']);
                    $total += $questions;
                }

                array_push($averages, array($this->percentScore($scores, $total), $class['name']));
            } else {
                array_push($averages, array(0, $class['name']));
            }
        }
        usort($averages, function($b, $a) {
            return $a[0] - $b[0];
        });
        return $averages;
    }

    /**
     * Retrieves class's students
     * @param string $classID
     * @return array $data
     */
    public function getStudents($classID)
    {
        $query = $this->dbh->prepare("SELECT * FROM membership WHERE class_id = ? AND type = 0");
        $query->execute(array($classID));

        if ($query->rowCount() == 0) {
            return false;
        }

        $data = $query->fetchAll(\PDO::FETCH_ASSOC);

        if (!$data) {
            return false;
        }

        return $data;
    }

    /**
     * Adds a user to a class
     * @param string $userID
     * @param string $classID
     * @return bool
     */
    public function addUser($classID, $userID, $type)
    {
        $query = $this->dbh->prepare("INSERT INTO membership (class_id, user_id, type) VALUES (?,?,?)");

        if (!$query->execute(array($classID, $userID, $type))) {
            return false;
        }

        return true;
    }

    /**
     * Deletes a user from a class
     * @param string $userID
     * @param string $classID
     * @return bool
     */
    public function deleteUser($classID, $userID)
    {
        $query = $this->dbh->prepare("DELETE FROM membership WHERE class_id = ? AND user_id = ?");

        if (!$query->execute(array($classID, $userID))) {
            return false;
        }

        return true;
    }

    /**
     * Creates a CodeSet class
     * @param string $name
     * @param string $user
     * @param int $remember
     * @param string $captcha = NULL
     * @return bool
     */
    public function create($name, $userID)
    {
        $query = $this->dbh->prepare("SELECT id FROM classes");
        $query->execute();

        $occuredIDs = $query->fetchAll(\PDO::FETCH_ASSOC);

        $query = $this->dbh->prepare("INSERT INTO classes (id, name, author_id) VALUES (?,?,?)");

        if (!$query->execute(array($this->generateID(20, $occuredIDs), $name, $userID))) {
            return false;
        }

        $query = $this->dbh->prepare("SELECT id FROM classes WHERE name = ? AND author_id = ?");
        $query->execute(array($name, $userID));

        if ($query->rowCount() == 0) {
            return false;
        }

        $classID = $query->fetch(\PDO::FETCH_ASSOC);

        if (!$classID) {
            return false;
        }

        if (!$this->addUser($classID['id'], $userID, 1)) {
            return false;
        }

        return true;
    }

    /**
     * Deletes a CodeSet class
     * @param string $classID
     * @param string $userID
     * @return bool
     */
    public function delete($classID, $userID)
    {
        if ($this->checkUserClassPermission($classID, $userID)) {
            $query = $this->dbh->prepare("DELETE FROM classes WHERE id = ?");

            if (!$query->execute(array($classID))) {
                return "Could not delete class. Please try again";
            } else {
                $query = $this->dbh->prepare("DELETE FROM membership WHERE class_id = ?");

                if (!$query->execute(array($classID))) {
                    return "Could not remove users from class";
                }
                return true;
            }
        } else {
            return "You do not have permission to delete that class";
        }
    }

    /**
     * Renames a CodeSet class
     * @param string $newName
     * @param string $classID
     * @param string $userID
     * @return bool
     */
    public function rename($newName, $classID, $userID)
    {
        if ($this->checkUserClassPermission($classID, $userID)) {
            $query = $this->dbh->prepare("UPDATE classes SET name = ? WHERE id = ?");

            if (!$query->execute(array(htmlspecialchars($newName), $classID))) {
                return "Could not rename class. Please try again";
            }
            return true;
        } else {
            return "You do not have permission to rename that class";
        }
    }

    /**
     * Composes subject and body for student help
     * @param string $teacherEmail
     * @param string $studentName
     * @param string $className
     * @param int $questionID
     * @param string $code
     * @param string $instructions
     * @param string $comments
     * @return bool
     */
    public function composeStudentToTeacherEmail($teacherEmail, $studentName, $studentEmail, $className, $questionID, $code, $instructions, $comments)
    {
        $subject = "$studentName is stuck on question $questionID";
        $body = "
            <h2>$studentName, from the class $className, is stuck on question $questionID</h2>
            <br>
            <p><b>Question:</b> ".nl2br($instructions)."</p>
            <p><b>Student's Comments:</b> ".nl2br($comments)."</p>
            <p><b>$studentName's code:</b> <br><code>".nl2br($code)."</code></p>
            <br>
            <p>You can reply to $studentName here: <a href='mailto:$studentEmail?Subject=Codeset - Question $questionID Help'>$studentEmail</a></p>
        ";
        return ['subject' => $subject, 'body' => $body, 'email' => $teacherEmail];
    }

    /**
     * Composes subject and body for student help
     * @param string $teacherEmail
     * @param string $studentName
     * @param string $className
     * @param int $questionID
     * @param string $code
     * @param string $instructions
     * @param string $comments
     * @return bool
     */
    public function composeStudentLoginEmail($studentName, $studentEmail, $username, $password)
    {
        $subject = "Your CodeSet Account";
        $body = "
            <h2>Hello $studentName</h2>
            <br>
            <p>Your teacher has created your CodeSet account. You can log in with the details below:</p>
            <p><b>Username:</b> <code>$username</code></p>
            <p><b>Password:</b> <code>$password</code></p>
            <br>
            <p>You can log into your account <a href='https://codeset.co.uk/public_html/pages/public/signin.php'>here</a></p>
        ";
        return ['subject' => $subject, 'body' => $body, 'email' => $studentEmail];
    }

    /**
     * Checks if a user has permission for a class
     * @param string $classID
     * @param string $userID
     * @return bool
     */
    private function checkUserClassPermission($classID, $userID)
    {
        $query = $this->dbh->prepare("SELECT author_id FROM classes WHERE id = ?");
        $query->execute(array($classID));

        if ($query->rowCount() == 0) {
            return false;
        }

        $data = $query->fetch(\PDO::FETCH_ASSOC);

        if (!$data) {
            return false;
        }

        if ($data['author_id'] == $userID) {
            return true;
        }
    }

    /**
     * Calculates percentage complete for all students in a class
     * @param array $scores
     * @param int $max
     * @return int
     */
    private function percentScore($scores, $max)
    {
        $scoreTotal = 0;
        foreach ($scores as $score) {
            $scoreTotal += $score;
        }
        return round(($scoreTotal/$max)*100);
    }

    /**
     * Generates random ID for class
     * @param int $length
     * @param $array $occured
     * @return int
     */
    private function generateID($length, $occured)
    {
        if (function_exists("random_bytes")) {
            $bytes = random_bytes(ceil($length / 2));
        } elseif (function_exists("openssl_random_pseudo_bytes")) {
            $bytes = openssl_random_pseudo_bytes(ceil($length / 2));
        } else {
            return false;
        }

        $id = substr(bin2hex($bytes), 0, $length);

        if (in_array($id, $occured)) {
            return $this->generateID($length, $occured);
        } else {
            return $id;
        }
    }
}

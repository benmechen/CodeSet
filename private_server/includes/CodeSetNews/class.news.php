<?php
/**
 * @Author: Ben
 * @Date: 2016-11-26 12:45:30
 * @Project: codeset.co.uk
 * @File Name: class.news.php
 * @Last Modified by:   Ben
 * @Last Modified time: 2017-08-29 19:13:58
**/

namespace CodeSet;

/**
 * News class
 * Class news system
 */

class News{
    
    private $dbh;

    public function __construct(\PDO $dbh) {
        $this->dbh = $dbh;
    }

    public function setClassDeadline($classID, $point, $expiration, $maxQuestions) {
        $return = ['error' => True, 'message' => ''];

        if ($point > $maxQuestions) {
            $return['message'] = "Please enter a valid question number";
            return $return;
        }

        $now = new \DateTime("now");
        $date = explode('-', $expiration);
        $expiration = new \DateTime();
        $expiration->setDate($date[0], $date[1], $date[2]);
        $expiration->setTime(23, 59, 59);

        if ($now > $expiration) {
            $return['message'] = "Please enter a valid date";
            return $return;
        }

        $query = $this->dbh->prepare("INSERT INTO deadlines (`class_id`, `point`, `expiration`, `date`) VALUES (?,?,?,?)");

        if (!$query->execute(array($classID, $point, $expiration->format('Y-m-d H:i:s'), $now->format('Y-m-d')))) {
            $return['message'] = "Could not add deadline. Please try again.";
        }

        $return['error'] = False;
        return $return;
    }

    public function getClassDeadlines($classID) {
        $query = $this->dbh->prepare("SELECT * FROM deadlines WHERE class_id = ? ORDER BY expiration DESC");
        $query->execute(array(strval($classID)));

        if ($query->rowCount() == 0) {
            return false;
        }

        $deadlines = $query->fetchAll(\PDO::FETCH_ASSOC);

        if (!$deadlines) {
            return false;
        }

        return $deadlines;
    }

    public function getDeadlineInfo($deadlineID) {
        $query = $this->dbh->prepare("SELECT * FROM deadlines WHERE id = ?");
        $query->execute(array($deadlineID));

        if ($query->rowCount() == 0) {
            return false;
        }

        $deadline = $query->fetch(\PDO::FETCH_ASSOC);

        if (!$deadline) {
            return false;
        }

        return $deadline;
    }

    public function getActiveDeadlines($classID) {
        $deadlines = $this->getClassDeadlines($classID);
        
        if ($deadlines == False) {
            return False;
        }

        $activeDeadlines = array();

        foreach ($deadlines as $deadline) {
            $date = new \DateTime($deadline['expiration']);
            
            if ($date >= new \DateTime()) {
                array_push($activeDeadlines, $deadline);
            }
        }

        return array_reverse($activeDeadlines);
    }

    public function deleteClassDeadline($deadlineID) {
        $query = $this->dbh->prepare("DELETE FROM deadlines WHERE id = ?");

        if (!$query->execute(array($deadlineID))) {
            return False;
        } 

        return True;
    }

    public function getClassIdFromDeadlineId($deadlineID) {
        $query = $this->dbh->prepare("SELECT class_id FROM deadlines WHERE id = ?");
        $query->execute(array($deadlineID));

        if ($query->rowCount() == 0) {
            return false;
        }

        $classID = $query->fetch(\PDO::FETCH_ASSOC);

        if (!$classID) {
            return false;
        }

        return $classID['class_id'];
    }

    public function addClassNews($classID, $title, $body) {
        $previousNews = $this->getClassNews($classID);

        foreach ($previousNews as $previousNew) {
            if (in_array($body, $previousNew)) {
                return False;
            }   
        }

        $now = new \DateTime();
        $query = $this->dbh->prepare("INSERT INTO news (`class_id`, `title`, `body`, `date`) VALUES (?,?,?,?)");

        if (!$query->execute(array($classID, htmlspecialchars($title), htmlspecialchars($body), $now->format('Y-m-d')))) {
            return False;
        }

        return True;
    }

    public function getClassNews($classID) {
        $query = $this->dbh->prepare("SELECT * FROM news WHERE class_id = ? ORDER BY id DESC LIMIT 10");
        $query->execute(array(strval($classID)));

        if ($query->rowCount() == 0) {
            return false;
        }

        $news = $query->fetchAll(\PDO::FETCH_ASSOC);

        if (!$news) {
            return false;
        }

        return $news;
    }

    public function deleteClassNews($newsID) {
        $query = $this->dbh->prepare("DELETE FROM news WHERE id = ?");

        if (!$query->execute(array($newsID))) {
            return False;
        } 

        return True;
    }
}
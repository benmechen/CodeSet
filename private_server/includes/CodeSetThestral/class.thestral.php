<?php
/**
 * @Author: Ben
 * @Date: 2017-02-02 16:27:43
 * @Project: codeset.co.uk
 * @File Name: class.thestral.php
 * @Last Modified by:   Ben
 * @Last Modified time: 2017-09-07 17:59:46
**/

namespace CodeSet;

/**
 * Thestral class
 * Runs Thestral system
 */

class Thestral
{

    private $dbh;
    private $guard;

    public function __construct(\PDO $dbh)
    {
        $this->dbh = $dbh;
        $this->guard = $_SERVER['DOCUMENT_ROOT']."/private_server/includes/CodeSetThestral/thestral-guard.py";
    }

    /**
     * Gets question data
     * @param string $id
     * @return array
     */
    public function getQuestion($id)
    {
        $query = $this->dbh->prepare("SELECT * FROM questions WHERE id = ?");
        $query->execute(array($id));

        if ($query->rowCount() == 0) {
            return false;
        }

        return $query->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Gets all questions
     * @return array
     */
    public function getAllQuestions()
    {
        $query = $this->dbh->prepare("SELECT * FROM questions");
        $query->execute(array($id));

        if ($query->rowCount() == 0) {
            return false;
        }

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Gets last question ID
     * @param string $userID
     * @param string $questionID
     * @return string
     */
    public function getLastQuestionID()
    {
        $query = $this->dbh->prepare("SELECT id FROM questions ORDER BY id DESC LIMIT 1;");
        $query->execute();

        if ($query->rowCount() == 0) {
            return false;
        }

        return $query->fetch(\PDO::FETCH_ASSOC)['id'];
    }

    /**
     * Gets user's saved code from file
     * @param string $userID
     * @param string $questionID
     * @return string
     */
    public function getSavedCode($userID, $questionID)
    {
        $file = $_SERVER['DOCUMENT_ROOT']."/private_server/guard/CELL-".$userID."/Q-".$questionID."/".$userID."-".$questionID.".py";
        if (file_exists($file)) {
            $code = file_get_contents($file);
            if (!empty($code)) {
                return $code;
            }
        }

        return false;
    }

    /**
     * Deletes user's saved code file
     * @param string $userID
     * @param string $questionID
     * @return bool
     */
    public function deleteSavedCode($userID, $questionID)
    {
        $file = $_SERVER['DOCUMENT_ROOT']."/private_server/guard/CELL-".$userID."/Q-".$questionID."/".$userID."-".$questionID.".py";
        if (file_exists($file)) {
            if (!unlink($file)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks user's code
     * @param string $code
     * @param string $userID
     * @return string
     */
    public function check($questionID, $output, $code)
    {
        $return['error'] = true;
        $return['correct'] = false;

        $question = $this->getQuestion($questionID);

        if (!$question) {
            return $return;
        }

        if ($question['output'] != "EMPTY") {
            if ($question['output'] != "NULL" && $question['output'] != "CODE") {
                $question['output'] = preg_replace('/[\r\n]+|\s+/', '', $question['output']);
                $output = preg_replace('/[\r\n]+|\s+/', '', $output);

                if ($question['output'] == "INT") {
                    if (is_null(is_numeric($output))) {
                        $return['correct'] = false;
                        $return['error'] = false;
                        return $return;
                    }
                } elseif ($question['output'] != $output) {
                    $return['correct'] = false;
                    $return['error'] = false;
                    $return['expectedOutput'] = $question['output'];
                    $return['actualOutput'] = $output;
                    return $return;
                }
            }

            if ($question['output'] == "NULL") {
                if ($output != "") {
                    $return['correct'] = false;
                    $return['error'] = false;
                    return $return;
                }
            }

            $percent = 0;

            $codeArray = explode("\n", $code);
            $i = 0;
            $multiLine = false;

            foreach ($codeArray as $codeLine) {
                if ($multiLine == true) {
                    if (substr($codeLine, 0, 3) == '"""') {
                        $multiLine = false;
                    }
                    unset($codeArray[$i]);
                    array_values($codeArray);
                } else {
                    if (substr($codeLine, 0, 1) == "#") {
                        unset($codeArray[$i]);
                        array_values($codeArray);
                    }
                    if (substr($codeLine, 0, 3) == '"""') {
                        $multiLine = true;
                        unset($codeArray[$i]);
                        array_values($codeArray);
                    }
                }
                $i++;
            }

            $code = implode("", $codeArray);
            $code = preg_replace("/\r|\n/", "", $code);

            foreach (json_decode($question['code']) as $expectedCode) {
                $expectedCode = preg_replace("/\r|\n/", "", $expectedCode);
                $code = json_decode(json_encode($code));
                $expectedCode = json_decode(json_encode($expectedCode));
                $return['CODE1'] = $code;
                $return['CODE2'] = $expectedCode;
                similar_text($code, $expectedCode, $newpercent);
                if ($newpercent > $percent) {
                    $percent = $newpercent;
                }
            }
            $return['percent'] = $percent;

            if ($percent >= $question['percent']) {
                $return['correct'] = true;
                $return['error'] = false;
                return $return;
            } else {
                $return['correct'] = false;
                $return['error'] = false;
                return $return;
            }
        } else {
            $return['correct'] = true;
            $return['error'] = false;
            return $return;
        }

        return $return;
    }

    /**
     * Runs user's code
     * @param string $code
     * @param string $userID
     * @return string
     */
    public function run($code, $userID, $questionID, $loc, $inputs = None)
    {
        $return['error'] = true;

        if (!$this->checkUserCell($loc."/CELL-".$userID)) {
            $createUserCell = $this->createUserCell($loc, $userID);
            if ($createUserCell['error'] == true) {
                $return['message'] = $createUserCell['message'];
                return $return;
            }
        }

        if (!$this->checkQuestionCell($loc."/CELL-".$userID."/Q-".$questionID)) {
            $createQuestionCell = $this->createQuestionCell($loc."/CELL-".$userID."/", $questionID);
            if ($createQuestionCell['error'] == true) {
                $return['message'] = $createQuestionCell['message'];
                return $return;
            }
        }
        $createCodeFile = $this->createCodeFile($loc, $userID, $questionID, $code);
        if ($createCodeFile['error'] == true) {
            $return['message'] = $createCodeFile['message'];
            return $return;
        }
        if ($questionID == 75) {
            $output = fopen($loc."/CELL-".$userID."/Q-".$questionID."/output.txt", "w");
            fwrite($output, "1
                            4
                            9
                            16
                            25
                            36
                            49
                            64
                            81
                            100");
            fclose($output);
        }
        $return['message'] = $this->execute($_SERVER['DOCUMENT_ROOT']."/private_server/guard/CELL-".$userID."/Q-".$questionID."/".$userID."-".$questionID.".py", $inputs, $userID);

        $return['error'] = false;
        return $return;
    }

    /**
     * Checks if user's cell already exists
     * @param string $loc
     * @return Bool
     */
    private function checkUserCell($loc)
    {
        if (file_exists($loc)) {
            return true;
        }

        return false;
    }

    /**
     * Checks if user's question cell already exists
     * @param string $loc
     * @return Bool
     */
    private function checkQuestionCell($loc)
    {
        if (file_exists($loc)) {
            return true;
        }

        return false;
    }

    /**
     * Creates location for user's code
     * @param string $loc
     * @param string $id
     * @return Array
     */
    private function createUserCell($loc, $userID)
    {
        $return['error'] = true;

        $cell = "CELL-".$userID;
        if (!mkdir($loc.$cell, 0744)) {
            $return['message'] = "Could not create cell";
            return $return;
        }

        $return['error'] = false;
        return $return;
    }

    /**
     * Creates location for user's code
     * @param string $loc
     * @param string $id
     * @return Array
     */
    private function createQuestionCell($loc, $questionID)
    {
        $return['error'] = true;

        $cell = "Q-".$questionID;
        if (!mkdir($loc.$cell, 0700)) {
            $return['message'] = "Could not create cell";
            return $return;
        }

        $return['error'] = false;
        return $return;
    }

    /**
     * Creates python file to store code
     * @param string $loc
     * @param string $userID
     * @param string $questionID
     * @param string $code
     * @return Array
     */
    public function createCodeFile($loc, $userID, $questionID, $code)
    {
        $return['error'] = true;

        $questionFile = fopen($loc."/CELL-".$userID."/Q-".$questionID."/".$userID."-".$questionID.".py", "w");
        $this->codeFile = $loc."/CELL-".$userID."/Q-".$questionID."/".$userID."-".$questionID.".py";
        $write = fwrite($questionFile, $code);

        if ($write === false) {
            $return['message'] = "Could not write to file";
            return $return;
        }

        $return['error'] = false;
        return $return;
    }

    private function execute($file, $inputs, $userID)
    {
        chdir(dirname($file));
        $code = file_get_contents($file);

        if (strpos($code, 'import os') !== false && $userID != 1) {
            $output = "Module 'os' is blocked";
            return $output;
        }
        if (strpos($code, 'import sys') !== false && $userID != 1) {
            $output = "Module 'sys' is blocked";
            return $output;
        }
        if (strpos($code, 'import shutil') !== false && $userID != 1) {
            $output = "Module 'shutil' is blocked";
            return $output;
        }
        if (strpos($code, 'import subprocess') !== false && $userID != 1) {
            $output = "Module 'subprocess' is blocked";
            return $output;
        }

        $output = "";
        $descriptorspec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("file", "error.txt", "w"),
        );

        $process = proc_open("python3 $file", $descriptorspec, $pipes);

        if (is_resource($process)) {
            $count = substr_count(file_get_contents($file), "input(");
            for ($i=0; $i < $count; $i++) {
                fwrite($pipes[0], $inputs[$i]);
                fwrite($pipes[0], "\n");
            }
            fclose($pipes[0]);

            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            $return_value = proc_close($process);
            if ($return_value != 0) {
                $output = file_get_contents('error.txt');
                $output = str_replace('File "'.$file.'", ', "", $output);
            }
        }

        return $output;
        // $file = escapeshellcmd($file);
        // $execute = shell_exec("/kunden/homepages/8/d657042007/htdocs/python34/Python-3.4.2/python $this->guard 2>&1 $file");
        // return "False";
    }
}

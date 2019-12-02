<?php
/**
 * @Author: Ben
 * @Date: 2017-01-25 17:27:26
 * @Project: codeset.co.uk
 * @File Name: class.logs.php
 * @Last Modified by: Ben
 * @Last Modified time: 2017-04-25 22:33:18
**/

namespace CodeSet;

/**
 * Monitor class
 * Monitors CodeSet actions
 */

class Logs {

    public $lang;
    private $logFile;
    private $mail;

    public function __construct($logFile, $mail) {
        $this->logFile = fopen($logFile, "a+");
        include 'lang.php';
        $this->lang = $lang;
        $this->mail = $mail;
    }

    /**
     * Logs to file
     * @param string $name
     * @param string $params
     * @param string $user
     * @return NULL
     */
    public function log($name, $params = NULL, $user = NULL) {
        if (!fwrite($this->logFile, $this->createLog($name, $params, $user))) {
            $from = "CodeSet <server@codeset.co.uk>";
            $to = "<benm123@yahoo.com>";
            // $this->mail->Send($from, $to, "CodeSet Server - Runtime Logging Error", "<h1>Logging Error</h1><br><br><hr><br><br>".$this->createLog($name, $params, $user));
        }
    }

    /**
     * Creates log
     * @param string $name
     * @param string $params
     * @param string $user
     * @return string
     */
    private function createLog($name, $params = NULL, $user = NULL) {
        $log = "[".$this->lang[$name]['type']."]" . date(DATE_ATOM) . " ==> " . $this->lang[$name]['message'] . " -- " . var_export($params, true) . " " . $_SERVER['REMOTE_ADDR']. "\n";
        return $log;
    }


}
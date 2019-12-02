<?php
/**
 * @Author: Ben
 * @Date: 2017-01-20 21:25:17
 * @Project: codeset.co.uk
 * @File Name: class.mail.php
 * @Last Modified by:   Ben
 * @Last Modified time: 2017-08-24 11:59:13
**/

namespace CodeSet;

/**
 * Mail class
 * Sends email
 */

class SendMail {

    private $port;
    private $username;
    private $password;

    public function __construct($port, $username, $password) {
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Sends email
     * @param string $from
     * @param string $to
     * @param string $subject
     * @return array $return
     */
    public function Send($from, $to, $subject, $body) {
        require_once "Mail.php";
        $headers = array (
            'From' => $from,
            'To' => $to,
            'Subject' => $subject,
            'Content-type' => 'text/html; charset=iso-8859-1'
            );

        $smtp = \Mail::factory('sendmail', array (
            'auth' => true,
            'port' => $this->port,
            'username' => $this->username,
            'password' => $this->password));

        $mail = $smtp->send($to, $headers, $body);

        if(\PEAR::isError($mail)) {
            return False;
        }
        return True;
    }

}
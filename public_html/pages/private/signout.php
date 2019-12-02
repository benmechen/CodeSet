<?php
/**
 * @Author: Ben
 * @Date: 2016-11-25 22:15:34
 * @Project: codeset.co.uk
 * @File Name: signout.php
 * @Last Modified by: Ben
 * @Last Modified time: 2017-03-19 13:34:17
**/

if (empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] !== "on") {
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}

include '../../../config.php';

$auth->logout($_COOKIE[$auth->config->cookie_name]);

header("Location: /");
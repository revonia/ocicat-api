<?php
/**
 * Auto generate source code header.
 * Original File Name: fast_response.php
 * Author: Wangjian
 * Date: 2017/3/31
 * Time: 12:02
 */

if (
    null !== $_SERVER
    && strtoupper($_SERVER['REQUEST_METHOD']) === 'OPTIONS'
) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS');
    header('Access-Control-Allow-Headers: Origin,Accept,X-Requested-With,Content-Type,Authorization');
    exit(0);
}
<?php
/**
 * Created by PhpStorm.
 * User: Shavkat
 * Date: 10/25/13
 * Time: 2:40 AM
 */

class Session
{
    public function __construct()
    {
        session_start();
    }

    protected static $_instance;

    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new Session();
        }
        return self::$_instance;
    }
} 
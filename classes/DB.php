<?php

class DB {

    private static $db;

    public static function getDB() {
        if (!self::$db || empty(self::$db)) {
            self::$db = 1;
        }
        return self::$db;
    }
}
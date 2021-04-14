<?php

class DB {

    private static $db;
    private static $defaultConfig = [
        'APP' => 'MSSQL php api',
        'ConnectionPooling' => 1
    ];
    private static $tables = [];
    private static $user = 'user';
    private static $password = '1234';

    public static function getDB() {
        if (!self::$db || empty(self::$db)) {
            self::$db = new PDO('sqlsrv:' . self::getConnectionString(), self::$user, self::$password);
        }
        if (empty(self::$tables)) {
            self::$tables = self::$db
                ->query("SELECT TABLE_SCHEMA + cast('.' as varchar) + TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'")
                ->fetchAll(PDO::FETCH_ASSOC);
        }
        return self::$db;
    }

    public static function getAllowedTables() {
        return self::$tables;
    }

    public static function tableIsAllowed($tableName) {
        // TODO: implement user-defined tables list check
        return in_array($tableName, self::$tables);
    }

    private static function getConfig() {
        $configFromFile = [];
        if (is_file(__DIR__ . '../config.php')) {
            $configFromFile = require_once __DIR__ . '../config.php';
            if (isset($configFromFile['MSSQL']))
                $configFromFile = $configFromFile['MSSQL'];
        }
        if (is_file(__DIR__ . '../config-local.php')) {
            $configFromFile = require_once __DIR__ . '../config-local.php';
            if (isset($configFromFile['MSSQL']))
                $configFromFile = array_merge($configFromFile, $configFromFile['MSSQL']);
        }
        return array_merge(self::$defaultConfig, $configFromFile);
    }

    private static function getConnectionString() {
        $pairs = [];
        foreach (self::getConfig() as $key => $value) {
            $pairs[] = $key . '=' . $value;
        }
        return join(';', $pairs);
    }
}
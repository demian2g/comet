<?php

class DB {

    private static $db;
    private static $defaultConfig = [
        'APP' => 'MSSQL php api',
        'ConnectionPooling' => 1,
        'LoginTimeout' => 5,
    ];
    private static $tables = [];
    private static $user = 'user';
    private static $password = 'password';

    public static function getDB() {
        if (!self::$db || empty(self::$db)) {
            try {
                self::$db = new PDO('sqlsrv:' . self::getConnectionString(), self::$user, self::$password);
            } catch (PDOException $p) {
                echo $p->getMessage() . ": " . self::getConnectionString() . "\r\n"; die();
            }
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
        if (is_file(__DIR__ . '/../config.php')) {
            $configFromFile = require __DIR__ . '/../config.php';
            if (isset($configFromFile['MSSQL']))
                $configFromFile = $configFromFile['MSSQL'];
        }
        if (isset($configFromFile['User']) && !empty($configFromFile['User'])) {
            self::$user = $configFromFile['User'];
        }
        if (isset($configFromFile['Password']) && !empty($configFromFile['Password'])) {
            self::$password = $configFromFile['Password'];
        }
        if (is_file(__DIR__ . '/../config-local.php')) {
            $configFromLocalFile = require __DIR__ . '/../config-local.php';
            if (isset($configFromLocalFile['MSSQL'])) {
                if (isset($configFromLocalFile['MSSQL']['User']) && !empty($configFromLocalFile['MSSQL']['User']))
                    self::$user = $configFromLocalFile['MSSQL']['User'];
                if (isset($configFromLocalFile['MSSQL']['Password']) && !empty($configFromLocalFile['MSSQL']['Password']))
                    self::$password = $configFromLocalFile['MSSQL']['Password'];
                $configFromFile = array_merge($configFromFile, $configFromLocalFile['MSSQL']);
            }
        }
        $configFromFile = array_merge(self::$defaultConfig, $configFromFile);
        unset($configFromFile['User']);
        unset($configFromFile['Password']);
        return $configFromFile;
    }

    private static function getConnectionString() {
        $pairs = [];
        foreach (self::getConfig() as $key => $value) {
            $pairs[] = $key . '=' . $value;
        }
        return join(';', $pairs);
    }
}
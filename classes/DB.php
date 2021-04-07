<?php

class DB {

    private static $db;
    private static $defaultConfig = [
        'APP' => 'MSSQL php api',
        'ConnectionPooling' => 1,
        'Database' => 'AdventureWorks',
        'LoginTimeout' => 5,
        'Server' => '192.168.1.226'
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

    public static function tableIsAllowed($tableName) {
        // TODO: implement user-defined tables list check
        return in_array($tableName, self::$tables);
    }

    private static function getConfig() {
        $configFromFile = []; // get config from file
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
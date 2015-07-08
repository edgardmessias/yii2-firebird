<?php

/**
 * This is the configuration file for the Yii2 unit tests.
 * You can override configuration values by creating a `config.local.php` file
 * and manipulate the `$config` variable.
 */
$config = [
    'databases' => [
        'firebird' => [
            'dsn'      => 'firebird:dbname=localhost:/tmp/TEST.FDB;charset=ISO8859_1',
            'username' => 'SYSDBA',
            'password' => 'masterkey',
            'fixture'  => __DIR__ . '/source.sql',
        ],
    ],
];

if (is_file(__DIR__ . '/config.local.php')) {
    include(__DIR__ . '/config.local.php');
}

return $config;

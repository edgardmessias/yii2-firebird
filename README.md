Firebird Extension for Yii 2
==========================

This extension adds [Firebird](http://www.firebirdsql.org/) database engine extension for the [Yii framework 2.0](http://www.yiiframework.com).

[![Build Status](https://travis-ci.org/edgardmessias/yii2-firebird.svg?branch=master)](https://travis-ci.org/edgardmessias/yii2-firebird)
Requirements
------------

At least Firebird version 2.0 is required. However, in order to use all extension features.

Unsupported
------------

Functions not supported by the Firebird database:

 * Rename Table
 * Check Integrity
 * BLOB data type - [See this bug](https://bugs.php.net/bug.php?id=61183)

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist "edgardmessias/yii2-firebird:*"
```

or add

```json
"edgardmessias/yii2-firebird": "*"
```

to the require section of your composer.json.


Configuration
-------------

To use this extension, simply add the following code in your application configuration:

```php
return [
    //....
    'components' => [
        'db' => [
            'class' => 'edgardmessias\db\firebird\Connection',
            'dsn' => 'firebird:dbname=localhost:/tmp/TEST.FDB;charset=ISO8859_1',
            'username' => 'username',
            'password' => 'password',
        ],
    ],
];
```

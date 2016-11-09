Firebird Extension for Yii 2
==========================
[![Latest Stable Version](https://poser.pugx.org/edgardmessias/yii2-firebird/v/stable)](https://packagist.org/packages/edgardmessias/yii2-firebird)
[![Total Downloads](https://poser.pugx.org/edgardmessias/yii2-firebird/downloads)](https://packagist.org/packages/edgardmessias/yii2-firebird)
[![Latest Unstable Version](https://poser.pugx.org/edgardmessias/yii2-firebird/v/unstable)](https://packagist.org/packages/edgardmessias/yii2-firebird)
[![License](https://poser.pugx.org/edgardmessias/yii2-firebird/license)](https://packagist.org/packages/edgardmessias/yii2-firebird)

This branch use last stable version of Yii2 (dev)

This extension adds [Firebird](http://www.firebirdsql.org/) database engine extension for the [Yii framework 2.0](http://www.yiiframework.com).

[![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](http://www.yiiframework.com/)
[![Build Status](https://travis-ci.org/edgardmessias/yii2-firebird.svg?branch=master)](https://travis-ci.org/edgardmessias/yii2-firebird)
[![Dependency Status](https://www.versioneye.com/php/edgardmessias:yii2-firebird/dev-master/badge.png)](https://www.versioneye.com/php/edgardmessias:yii2-firebird/dev-master)
[![Reference Status](https://www.versioneye.com/php/edgardmessias:yii2-firebird/reference_badge.svg)](https://www.versioneye.com/php/edgardmessias:yii2-firebird/references)
[![Code Coverage](https://scrutinizer-ci.com/g/edgardmessias/yii2-firebird/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/edgardmessias/yii2-firebird/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/edgardmessias/yii2-firebird/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/edgardmessias/yii2-firebird/?branch=master)

Requirements
------------

At least Firebird version 2.0 is required. However, in order to use all extension features.

Not work with Firebird 3.0 [See this bug](https://bugs.php.net/bug.php?id=72931)

Unsupported
------------

Functions not supported by the Firebird database:

 * Rename Table
 * Check Integrity 

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

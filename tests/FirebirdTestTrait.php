<?php

namespace edgardmessias\unit\db\firebird;

trait FirebirdTestTrait
{
    private $traitDbs = [];

    public function setUp()
    {
        if (static::$params === null) {
            static::$params = require(__DIR__ . '/data/config.php');
        }
        parent::setUp();
    }
    
    public function tearDown()
    {
        //close all DBs connections
        foreach ($this->traitDbs as $db) {
            $db->close();
        }
        parent::tearDown();
    }

    public function prepareDatabase($config, $fixture, $open = true)
    {
        if (!isset($config['class'])) {
            $config['class'] = '\edgardmessias\db\firebird\Connection';
        }
        /* @var $db \edgardmessias\db\firebird\Connection */
        $db = \Yii::createObject($config);
        
        $this->traitDbs[] = $db;
        
        if (!$open) {
            return $db;
        }
        $db->open();
        if ($fixture !== null) {
            $lines = explode('-- SQL', file_get_contents($fixture));
            foreach ($lines as $line) {
                if (trim($line) !== '') {
                    $db->pdo->exec($line);
                }
            }
            //Unlock resources of table modification.
            $db->close();
            $db->open();
            foreach ($this->traitDbs as $db) {
                if ($db->pdo !== null) {
                    $db->close();
                    $db->open();
                }
            }
        }
        return $db;
    }
}

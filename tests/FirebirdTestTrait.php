<?php

namespace edgardmessias\unit\db\firebird;

trait FirebirdTestTrait
{
    public $reservedWords = [
        'ORDER',
        'POSITION',
        'TIME',
        'VALUE',
    ];

    private $_traitDbs = [];

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
        foreach ($this->_traitDbs as $db) {
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
        
        $this->_traitDbs[] = $db;
        
        if (!$open) {
            return $db;
        }
        $db->open();
        if ($fixture !== null) {
            $lines = explode('-- SQL', file_get_contents($fixture));
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line !== '') {
                    if (preg_match('/^\-\-\s+([\<\>\=]+)\s+(\d+(\.\d+)*)/', $line, $matches)) {
                        if (version_compare($db->firebird_version, $matches[2], $matches[1])) {
                            $db->pdo->exec($line);
                        }
                    } else {
                        $db->pdo->exec($line);
                    }
                }
            }
            //Unlock resources of table modification.
            $db->close();
            $db->open();
            foreach ($this->_traitDbs as $db) {
                if ($db->pdo !== null) {
                    $db->close();
                    $db->open();
                }
            }
        }
        return $db;
    }

    /**
     * adjust dbms specific escaping
     * @param $sql
     * @return mixed
     */
    protected function replaceQuotes($sql)
    {
        $pattern = '/\[\[(' . implode('|', $this->reservedWords) . ')\]\]/i';
        $sql = preg_replace($pattern, '"$1"', $sql);
        
        return str_replace(['[[', ']]'], '', $sql);
    }
}

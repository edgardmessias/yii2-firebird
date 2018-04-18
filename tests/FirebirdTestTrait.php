<?php

namespace edgardmessias\unit\db\firebird;

trait FirebirdTestTrait
{
    public $reservedWords = [
        'ADD',
        'ADMIN',
        'ALL',
        'ALTER',
        'AND',
        'ANY',
        'AS',
        'AT',
        'AVG',
        'BEGIN',
        'BETWEEN',
        'BIGINT',
        'BIT_LENGTH',
        'BLOB',
        'BOTH',
        'BOOLEAN',
        'BY',
        'CASE',
        'CAST',
        'CHAR',
        'CHAR_LENGTH',
        'CHARACTER',
        'CHARACTER_LENGTH',
        'CHECK',
        'CLOSE',
        'COLLATE',
        'COLUMN',
        'COMMIT',
        'CONNECT',
        'CONSTRAINT',
        'CORR',
        'COUNT',
        'COVAR_POP',
        'CREATE',
        'CROSS',
        'CURRENT',
        'CURRENT_CONNECTION',
        'CURRENT_DATE',
        'CURRENT_ROLE',
        'CURRENT_TIME',
        'CURRENT_TIMESTAMP',
        'CURRENT_TRANSACTION',
        'CURRENT_USER',
        'CURSOR',
        'DATE',
        'DAY',
        'DEC',
        'DECIMAL',
        'DECLARE',
        'DEFAULT',
        'DELETE',
        'DELETING',
        'DETERMINISTIC',
        'DISCONNECT',
        'DISTINCT',
        'DOUBLE',
        'DROP',
        'ELSE',
        'END',
        'ESCAPE',
        'EXECUTE',
        'EXISTS',
        'EXTERNAL',
        'EXRACT',
        'FALSE',
        'FETCH',
        'FILTER',
        'FLOAT',
        'FOR',
        'FOREIGN',
        'FROM',
        'FULL',
        'FUNCTION',
        'GDSCODE',
        'GLOBAL',
        'GRANT',
        'GROUP',
        'HAVING',
        'HOUR',
        'IN',
        'INDEX',
        'INNER',
        'INSENSITIVE',
        'INSERT',
        'INSERTING',
        'INT',
        'INTEGER',
        'INTO',
        'IS',
        'JOIN',
        'LEADING',
        'LEFT',
        'LIKE',
        'LONG',
        'LOWER',
        'MAX',
        'MAXIMUM_SEGMENT',
        'MERGE',
        'MIN',
        'MINUTE',
        'MONTH',
        'NATIONAL',
        'NATURAL',
        'NCHAR',
        'NO',
        'NOT',
        'NULL',
        'NUMERIC',
        'OCTET_LENGTH',
        'OF',
        'OFFSET',
        'ON',
        'OPEN',
        'OR',
        'ORDER',
        'OUTER',
        'OVER',
        'PARAMETER',
        'PASSWORD',
        'PLAN',
        'POSITION',
        'POST_EVENT',
        'PRECISION',
        'PRIMARY',
        'PROCEDURE',
        'RDB$DB_KEY',
        'RDB$RECORD_VERSION',
        'REAL',
        'RECORD_VERSION',
        'RECREATE',
        'RECURSIVE',
        'REFERENCES',
        'REGR_AVGX',
        'REGR_AVGY',
        'REGR_COUNT',
        'REGR_INTERCEPT',
        'REGR_R2',
        'REGR_SLOPE',
        'REGR_SXX',
        'REGR_SXY',
        'REGR_SYY',
        'RELEASE',
        'RETURN',
        'RETURNING_VALUES',
        'RETURNS',
        'REVOKE',
        'RIGHT',
        'ROLLBACK',
        'ROW',
        'ROWS',
        'ROW_COUNT',
        'SAVEPOINT',
        'SCROLL',
        'SECOND',
        'SELECT',
        'SENSITIVE',
        'SET',
        'SIMILAR',
        'SOME',
        'SQLCODE',
        'SQLSTATE',
        'START',
        'STDDEV_POP',
        'STDDEV_SAMP',
        'SUM',
        'TABLE',
        'THEN',
        'TIME',
        'TIMESTAMP',
        'TO',
        'TRAILING',
        'TRIGGER',
        'TRIM',
        'TRUE',
        'UNION',
        'UNIQUE',
        'UNKNOWN',
        'UPDATE',
        'UPDATING',
        'UPPER',
        'USER',
        'USING',
        'VALUE',
        'VALUES',
        'VARCHAR',
        'VARIABLE',
        'VARYING',
        'VAR_POP',
        'VAR_SAMP',
        'VIEW',
        'WHEN',
        'WHERE',
        'WHILE',
        'WITH',
        'YEAR',
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
                if (trim($line) !== '') {
                    $db->pdo->exec($line);
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
        $sql = preg_replace('/(\{\{)(%?[\w\-\. ]+%?)(\}\})/', '\1@\2@\3', $sql);
        
        return str_replace(['[[', ']]'], '', $sql);
    }
}

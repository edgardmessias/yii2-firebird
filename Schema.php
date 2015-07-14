<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace edgardmessias\db\firebird;

use yii\db\ColumnSchema;
use yii\db\Exception;
use yii\db\TableSchema;

/**
 * Schema represents the Firebird schema information.
 *
 * @property string[] $indexNames All index names in the Firebird. This property is read-only.
 * @property IndexSchema[] $indexSchemas The metadata for all indexes in the Firebird. Each array element is an
 * instance of [[IndexSchema]] or its child class. This property is read-only.
 * @property array $indexTypes All index types in the Firebird in format: index name => index type. This
 * property is read-only.
 * @property QueryBuilder $queryBuilder The query builder for this connection. This property is read-only.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Schema extends \yii\db\Schema
{

    private $_sequences = [];
    private $_lastInsertID = null;

    /**
     * @var array map of DB errors and corresponding exceptions
     * If left part is found in DB error message exception class from the right part is used.
     */
    public $exceptionMap = [
        'SQLSTATE[23'                                               => 'yii\db\IntegrityException',
        'SQLSTATE[HY000]: General error: -803 violation of PRIMARY' => 'yii\db\IntegrityException',
    ];
    public $revervedWords = [
        'ORDER',
        'TIME',
    ];

    /**
     * @var array mapping from physical column types (keys) to abstract column types (values)
     */
    public $typeMap = [
        'bigint'             => self::TYPE_BIGINT,
        'char'               => self::TYPE_STRING,
        'varchar'            => self::TYPE_STRING,
        'timestamp'          => self::TYPE_TIMESTAMP,
        'decimal'            => self::TYPE_DECIMAL,
        'float'              => self::TYPE_FLOAT,
        'blob'               => self::TYPE_BINARY,
        'integer'            => self::TYPE_INTEGER,
        'blob sub_type text' => self::TYPE_TEXT,
        'numeric'            => self::TYPE_DECIMAL,
        'double precision'   => self::TYPE_DOUBLE,
        'smallint'           => self::TYPE_SMALLINT,
    ];

    /**
     * Creates a query builder for the database.
     * This method may be overridden by child classes to create a DBMS-specific query builder.
     * @return QueryBuilder query builder instance
     */
    public function createQueryBuilder()
    {
        return new QueryBuilder($this->db);
    }

    public function quoteSimpleTableName($name)
    {
        if (in_array(strtoupper($name), $this->revervedWords)) {
            return strpos($name, '"') !== false ? $name : '"' . $name . '"';
        }

        return $name;
    }

    public function quoteSimpleColumnName($name)
    {
        if (in_array(strtoupper($name), $this->revervedWords)) {
            return parent::quoteSimpleColumnName($name);
        }
        return $name;
    }

    protected function loadTableSchema($name)
    {
        $table = new TableSchema;
        $this->resolveTableNames($table, $name);
        if ($this->findColumns($table)) {
            $this->findConstraints($table);
            if (is_string($table->primaryKey) && isset($this->_sequences[$table->fullName . '.' . $table->primaryKey])) {
                $table->sequenceName = $this->_sequences[$table->fullName . '.' . $table->primaryKey];
            } elseif (is_array($table->primaryKey)) {
                foreach ($table->primaryKey as $pk) {
                    if (isset($this->_sequences[$table->fullName . '.' . $pk])) {
                        $table->sequenceName = $this->_sequences[$table->fullName . '.' . $pk];
                        break;
                    }
                }
            }
            return $table;
        }
        return null;
    }

    public function getPdoType($data)
    {
        static $typeMap = [
            // php type => PDO type
            'boolean'  => \PDO::PARAM_INT,
            'integer'  => \PDO::PARAM_INT,
            'string'   => \PDO::PARAM_STR,
            'resource' => \PDO::PARAM_LOB,
            'NULL'     => \PDO::PARAM_NULL,
        ];
        $type = gettype($data);

        return isset($typeMap[$type]) ? $typeMap[$type] : \PDO::PARAM_STR;
    }

    /**
     *
     * @param TableSchema $table
     * @param string $name
     */
    protected function resolveTableNames($table, $name)
    {
        $parts = explode('.', str_replace('"', '', $name));
        if (isset($parts[1])) {
            $table->schemaName = $parts[0];
            $table->name = strtolower($parts[1]);
            $table->fullName = $this->quoteTableName($table->schemaName) . '.' . $this->quoteTableName($table->name);
        } else {
            $table->name = strtolower($parts[0]);
            $table->fullName = $this->quoteTableName($table->name);
        }
    }

    /**
     * Collects the table column metadata.
     *
     * @param TableSchema $table the table metadata
     * @return boolean whether the table exists in the database
     */
    protected function findColumns($table)
    {
        // Zoggo - Converted sql to use join syntax
        // robregonm - Added isAutoInc
        $sql = 'SELECT
                    rel.rdb$field_name AS fname,
                    rel.rdb$default_source AS fdefault,
                    fld.rdb$field_type AS fcodtype,
                    fld.rdb$field_sub_type AS fcodsubtype,
                    fld.rdb$field_length AS flength,
                    fld.rdb$character_length AS fcharlength,
                    fld.rdb$field_scale AS fscale,
                    fld.rdb$field_precision AS fprecision,
                    rel.rdb$null_flag AS fnull,
                    fld.rdb$default_value AS fdefault_value,
                    (SELECT 1 FROM RDB$TRIGGERS
                        WHERE RDB$SYSTEM_FLAG = 0
                        AND UPPER(RDB$RELATION_NAME)=UPPER(\'' . $table->name . '\')
                        AND RDB$TRIGGER_TYPE = 1
                        AND RDB$TRIGGER_INACTIVE = 0
                        AND (UPPER(REPLACE(RDB$TRIGGER_SOURCE,\' \',\'\')) LIKE \'%NEW.\'||TRIM(rel.rdb$field_name)||\'=GEN_ID%\'
                            OR UPPER(REPLACE(RDB$TRIGGER_SOURCE,\' \',\'\')) LIKE \'%NEW.\'||TRIM(rel.rdb$field_name)||\'=NEXTVALUEFOR%\'))
                    AS fautoinc
                FROM
                    rdb$relation_fields rel
                    JOIN rdb$fields fld ON rel.rdb$field_source=fld.rdb$field_name
                WHERE
                    UPPER(rel.rdb$relation_name)=UPPER(\'' . $table->name . '\')
                ORDER BY
                    rel.rdb$field_position;';
        try {
            $columns = $this->db->createCommand($sql)->queryAll();
            if (!$columns) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
        $sql = 'SELECT
                    idx.rdb$field_name AS fname
                FROM
                    rdb$relation_constraints rc
                    JOIN rdb$index_segments idx ON idx.rdb$index_name=rc.rdb$index_name
                WHERE rc.rdb$constraint_type=\'PRIMARY KEY\'
					AND UPPER(rc.rdb$relation_name)=UPPER(\'' . $table->name . '\')';
        try {
            $pkeys = $this->db->createCommand($sql)->queryColumn();
        } catch (Exception $e) {
            return false;
        }
        $pkeys = array_map("rtrim", $pkeys);
        $pkeys = array_map("strtolower", $pkeys);
        foreach ($columns as $key => $column) {
            $column = array_map("strtolower", $column);
            $columns[$key]['fprimary'] = in_array(rtrim($column['fname']), $pkeys);
        }
        foreach ($columns as $column) {
            $c = $this->loadColumnSchema($column);
            if ($c->autoIncrement) {
                $this->_sequences[$table->fullName . '.' . $c->name] = $table->fullName . '.' . $c->name;
            }
            $table->columns[$c->name] = $c;
            if ($c->isPrimaryKey) {
                $table->primaryKey[] = $c->name;
            }
        }
        return (count($table->columns) > 0);
    }

    /**
     * @return \yii\db\ColumnSchema
     * @throws \yii\base\InvalidConfigException
     */
    protected function createColumnSchema()
    {
        return \Yii::createObject('\edgardmessias\db\firebird\ColumnSchema');
    }

    /**
     * Creates a table column.
     *
     * @param array $column column metadata
     * @return ColumnSchema normalized column metadata
     */
    protected function loadColumnSchema($column)
    {
        $c = $this->createColumnSchema();
        $c->name = strtolower(rtrim($column['fname']));
        $c->allowNull = $column['fnull'] !== '1';
        $c->isPrimaryKey = $column['fprimary'];
        $c->autoIncrement = $column['fautoinc'] === '1';

        $c->type = self::TYPE_STRING;

        $defaultValue = null;
        if (!empty($column['fdefault'])) {
            // remove whitespace, 'DEFAULT ' prefix and surrounding single quotes; all optional
            if (preg_match("/\s*(DEFAULT\s+){0,1}('(.*)'|(.*))\s*/i", $column['fdefault'], $parts)) {
                $defaultValue = array_pop($parts);
            }
            // handle escaped single quotes like in "funny''quoted''string"
            $defaultValue = str_replace('\'\'', '\'', $defaultValue);
        }
        if ($defaultValue === null) {
            $defaultValue = $column['fdefault_value'];
        }
        $dbType = "";
        $baseTypes = array(
            7   => 'SMALLINT',
            8   => 'INTEGER',
            16  => 'INT64',
            9   => 'QUAD',
            10  => 'FLOAT',
            11  => 'D_FLOAT',
            17  => 'BOOLEAN',
            27  => 'DOUBLE PRECISION',
            12  => 'DATE',
            13  => 'TIME',
            35  => 'TIMESTAMP',
            261 => 'BLOB',
            40  => 'CSTRING',
            45  => 'BLOB_ID',
        );
        $baseCharTypes = array(
            37 => 'VARCHAR',
            14 => 'CHAR',
        );
        if (array_key_exists((int) $column['fcodtype'], $baseTypes)) {
            $dbType = $baseTypes[(int) $column['fcodtype']];
        } elseif (array_key_exists((int) $column['fcodtype'], $baseCharTypes)) {
            $c->size = (int) $column['fcharlength'];
            $c->precision = $c->size;
            $dbType = $baseCharTypes[(int) $column['fcodtype']] . "($c->size)";
        }
        switch ((int) $column['fcodtype']) {
            case 7:
            case 8:
                switch ((int) $column['fcodsubtype']) {
                    case 1:
                        $c->precision = (int) $column['fprecision'];
                        $c->size = $c->precision;
                        $c->scale = abs((int) $column['fscale']);
                        $dbType = "NUMERIC({$c->precision},{$c->scale})";
                        break;
                    case 2:
                        $c->precision = (int) $column['fprecision'];
                        $c->size = $c->precision;
                        $c->scale = abs((int) $column['fscale']);
                        $dbType = "DECIMAL({$c->precision},{$c->scale})";
                        break;
                }
                break;
            case 16:
                switch ((int) $column['fcodsubtype']) {
                    case 1:
                        $c->precision = (int) $column['fprecision'];
                        $c->size = $c->precision;
                        $c->scale = abs((int) $column['fscale']);
                        $dbType = "NUMERIC({$c->precision},{$c->scale})";
                        break;
                    case 2:
                        $c->precision = (int) $column['fprecision'];
                        $c->size = $c->precision;
                        $c->scale = abs((int) $column['fscale']);
                        $dbType = "DECIMAL({$c->precision},{$c->scale})";
                        break;
                    default :
                        $dbType = 'BIGINT';
                        break;
                }
                break;
            case 261:
                switch ((int) $column['fcodsubtype']) {
                    case 1:
                        $dbType = 'BLOB SUB_TYPE TEXT';
                        $c->size = null;
                        break;
                }
                break;
        }

        $c->dbType = strtolower($dbType);

        $c->type = self::TYPE_STRING;
        if (preg_match('/^([\w\ ]+)(?:\(([^\)]+)\))?/', $c->dbType, $matches)) {
            $type = strtolower($matches[1]);
            if (isset($this->typeMap[$type])) {
                $c->type = $this->typeMap[$type];
            }
        }


        $c->phpType = $this->getColumnPhpType($c);

        $c->defaultValue = null;
        if ($defaultValue !== null) {
            if (in_array($c->type, [self::TYPE_DATE, self::TYPE_DATETIME, self::TYPE_TIME, self::TYPE_TIMESTAMP]) 
                    && preg_match('/(CURRENT_|NOW|NULL|TODAY|TOMORROW|YESTERDAY)/i', $defaultValue)) {
                $c->defaultValue = new \yii\db\Expression(trim($defaultValue));
            } else {
                $c->defaultValue = $c->phpTypecast($defaultValue);
            }
        }

        return $c;
    }

    /**
     * Collects the foreign key column details for the given table.
     *
     * @param TableSchema $table the table metadata
     */
    protected function findConstraints($table)
    {
        // Zoggo - Converted sql to use join syntax
        $sql = 'SELECT
                    a.rdb$constraint_name as fconstraint,
                    c.rdb$relation_name AS ftable,
                    d.rdb$field_name AS pfield,
                    e.rdb$field_name AS ffield
                FROM
                    rdb$ref_constraints b
                    JOIN rdb$relation_constraints a ON a.rdb$constraint_name=b.rdb$constraint_name
                    JOIN rdb$relation_constraints c ON b.rdb$const_name_uq=c.rdb$constraint_name
                    JOIN rdb$index_segments d ON c.rdb$index_name=d.rdb$index_name
                    JOIN rdb$index_segments e ON a.rdb$index_name=e.rdb$index_name AND e.rdb$field_position = d.rdb$field_position
                WHERE
                    a.rdb$constraint_type=\'FOREIGN KEY\' AND
                    UPPER(a.rdb$relation_name)=UPPER(\'' . $table->name . '\') ';
        try {
            $fkeys = $this->db->createCommand($sql)->queryAll();
        } catch (Exception $e) {
            return false;
        }

        $constraints = [];
        foreach ($fkeys as $fkey) {
            // Zoggo - Added strtolower here to guarantee that values are
            // returned lower case. Otherwise gii generates wrong code.
            $fkey = array_map("rtrim", $fkey);
            $fkey = array_map("strtolower", $fkey);

            if (!isset($constraints[$fkey['fconstraint']])) {
                $constraints[$fkey['fconstraint']] = [
                    $fkey['ftable']
                ];
            }
            $constraints[$fkey['fconstraint']][$fkey['ffield']] = $fkey['pfield'];
        }
        $table->foreignKeys = array_values($constraints);
    }

    protected function findTableNames($schema = '')
    {
        $sql = 'SELECT
                    rdb$relation_name
                FROM
                    rdb$relations
                WHERE
                    (rdb$view_blr is null) AND
                    (rdb$system_flag is null OR rdb$system_flag=0)';
        try {
            $tables = $this->db->createCommand($sql)->queryColumn();
        } catch (Exception $e) {
            return false;
        }
        foreach ($tables as $key => $table) {
            $tables[$key] = strtolower(rtrim($table));
        }
        return $tables;
    }

    /**
     * Sets the isolation level of the current transaction.
     * @param string $level The transaction isolation level to use for this transaction.
     * This can be one of [[Transaction::READ_UNCOMMITTED]], [[Transaction::READ_COMMITTED]], [[Transaction::REPEATABLE_READ]]
     * and [[Transaction::SERIALIZABLE]] but also a string containing DBMS specific syntax to be used
     * after `SET TRANSACTION ISOLATION LEVEL`.
     * @see http://en.wikipedia.org/wiki/Isolation_%28database_systems%29#Isolation_levels
     */
    public function setTransactionIsolationLevel($level)
    {
        if ($level == \yii\db\Transaction::READ_UNCOMMITTED) {
            parent::setTransactionIsolationLevel('READ COMMITTED RECORD_VERSION');
        } elseif ($level == \yii\db\Transaction::REPEATABLE_READ) {
            parent::setTransactionIsolationLevel('SNAPSHOT');
        } elseif ($level == \yii\db\Transaction::SERIALIZABLE) {
            parent::setTransactionIsolationLevel('SNAPSHOT TABLE STABILITY');
        } else {
            parent::setTransactionIsolationLevel($level);
        }
    }

    /**
     * Executes the INSERT command, returning primary key values.
     * @param string $table the table that new rows will be inserted into.
     * @param array $columns the column data (name => value) to be inserted into the table.
     * @return array primary key values or false if the command fails
     * @since 2.0.4
     */
    public function insert($table, $columns)
    {

        $tableSchema = $this->getTableSchema($table);

        $command = $this->db->createCommand()->insert($table, $columns);

        if ($tableSchema->sequenceName !== null) {
            $this->_lastInsertID = $command->queryScalar();
            if ($this->_lastInsertID === false) {
                return false;
            }
        } else {
            if (!$command->execute()) {
                return false;
            }
        }
        $result = [];
        foreach ($tableSchema->primaryKey as $name) {
            if ($tableSchema->columns[$name]->autoIncrement) {
                $result[$name] = $this->getLastInsertID($tableSchema->sequenceName);
                break;
            } else {
                $result[$name] = isset($columns[$name]) ? $columns[$name] : $tableSchema->columns[$name]->defaultValue;
            }
        }
        return $result;
    }

    /**
     * Returns the ID of the last inserted row or sequence value.
     * @param string $sequenceName name of the sequence object (required by some DBMS)
     * @return string the row ID of the last row inserted, or the last value retrieved from the sequence object
     * @throws InvalidCallException if the DB connection is not active
     * @see http://www.php.net/manual/en/function.PDO-lastInsertId.php
     */
    public function getLastInsertID($sequenceName = '')
    {
        if (!$this->db->isActive) {
            throw new InvalidCallException('DB Connection is not active.');
        }

        if ($this->_lastInsertID !== false) {
            return $this->_lastInsertID;
        }
        return null;
    }
}

<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace edgardmessias\db\firebird;

use yii\base\InvalidCallException;
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
 * @author Edgard Lorraine Messias <edgardmessias@gmail.com>
 * @since 2.0
 */
class Schema extends \yii\db\Schema
{

    private $_lastInsertID = null;

    /**
     * @var array map of DB errors and corresponding exceptions
     * If left part is found in DB error message exception class from the right part is used.
     */
    public $exceptionMap = [
        'SQLSTATE[23'                                               => 'yii\db\IntegrityException',
        'SQLSTATE[HY000]: General error: -803 violation of PRIMARY' => 'yii\db\IntegrityException',
    ];
    public $reservedWords = [
        'ORDER',
        'POSITION',
        'TIME',
        'VALUE',
    ];

    /**
     * @var array mapping from physical column types (keys) to abstract column types (values)
     */
    public $typeMap = [
        'bigint'             => self::TYPE_BIGINT,
        'char'               => self::TYPE_CHAR,
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

    /**
     * @inheritdoc
     */
    public function createColumnSchemaBuilder($type, $length = null)
    {
        return new ColumnSchemaBuilder($type, $length);
    }

    public function quoteSimpleTableName($name)
    {
        if ($this->db->tablePrefix !== '') {
            return $name;
        }

        $word = strtoupper(str_replace('%', '', $name));
        if (in_array($word, $this->reservedWords)) {
            return strpos($name, '"') !== false ? $name : '"' . $name . '"';
        }

        return $name;
    }

    public function quoteSimpleColumnName($name)
    {
        if (in_array(strtoupper($name), $this->reservedWords)) {
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
                    rel.rdb$description AS fcomment,
                    fld.rdb$default_value AS fdefault_value,
                    (SELECT RDB$TRIGGER_SOURCE FROM RDB$TRIGGERS
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
            if (empty($columns)) {
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
            if ($table->sequenceName === null && $c->autoIncrement) {
                $matches = [];
                if (preg_match("/NEW.{$c->name}\s*=\s*GEN_ID\((\w+)/i", $column['fautoinc'], $matches)) {
                    $table->sequenceName = $matches[1];
                } elseif (preg_match("/NEW.{$c->name}\s*=\s*NEXT\s+VALUE\s+FOR\s+(\w+)/i", $column['fautoinc'], $matches)) {
                    $table->sequenceName = $matches[1];
                }
            }
            $table->columns[$c->name] = $c;
            if ($c->isPrimaryKey) {
                $table->primaryKey[] = $c->name;
            }
        }
        return (count($table->columns) > 0);
    }

    /**
     * @return ColumnSchema
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
        $c->autoIncrement = (boolean) $column['fautoinc'];
        $c->comment = $column['fcomment'] === null ? '' : $column['fcomment'];

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
        $baseTypes = [
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
        ];
        $baseCharTypes = [
            37 => 'VARCHAR',
            14 => 'CHAR',
        ];
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
                    default:
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
                    (rdb$system_flag is null OR rdb$system_flag=0)';
        try {
            $tables = $this->db->createCommand($sql)->queryColumn();
        } catch (Exception $e) {
            return false;
        }

        $tables = array_map('rtrim', $tables);
        $tables = array_map('strtolower', $tables);

        return $tables;
    }

    /**
     * Returns all unique indexes for the given table.
     * Each array element is of the following structure:
     *
     * ~~~
     * [
     *  'IndexName1' => ['col1' [, ...]],
     *  'IndexName2' => ['col2' [, ...]],
     * ]
     * ~~~
     *
     * @param TableSchema $table the table metadata
     * @return array all unique indexes for the given table.
     * @since 2.0.4
     */
    public function findUniqueIndexes($table)
    {
        $query = '
SELECT id.RDB$INDEX_NAME as index_name, ids.RDB$FIELD_NAME as column_name
FROM RDB$INDICES id
INNER JOIN RDB$INDEX_SEGMENTS ids ON ids.RDB$INDEX_NAME = id.RDB$INDEX_NAME
WHERE id.RDB$UNIQUE_FLAG = 1
AND   id.RDB$SYSTEM_FLAG = 0
AND UPPER(id.RDB$RELATION_NAME) = UPPER(\'' . $table->name . '\')
ORDER BY id.RDB$RELATION_NAME, id.RDB$INDEX_NAME, ids.RDB$FIELD_POSITION';
        $result = [];
        $command = $this->db->createCommand($query);
        foreach ($command->queryAll() as $row) {
            $result[strtolower(rtrim($row['index_name']))][] = strtolower(rtrim($row['column_name']));
        }
        return $result;
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
     * @inheritdoc
     */
    public function insert($table, $columns)
    {
        $this->_lastInsertID = false;
        $params = [];
        $sql = $this->db->getQueryBuilder()->insert($table, $columns, $params);
        $returnColumns = $this->getTableSchema($table)->primaryKey;
        if (!empty($returnColumns)) {
            $returning = [];
            foreach ((array) $returnColumns as $name) {
                $returning[] = $this->quoteColumnName($name);
            }
            $sql .= ' RETURNING ' . implode(', ', $returning);
        }

        $command = $this->db->createCommand($sql, $params);
        $command->prepare(false);
        $result = $command->queryOne();

        if (!$command->pdoStatement->rowCount()) {
            return false;
        } else {
            if (!empty($returnColumns)) {
                foreach ((array) $returnColumns as $name) {
                    if ($this->getTableSchema($table)->getColumn($name)->autoIncrement) {
                        $this->_lastInsertID = $result[$name];
                        break;
                    }
                }
            }
            return $result;
        }
    }

    /**
     * @inheritdoc
     */
    public function getLastInsertID($sequenceName = '')
    {
        if (!$this->db->isActive) {
            throw new InvalidCallException('DB Connection is not active.');
        }
        
        if ($sequenceName !== '') {
            return $this->db->createCommand('SELECT GEN_ID(' . $this->db->quoteTableName($sequenceName) .  ', 0 ) FROM RDB$DATABASE;')->queryScalar();
        }

        if ($this->_lastInsertID !== false) {
            return $this->_lastInsertID;
        }
        return null;
    }
}

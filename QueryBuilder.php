<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace edgardmessias\db\firebird;

/**
 *
 * @author Edgard Lorraine Messias <edgardmessias@gmail.com>
 * @since 2.0
 */
class QueryBuilder extends \yii\db\QueryBuilder
{

    /**
     * @var array mapping from abstract column types (keys) to physical column types (values).
     */
    public $typeMap = [
        Schema::TYPE_PK        => 'integer NOT NULL PRIMARY KEY',
        Schema::TYPE_BIGPK     => 'bigint NOT NULL PRIMARY KEY',
        Schema::TYPE_STRING    => 'varchar(255)',
        Schema::TYPE_TEXT      => 'blob sub_type text',
        Schema::TYPE_SMALLINT  => 'smallint',
        Schema::TYPE_INTEGER   => 'integer',
        Schema::TYPE_BIGINT    => 'bigint',
        Schema::TYPE_FLOAT     => 'float',
        Schema::TYPE_DOUBLE    => 'double precision',
        Schema::TYPE_DECIMAL   => 'numeric(10,0)',
        Schema::TYPE_DATETIME  => 'timestamp',
        Schema::TYPE_TIMESTAMP => 'timestamp',
        Schema::TYPE_TIME      => 'time',
        Schema::TYPE_DATE      => 'date',
        Schema::TYPE_BINARY    => 'blob',
        Schema::TYPE_BOOLEAN   => 'smallint',
        Schema::TYPE_MONEY     => 'numeric(18,4)',
    ];

    public function buildSelect($columns, &$params, $distinct = false, $selectOption = null)
    {
        if (is_array($columns)) {
            foreach ($columns as $i => $column) {
                if (!is_string($column)) {
                    continue;
                }
                $matches = [];
                if (preg_match("/^(COUNT|SUM|AVG|MIN|MAX)\((\w+|\*)\)$/i", $column, $matches)) {
                    $function = $matches[1];
                    $alias = $matches[2] != '*' ? $matches[2] : 'ALL';

                    $columns[$i] = "{$column} AS {$function}_{$alias}";
                }
            }
        }

        return parent::buildSelect($columns, $params, $distinct, $selectOption);
    }

    protected function buildCompositeInCondition($operator, $columns, $values, &$params)
    {
        $quotedColumns = [];
        foreach ($columns as $i => $column) {
            $quotedColumns[$i] = strpos($column, '(') === false ? $this->db->quoteColumnName($column) : $column;
        }
        $vss = [];
        foreach ($values as $value) {
            $vs = [];
            foreach ($columns as $i => $column) {
                if (isset($value[$column])) {
                    $phName = self::PARAM_PREFIX . count($params);
                    $params[$phName] = $value[$column];
                    $vs[] = $quotedColumns[$i] . ($operator === 'IN' ? ' = ' : ' != ') . $phName;
                } else {
                    $vs[] = $quotedColumns[$i] . ($operator === 'IN' ? ' IS' : ' IS NOT') . ' NULL';
                }
            }
            $vss[] = '(' . implode($operator === 'IN' ? ' AND ' : ' OR ', $vs) . ')';
        }
        return '(' . implode($operator === 'IN' ? ' OR ' : ' AND ', $vss) . ')';
    }

    public function buildOrderByAndLimit($sql, $orderBy, $limit, $offset)
    {

        $orderBy = $this->buildOrderBy($orderBy);
        if ($orderBy !== '') {
            $sql .= $this->separator . $orderBy;
        }

        $limit = $limit !== null ? intval($limit) : -1;
        $offset = $offset !== null ? intval($offset) : -1;
        // If ignoring both params then do nothing
        if ($offset < 0 && $limit < 0) {
            return $sql;
        }
        // If we are ignoring limit then return full result set starting
        // from $offset. In Firebird this can only be done with SKIP
        if ($offset >= 0 && $limit < 0) {
            $count = 1; //Only do it once
            $sql = preg_replace('/^SELECT /i', 'SELECT SKIP ' . (int) $offset . ' ', $sql, $count);
            return $sql;
        }
        // If we are ignoring $offset then return $limit rows.
        // ie, return the first $limit rows in the set.
        if ($offset < 0 && $limit >= 0) {
            $rows = $limit;
            $sql .= ' ROWS ' . (int) $rows;
            return $sql;
        }
        // Otherwise apply the params and return the amended sql.
        if ($offset >= 0 && $limit >= 0) {
            // calculate $rows for ROWS...
            $rows = $offset + 1;
            $sql .= ' ROWS ' . (int) $rows;
            // calculate $to for TO...
            $to = $offset + $limit;
            $sql .= ' TO ' . (int) $to;
            return $sql;
        }
        // If we have fallen through the cracks then just pass
        // the sql back.
        return $sql;
    }

    public function insert($table, $columns, &$params)
    {
        $schema = $this->db->getSchema();
        $autoIncrementColumn = null;
        if (($tableSchema = $schema->getTableSchema($table)) !== null) {
            $columnSchemas = $tableSchema->columns;
            if ($tableSchema->sequenceName !== null) {
                $autoIncrementColumn = $tableSchema->sequenceName;
            }
        } else {
            $columnSchemas = [];
        }

        foreach ($columns as $name => $value) {
            if (in_array($columnSchemas[$name]->type, [Schema::TYPE_TEXT, Schema::TYPE_BINARY])) {
                $columns[$name] = [$value, 'blob'];
            }
        }
        $sql = parent::insert($table, $columns, $params);

        if ($autoIncrementColumn !== null) {
            $sql .= ' RETURNING ' . $autoIncrementColumn;
        }

        return $sql;
    }

    public function update($table, $columns, $condition, &$params)
    {
        $schema = $this->db->getSchema();
        if (($tableSchema = $schema->getTableSchema($table)) !== null) {
            $columnSchemas = $tableSchema->columns;
        } else {
            $columnSchemas = [];
        }
        foreach ($columns as $name => $value) {
            if (in_array($columnSchemas[$name]->type, [Schema::TYPE_TEXT, Schema::TYPE_BINARY])) {
                $columns[$name] = [$value, 'blob'];
            }
        }
        return parent::update($table, $columns, $condition, $params);
    }

    /**
     * Generates a batch INSERT SQL statement.
     * For example,
     *
     * ~~~
     * $sql = $queryBuilder->batchInsert('user', ['name', 'age'], [
     *     ['Tom', 30],
     *     ['Jane', 20],
     *     ['Linda', 25],
     * ]);
     * ~~~
     *
     * Note that the values in each row must match the corresponding column names.
     *
     * The method will properly escape the column names, and quote the values to be inserted.
     *
     * @param string $table the table that new rows will be inserted into.
     * @param array $columns the column names
     * @param array $rows the rows to be batch inserted into the table
     * @return string the batch INSERT SQL statement
     */
    public function batchInsert($table, $columns, $rows)
    {
        $schema = $this->db->getSchema();
        if (($tableSchema = $schema->getTableSchema($table)) !== null) {
            $columnSchemas = $tableSchema->columns;
        } else {
            $columnSchemas = [];
        }

        $values = [];
        foreach ($rows as $row) {
            $vs = [];
            foreach ($row as $i => $value) {
                if (isset($columns[$i], $columnSchemas[$columns[$i]]) && !is_array($value)) {
                    $value = $columnSchemas[$columns[$i]]->dbTypecast($value);
                }
                if (is_string($value)) {
                    $value = $schema->quoteValue($value);
                } elseif ($value === false) {
                    $value = 0;
                } elseif ($value === null) {
                    $value = 'NULL';
                }
                $vs[] = $value;
            }
            $values[] = 'INSERT INTO ' . $schema->quoteTableName($table)
                    . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $vs) . ');';
        }

        foreach ($columns as $i => $name) {
            $columns[$i] = $schema->quoteColumnName($name);
        }

        return 'EXECUTE block AS BEGIN ' . implode(' ', $values) . ' END;';
    }
    
    /**
     * @inheritdoc
     */
    public function renameTable($oldName, $newName)
    {
        throw new \yii\base\NotSupportedException($this->db->getDriverName() . ' does not support rename table.');
    }
}

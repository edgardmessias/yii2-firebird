<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace edgardmessias\db\firebird;

use yii\base\InvalidParamException;
use yii\db\Expression;
use yii\db\Query;

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

    /**
     * Generates a SELECT SQL statement from a [[Query]] object.
     * @param Query $query the [[Query]] object from which the SQL statement will be generated.
     * @param array $params the parameters to be bound to the generated SQL statement. These parameters will
     * be included in the result with the additional parameters generated during the query building process.
     * @return array the generated SQL statement (the first array element) and the corresponding
     * parameters to be bound to the SQL statement (the second array element). The parameters returned
     * include those provided in `$params`.
     */
    public function build($query, $params = [])
    {
        $query = $query->prepare($this);

        $params = empty($params) ? $query->params : array_merge($params, $query->params);

        $clauses = [
            $this->buildSelect($query->select, $params, $query->distinct, $query->selectOption),
            $this->buildFrom($query->from, $params),
            $this->buildJoin($query->join, $params),
            $this->buildWhere($query->where, $params),
            $this->buildGroupBy($query->groupBy),
            $this->buildHaving($query->having, $params),
        ];

        $sql = implode($this->separator, array_filter($clauses));
        $sql = $this->buildOrderByAndLimit($sql, $query->orderBy, $query->limit, $query->offset);

        if (!empty($query->orderBy)) {
            foreach ($query->orderBy as $expression) {
                if ($expression instanceof Expression) {
                    $params = array_merge($params, $expression->params);
                }
            }
        }
        if (!empty($query->groupBy)) {
            foreach ($query->groupBy as $expression) {
                if ($expression instanceof Expression) {
                    $params = array_merge($params, $expression->params);
                }
            }
        }

        $union = $this->buildUnion($query->union, $params);
        if ($union !== '') {
            $sql = "$sql{$this->separator}$union";
        }

        return [$sql, $params];
    }

    /**
     * @inheritdoc
     */
    public function buildSelect($columns, &$params, $distinct = false, $selectOption = null)
    {
        if (is_array($columns)) {
            foreach ($columns as $i => $column) {
                if (!is_string($column)) {
                    continue;
                }
                $matches = [];
                if (preg_match("/^(COUNT|SUM|AVG|MIN|MAX)\([\{\[]{0,2}(\w+|\*)[\}\]]{0,2}\)$/i", $column, $matches)) {
                    $function = $matches[1];
                    $alias = $matches[2] != '*' ? $matches[2] : 'ALL';

                    $columns[$i] = "{$column} AS {$function}_{$alias}";
                }
            }
        }

        return parent::buildSelect($columns, $params, $distinct, $selectOption);
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
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
            $count = 1; //Only do it once
            $sql = preg_replace('/^SELECT /i', 'SELECT FIRST ' . (int) $limit . ' ', $sql, $count);
            return $sql;
        }
        // Otherwise apply the params and return the amended sql.
        if ($offset >= 0 && $limit >= 0) {
            $count = 1; //Only do it once
            $sql = preg_replace('/^SELECT /i', 'SELECT FIRST ' . (int) $limit . ' SKIP ' . (int) $offset, $sql, $count);
            return $sql;
        }
        // If we have fallen through the cracks then just pass
        // the sql back.
        return $sql;
    }

    /**
     * @param array $unions
     * @param array $params the binding parameters to be populated
     * @return string the UNION clause built from [[Query::$union]].
     */
    public function buildUnion($unions, &$params)
    {
        if (empty($unions)) {
            return '';
        }

        $result = '';

        foreach ($unions as $i => $union) {
            $query = $union['query'];
            if ($query instanceof Query) {
                list($unions[$i]['query'], $params) = $this->build($query, $params);
            }

            $result .= 'UNION ' . ($union['all'] ? 'ALL ' : '') . ' ' . $unions[$i]['query'] . ' ';
        }

        return trim($result);
    }

    /**
     *
     * @param Expression $value
     * @return Expression
     */
    protected function convertExpression($value)
    {
        if (!($value instanceof Expression)) {
            return $value;
        }
        
        $expressionMap = [
            "strftime('%Y')" => "EXTRACT(YEAR FROM TIMESTAMP 'now')"
        ];
        
        if (isset($expressionMap[$value->expression])) {
            return new Expression($expressionMap[$value->expression]);
        }
        return $value;
    }

    /**
     * @inheritdoc
     */
    public function insert($table, $columns, &$params)
    {
        $schema = $this->db->getSchema();
        if (($tableSchema = $schema->getTableSchema($table)) !== null) {
            $columnSchemas = $tableSchema->columns;
        } else {
            $columnSchemas = [];
        }

        foreach ($columns as $name => $value) {
            if ($value instanceof Expression) {
                $columns[$name] = $this->convertExpression($value);
            } elseif (isset($columnSchemas[$name]) && in_array($columnSchemas[$name]->type, [Schema::TYPE_TEXT, Schema::TYPE_BINARY])) {
                $columns[$name] = [$value, 'blob'];
            }
        }

        return parent::insert($table, $columns, $params);
    }

    /**
     * @inheritdoc
     */
    public function update($table, $columns, $condition, &$params)
    {
        $schema = $this->db->getSchema();
        if (($tableSchema = $schema->getTableSchema($table)) !== null) {
            $columnSchemas = $tableSchema->columns;
        } else {
            $columnSchemas = [];
        }
        foreach ($columns as $name => $value) {
            if ($value instanceof Expression) {
                $columns[$name] = $this->convertExpression($value);
            } elseif (isset($columnSchemas[$name]) && in_array($columnSchemas[$name]->type, [Schema::TYPE_TEXT, Schema::TYPE_BINARY])) {
                $columns[$name] = [$value, 'blob'];
            }
        }
        return parent::update($table, $columns, $condition, $params);
    }

    /**
     * @inheritdoc
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
    
    /**
     * @inheritdoc
     */
    public function truncateTable($table)
    {
        return "DELETE FROM " . $this->db->quoteTableName($table);
    }
    
    /**
     * @inheritdoc
     */
    public function dropColumn($table, $column)
    {
        return "ALTER TABLE " . $this->db->quoteTableName($table)
            . " DROP " . $this->db->quoteColumnName($column);
    }
    
    /**
     * @inheritdoc
     */
    public function renameColumn($table, $oldName, $newName)
    {
        return "ALTER TABLE " . $this->db->quoteTableName($table)
            . " ALTER " . $this->db->quoteColumnName($oldName)
            . " TO " . $this->db->quoteColumnName($newName);
    }
    
    /**
     * @inheritdoc
     */
    public function alterColumn($table, $column, $type)
    {
        $schema = $this->db->getSchema();
        $tableSchema = $schema->getTableSchema($table);
        $columnSchema = $tableSchema->getColumn($column);
        
        $allowNullNewType = !preg_match("/not +null/i", $type);
        
        $type = preg_replace("/ +(not)? *null/i", "", $type);
        
        $hasType = false;
        
        $matches = [];
        if (isset($this->typeMap[$type])) {
            $hasType = true;
        } elseif (preg_match('/^(\w+)[\( ]/', $type, $matches)) {
            if (isset($this->typeMap[$matches[1]])) {
                $hasType = true;
            }
        }
        
        $baseSql    = 'ALTER TABLE ' . $this->db->quoteTableName($table)
        . ' ALTER '. $this->db->quoteColumnName($column)
        . (($hasType)? ' TYPE ': ' ') .  $this->getColumnType($type);
        
        if ($columnSchema->allowNull == $allowNullNewType) {
            return $baseSql;
        } else {
            $sql = 'EXECUTE BLOCK AS BEGIN'
                . ' EXECUTE STATEMENT ' . $this->db->quoteValue($baseSql) . ';'
                . ' UPDATE RDB$RELATION_FIELDS SET RDB$NULL_FLAG = ' . ($allowNullNewType ? 'NULL' : '1')
                . ' WHERE UPPER(RDB$FIELD_NAME) = UPPER(\'' . $column . '\') AND UPPER(RDB$RELATION_NAME) = UPPER(\'' . $table . '\');';
            /**
             * In any case (whichever option you choose), make sure that the column doesn't have any NULLs.
             * Firebird will not check it for you. Later when you backup the database, everything is fine,
             * but restore will fail as the NOT NULL column has NULLs in it. To be safe, each time you change from NULL to NOT NULL.
             */
            if (!$allowNullNewType) {
                $sql .= ' UPDATE ' . $this->db->quoteTableName($table) . ' SET ' . $this->db->quoteColumnName($column) . ' = 0'
                    . ' WHERE ' . $this->db->quoteColumnName($column) . ' IS NULL;';
            }
            $sql .= ' END';
            return $sql;
        }
    }
    
    /**
     * @inheritdoc
     */
    public function dropIndex($name, $table)
    {
        return 'DROP INDEX ' . $this->db->quoteTableName($name);
    }
    
    /**
     * @inheritdoc
     */
    public function resetSequence($table, $value = null)
    {
        $tableSchema = $this->db->getTableSchema($table);
        if ($tableSchema === null) {
            throw new InvalidParamException("Table not found: $table");
        }
        if ($tableSchema->sequenceName === null) {
            throw new InvalidParamException("There is not sequence associated with table '$table'.");
        }

        if ($value !== null) {
            $value = (int) $value;
        } else {
            // use master connection to get the biggest PK value
            $value = $this->db->useMaster(function (Connection $db) use ($tableSchema) {
                $key = reset($tableSchema->primaryKey);
                return $db->createCommand("SELECT MAX({$this->db->quoteColumnName($key)}) FROM {$this->db->quoteTableName($tableSchema->name)}")->queryScalar();
            }) + 1;
        }

        return "ALTER SEQUENCE {$this->db->quoteColumnName($tableSchema->sequenceName)} RESTART WITH $value";
    }
    
    /**
     * @inheritdoc
     */
    public function createTable($table, $columns, $options = null)
    {
        $sql = parent::createTable($table, $columns, $options);
        
        foreach ($columns as $name => $type) {
            if (!is_string($name)) {
                continue;
            }
            
            if (strpos($type, Schema::TYPE_PK) === 0 || strpos($type, Schema::TYPE_BIGPK) === 0) {
                $sqlTrigger = <<<SQLTRIGGER
CREATE TRIGGER tr_{$table}_{$name} FOR {$this->db->quoteTableName($table)}
ACTIVE BEFORE INSERT POSITION 0
AS
BEGIN
    if (NEW.{$this->db->quoteColumnName($name)} is NULL) then NEW.{$this->db->quoteColumnName($name)} = NEXT VALUE FOR seq_{$table}_{$name};
END
SQLTRIGGER;
                
                $sqlBlock = <<<SQL
EXECUTE block AS
BEGIN
    EXECUTE STATEMENT {$this->db->quoteValue($sql)};
    EXECUTE STATEMENT {$this->db->quoteValue("CREATE SEQUENCE seq_{$table}_{$name}")};
    EXECUTE STATEMENT {$this->db->quoteValue($sqlTrigger)};
END;
SQL;

                return $sqlBlock;
            }
        }
        
        return $sql;
    }
    
    /**
     * @inheritdoc
     */
    public function dropTable($table)
    {
        $sql = parent::dropTable($table);
        
        $tableSchema = $this->db->getTableSchema($table);
        if ($tableSchema === null || $tableSchema->sequenceName === null) {
            return $sql;
        }
        
        $sqlBlock = <<<SQL
EXECUTE block AS
BEGIN
    EXECUTE STATEMENT {$this->db->quoteValue($sql)};
    EXECUTE STATEMENT {$this->db->quoteValue("DROP SEQUENCE {$tableSchema->sequenceName}")};
END;
SQL;
                return $sqlBlock;
        
    }
}

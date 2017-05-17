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
class Command extends \yii\db\Command
{
    
    /**
     * Used to prevent bindParam change the user value
     * @var array
     */
    private $_boolParams = [];

    /**
     * Binds a parameter to the SQL statement to be executed.
     * @param string|integer $name parameter identifier. For a prepared statement
     * using named placeholders, this will be a parameter name of
     * the form `:name`. For a prepared statement using question mark
     * placeholders, this will be the 1-indexed position of the parameter.
     * @param mixed $value Name of the PHP variable to bind to the SQL statement parameter
     * @param integer $dataType SQL data type of the parameter. If null, the type is determined by the PHP type of the value.
     * @param integer $length length of the data type
     * @param mixed $driverOptions the driver-specific options
     * @return static the current command being executed
     * @see http://www.php.net/manual/en/function.PDOStatement-bindParam.php
     */
    public function bindParam($name, &$value, $dataType = null, $length = null, $driverOptions = null)
    {
        if ($dataType === null) {
            $dataType = $this->db->getSchema()->getPdoType($value);
        }
        
        /**
         * PDO_FIREBIRD accept only 'true' and 'false' strings for booleans
         */
        if ($dataType == \PDO::PARAM_BOOL && !in_array($value, ['true', 'false'], true)) {
            $boolValue = boolval($value) ? 'true' : 'false';
            $this->_boolParams[] = [&$value, &$boolValue];

            return parent::bindParam($name, $boolValue, $dataType, $length, $driverOptions);
        }
        return parent::bindParam($name, $value, $dataType, $length, $driverOptions);
    }

    /**
     * Specifies the SQL statement to be executed.
     * The previous SQL execution (if any) will be cancelled, and [[params]] will be cleared as well.
     * @param string $sql the SQL statement to be set.
     * @return static this command instance
     */
    public function setSql($sql)
    {
        $matches = null;
        if (preg_match("/^\s*DROP TABLE IF EXISTS (['\"]?([^\s\;]+)['\"]?);?\s*$/i", $sql, $matches)) {
            if ($this->db->getSchema()->getTableSchema($matches[2]) !== null) {
                $sql = $this->db->getQueryBuilder()->dropTable($matches[2]);
            } else {
                $sql = 'select 1 from RDB$DATABASE;'; //Prevent Drop Table
            }
        }
        
        return parent::setSql($sql);
    }

    /**
     * Binds a value to a parameter.
     * @param string|integer $name Parameter identifier. For a prepared statement
     * using named placeholders, this will be a parameter name of
     * the form `:name`. For a prepared statement using question mark
     * placeholders, this will be the 1-indexed position of the parameter.
     * @param mixed $value The value to bind to the parameter
     * @param integer $dataType SQL data type of the parameter. If null, the type is determined by the PHP type of the value.
     * @return static the current command being executed
     * @see http://www.php.net/manual/en/function.PDOStatement-bindValue.php
     */
    public function bindValue($name, $value, $dataType = null)
    {
        if ($dataType === null) {
            $dataType = $this->db->getSchema()->getPdoType($value);
        }
        if ($dataType == \PDO::PARAM_BOOL && !in_array($value, ['true', 'false'], true)) {
            $value = boolval($value) ? 'true' : 'false';
        }

        return parent::bindValue($name, $value, $dataType);
    }
    
    /**
     * Executes the SQL statement.
     * This method should only be used for executing non-query SQL statement, such as `INSERT`, `DELETE`, `UPDATE` SQLs.
     * No result set will be returned.
     * @return int number of rows affected by the execution.
     * @throws Exception execution failed
     */
    public function execute()
    {
        /**
         * Rebind boolean parameters
         */
        foreach ($this->_boolParams as &$param) {
            if (!in_array($param[0], ['true', 'false'], true)) {
                $param[1] = boolval($param[0]) ? 'true' : 'false';
            } else {
                $param[1] = clone $param[0];
            }
        }
        
        return parent::execute();
    }
}

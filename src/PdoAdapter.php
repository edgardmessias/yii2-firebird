<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace edgardmessias\db\firebird;

use PDO;

/**
 * Description of PdoAdapter
 *
 * @author Edgard Lorraine Messias <edgard.cpd@romera.com.br>
 */
class PdoAdapter extends PDO
{

    private $_inTransaction = false;

    /**
     * Do some basic setup for Firebird.
     * o Force use of exceptions on error.
     * o Force all metadata to lower case.
     *   Yii will behave in unpredicatable ways if
     *   metadata is not lowercase.
     * o Ensure that table names are not prefixed to
     *    fieldnames when returning metadata.
     * Finally call parent constructor.
     *
     */
    public function __construct($dsn, $username, $password, $driver_options = [])
    {
        // Windows OS paths with backslashes should be changed
        $dsn = str_replace('\\', '/', $dsn);
        // apply error mode
        $driver_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        // lower case column names in results are necessary for Yii ActiveRecord proper functioning
        $driver_options[PDO::ATTR_CASE] = PDO::CASE_LOWER;
        // ensure we only receive fieldname not tablename.fieldname.
        $driver_options[PDO::ATTR_FETCH_TABLE_NAMES] = false;
        parent::__construct($dsn, $username, $password, $driver_options);
    }

    /**
     * Initiates a transaction
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function beginTransaction($isolationLevel = null)
    {
        $this->setAttribute(PDO::ATTR_AUTOCOMMIT, false);

        if ($isolationLevel === false) {
            $this->_inTransaction = true;
            return true;
        }

        if ($isolationLevel === null) {
            $r = $this->exec('SET TRANSACTION');
            $success = ($r !== false);
            if ($success) {
                $this->_inTransaction = true;
            }
            return ($success);
        }

        $r = $this->exec("SET TRANSACTION ISOLATION LEVEL $isolationLevel");
        $success = ($r !== false);
        if ($success) {
            $this->_inTransaction = true;
        }
        return ($success);
    }

    /**
     * Commits a transaction
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function commit()
    {
        $r = $this->exec('COMMIT');
        $this->setAttribute(PDO::ATTR_AUTOCOMMIT, true);
        $success = ($r !== false);
        if ($success) {
            $this->_inTransaction = false;
        }
        return ($success);
    }

    /**
     * Rolls back a transaction
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function rollBack()
    {
        $r = $this->exec('ROLLBACK');
        $this->setAttribute(PDO::ATTR_AUTOCOMMIT, true);
        $success = ($r !== false);
        if ($success) {
            $this->_inTransaction = false;
        }
        return ($success);
    }

    /**
     * Checks if inside a transaction
     * @return bool <b>TRUE</b> if a transaction is currently active, and <b>FALSE</b> if not.
     */
    public function inTransaction()
    {
        return $this->_inTransaction;
    }
}

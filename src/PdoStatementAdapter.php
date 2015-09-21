<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace edgardmessias\db\firebird;

use PDO;
use PDOStatement;

/**
 * Description of PdoAdapter
 *
 * @author Edgard Lorraine Messias <edgard.cpd@romera.com.br>
 */
class PdoStatementAdapter extends PDOStatement
{

    /**
     * @var PdoAdapter
     */
    public $pdo;

    protected function changeCase($row)
    {
        if (!is_array($row)) {
            return $row;
        }

        $newRow = [];

        foreach ($row as $key => $value) {
            if (preg_match("/^[A-Z0-9\-\_]+$/", $key)) {
                $newRow[strtolower($key)] = $value;
            } else {
                $newRow[$key] = $value;
            }
        }

        return $newRow;
    }

    public function fetchAll($fetch_style = null, $fetch_argument = null, $ctor_args = null)
    {
        $result = call_user_func_array([$this, 'parent::fetchAll'], func_get_args());

        if ($this->pdo->getAttribute(PDO::ATTR_CASE) == PDO::CASE_NATURAL) {
            foreach ($result as $key => $row) {
                $result[$key] = $this->changeCase($row);
            }
        }
        return $result;
    }

    public function fetch($fetch_style = null, $cursor_orientation = null, $cursor_offset = null)
    {
        $result = call_user_func_array([$this, 'parent::fetch'], func_get_args());

        if ($this->pdo->getAttribute(PDO::ATTR_CASE) == PDO::CASE_NATURAL) {
            $result = $this->changeCase($result);
        }
        return $result;
    }

    public function bindColumn($column, &$param, $type = null, $maxlen = null, $driverdata = null)
    {
        if (preg_match("/^[a-z0-9\-\_]+$/", $column)) {
            $column = strtoupper($column);
        }

        return parent::bindColumn($column, $param, $type, $maxlen, $driverdata);
    }
}

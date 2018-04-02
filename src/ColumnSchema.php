<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace edgardmessias\db\firebird;

use yii\db\ExpressionInterface;
use yii\db\PdoValue;
use yii\db\Query;

/**
 *
 * @author Edgard Lorraine Messias <edgardmessias@gmail.com>
 * @since 2.0
 */
class ColumnSchema extends \yii\db\ColumnSchema
{

    /**
     * Converts the input value according to [[phpType]] after retrieval from the database.
     * If the value is null or an [[Expression]], it will not be converted.
     * @param mixed $value input value
     * @return mixed converted value
     * @since 2.0.3
     */
    protected function typecast($value)
    {
        if ($value === ''
            && !in_array(
                $this->type,
                [
                    Schema::TYPE_TEXT,
                    Schema::TYPE_STRING,
                    Schema::TYPE_BINARY,
                    Schema::TYPE_CHAR
                ],
                true)
        ) {
            return null;
        }

        if ($value === null
            || gettype($value) === $this->phpType
            || $value instanceof ExpressionInterface
            || $value instanceof Query
        ) {
            return $value;
        }

        if (is_array($value)
            && count($value) === 2
            && isset($value[1])
            && in_array($value[1], $this->getPdoParamTypes(), true)
        ) {
            return new PdoValue($value[0], $value[1]);
        }

        switch ($this->phpType) {
            case 'resource':
            case 'string':
                if (is_resource($value)) {
                    return $value;
                }
                if (is_float($value)) {
                    // ensure type cast always has . as decimal separator in all locales
                    return str_replace(',', '.', (string) $value);
                }
                return (string) $value;
            case 'integer':
                if (is_bool($value)) {
                    return ($value) ? 1 : 0;
                }
                return (int) $value;
            case 'boolean':
                return (boolean) $value;
            case 'double':
                return (double) $value;
        }

        return $value;
    }

    /**
     * @return int[] array of numbers that represent possible PDO parameter types
     */
    private function getPdoParamTypes()
    {
        return [\PDO::PARAM_BOOL, \PDO::PARAM_INT, \PDO::PARAM_STR, \PDO::PARAM_LOB, \PDO::PARAM_NULL, \PDO::PARAM_STMT];
    }
}

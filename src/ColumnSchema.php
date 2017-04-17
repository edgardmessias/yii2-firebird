<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace edgardmessias\db\firebird;

use yii\db\Expression;

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

        if ($value === '' && $this->type !== Schema::TYPE_TEXT && $this->type !== Schema::TYPE_STRING && $this->type !== Schema::TYPE_BINARY) {
            return null;
        }
        if ($value === null || gettype($value) === $this->phpType || $value instanceof Expression) {
            return $value;
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
    
    public function dbTypecast($value) {
        $r = parent::dbTypecast($value);
        
        if ($r === true) {
            return 'true';
        } elseif ($r === false){
            return 'false';
        }
        
        return $r;
    }
}

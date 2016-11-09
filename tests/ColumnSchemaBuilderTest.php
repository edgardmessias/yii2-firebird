<?php

namespace edgardmessias\unit\db\firebird;

use edgardmessias\db\firebird\ColumnSchemaBuilder;

/**
 * @group firebird
 */
class ColumnSchemaBuilderTest extends \yiiunit\framework\db\ColumnSchemaBuilderTest
{

    use FirebirdTestTrait;

    public $driverName = 'firebird';

    /**
     * @param string $type
     * @param integer $length
     * @return ColumnSchemaBuilder
     */
    public function getColumnSchemaBuilder($type, $length = null)
    {
        return new ColumnSchemaBuilder($type, $length, $this->getConnection());
    }
    
    /**
     * @return array
     */
    public function typesProvider()
    {
        $parent = parent::typesProvider();
        
        $parent[0][0] = 'integer DEFAULT NULL NULL';
        
        return $parent;
    }
}

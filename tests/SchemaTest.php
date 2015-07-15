<?php

namespace edgardmessias\unit\db\firebird;

use edgardmessias\db\firebird\Schema;
use PDO;
use yii\caching\FileCache;
use yii\db\Expression;

/**
 * @group firebird
 */
class SchemaTest extends \yiiunit\framework\db\SchemaTest
{

    use FirebirdTestTrait;

    public $driverName = 'firebird';

    public function testGetTableNames()
    {
        /* @var $schema Schema */
        $schema = $this->getConnection()->schema;
        $tables = $schema->getTableNames();
        $this->assertTrue(in_array('customer', $tables));
        $this->assertTrue(in_array('category', $tables));
        $this->assertTrue(in_array('item', $tables));
        $this->assertTrue(in_array('order', $tables));
        $this->assertTrue(in_array('order_item', $tables));
        $this->assertTrue(in_array('type', $tables));
        $this->assertTrue(in_array('animal', $tables));
//        $this->assertTrue(in_array('animal_view', $tables));
    }

    public function testSingleFk()
    {
        /* @var $schema Schema */
        $schema = $this->getConnection()->schema;
        $table = $schema->getTableSchema('order_item');
        $this->assertCount(2, $table->foreignKeys);
        $this->assertTrue(isset($table->foreignKeys[0]));
        $this->assertEquals('order', $table->foreignKeys[0][0]);
        $this->assertEquals('id', $table->foreignKeys[0]['order_id']);
        $this->assertTrue(isset($table->foreignKeys[1]));
        $this->assertEquals('item', $table->foreignKeys[1][0]);
        $this->assertEquals('id', $table->foreignKeys[1]['item_id']);
    }

    public function getExpectedColumns()
    {
        $columns = parent::getExpectedColumns();
        unset($columns['enum_col']);
        $columns['int_col']['dbType'] = 'integer';
        $columns['int_col']['size'] = null;
        $columns['int_col']['precision'] = null;
        $columns['int_col2']['dbType'] = 'integer';
        $columns['int_col2']['size'] = null;
        $columns['int_col2']['precision'] = null;
        $columns['smallint_col']['dbType'] = 'smallint';
        $columns['smallint_col']['size'] = null;
        $columns['smallint_col']['precision'] = null;
        
        /**
         * Removed blob support
         * @see https://bugs.php.net/bug.php?id=61183
         */
//        $columns['char_col3']['dbType'] = 'blob sub_type text';
        
        $columns['char_col3']['dbType'] = 'varchar(255)';
        $columns['char_col3']['type'] = 'string';
        $columns['char_col3']['size'] = 255;
        $columns['char_col3']['precision'] = 255;
        $columns['blob_col']['dbType'] = 'varchar(255)';
        $columns['blob_col']['phpType'] = 'string';
        $columns['blob_col']['type'] = 'string';
        $columns['blob_col']['size'] = 255;
        $columns['blob_col']['precision'] = 255;
        
        $columns['float_col']['dbType'] = 'double precision';
        $columns['float_col']['size'] = null;
        $columns['float_col']['precision'] = null;
        $columns['float_col']['scale'] = null;
        $columns['float_col2']['dbType'] = 'double precision';
        $columns['float_col2']['size'] = null;
        $columns['float_col2']['precision'] = null;
        $columns['float_col2']['scale'] = null;
        $columns['bool_col']['dbType'] = 'smallint';
        $columns['bool_col']['size'] = null;
        $columns['bool_col']['precision'] = null;
        $columns['bool_col2']['dbType'] = 'smallint';
        $columns['bool_col2']['size'] = null;
        $columns['bool_col2']['precision'] = null;
        $columns['bit_col']['type'] = 'smallint';
        $columns['bit_col']['dbType'] = 'smallint';
        $columns['bit_col']['size'] = null;
        $columns['bit_col']['precision'] = null;
        return $columns;
    }
    
    public function testGetPDOType()
    {
        $values = [
            [null, \PDO::PARAM_NULL],
            ['', \PDO::PARAM_STR],
            ['hello', \PDO::PARAM_STR],
            [0, \PDO::PARAM_INT],
            [1, \PDO::PARAM_INT],
            [1337, \PDO::PARAM_INT],
            [true, \PDO::PARAM_INT],
            [false, \PDO::PARAM_INT],
            [$fp = fopen(__FILE__, 'rb'), \PDO::PARAM_LOB],
        ];

        /* @var $schema Schema */
        $schema = $this->getConnection()->schema;

        foreach ($values as $value) {
            $this->assertEquals($value[1], $schema->getPdoType($value[0]), 'type for value ' . print_r($value[0], true) . ' does not match.');
        }
        fclose($fp);
    }
    
    public function testGetLastInsertID()
    {
        /* @var $schema Schema */
        $schema = $this->getConnection()->schema;
        $this->assertEquals(null, $schema->getLastInsertID());
        $this->assertEquals(2, $schema->getLastInsertID($schema->getTableSchema('animal')->sequenceName));
        $this->assertEquals(2, $schema->getLastInsertID($schema->getTableSchema('profile')->sequenceName));
    }
}

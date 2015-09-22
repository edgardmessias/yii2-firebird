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

    public function testView()
    {
        /* @var $schema Schema */
        $schema = $this->getConnection()->schema;
        
        $table = $schema->getTableSchema('animal_view');
        
        $this->assertNotNull($table);
        $this->assertCount(2, $table->columnNames);

        //ID Column
        $this->assertTrue(isset($table->columns['id']));
        $this->assertEquals('integer', $table->columns['id']->type);
        $this->assertEquals('integer', $table->columns['id']->dbType);
        $this->assertEquals('integer', $table->columns['id']->phpType);
        $this->assertEquals(null, $table->columns['id']->size);
        $this->assertEquals(null, $table->columns['id']->precision);

        //Type Column
        $this->assertTrue(isset($table->columns['type']));
        $this->assertEquals('string', $table->columns['type']->type);
        $this->assertEquals('varchar(255)', $table->columns['type']->dbType);
        $this->assertEquals('string', $table->columns['type']->phpType);
        $this->assertEquals(255, $table->columns['type']->size);
        $this->assertEquals(255, $table->columns['type']->precision);
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

    public function testFindUniqueIndexes()
    {
        /* @var $schema Schema */
        $schema = $this->getConnection()->schema;

        /* Test single primary key */
        $table = $schema->getTableSchema('order');
        $uniqueIndexes = $schema->findUniqueIndexes($table);

        $this->assertTrue(count($uniqueIndexes) == 1);
        $this->assertEquals(['id'], reset($uniqueIndexes));

        /* Test composer primary key */
        $table = $schema->getTableSchema('order_item');
        $uniqueIndexes = $schema->findUniqueIndexes($table);

        $this->assertTrue(count($uniqueIndexes) == 1);
        $this->assertEquals(['order_id', 'item_id'], reset($uniqueIndexes));

        /* Test without primary key */
        $table = $schema->getTableSchema('unique_values');
        $uniqueIndexes = $schema->findUniqueIndexes($table);

        $this->assertTrue(count($uniqueIndexes) == 4);
        $this->assertEquals(['a'], $uniqueIndexes['uniquea']);
        $this->assertEquals(['b'], $uniqueIndexes['uniqueb']);
        $this->assertEquals(['b', 'c'], $uniqueIndexes['uniquebc']);
        $this->assertEquals(['a', 'b', 'c'], $uniqueIndexes['uniqueabc']);
    }
}

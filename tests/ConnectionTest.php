<?php

namespace edgardmessias\unit\db\firebird;

/**
 * @group firebird
 */
class ConnectionTest extends \yiiunit\framework\db\ConnectionTest
{

    use FirebirdTestTrait;

    public $driverName = 'firebird';

    public function testSerialize()
    {
        $connection = $this->getConnection(false, false);
        $connection->open();
        $serialized = serialize($connection);
        $unserialized = unserialize($serialized);
        $this->assertInstanceOf('yii\db\Connection', $unserialized);

        $this->assertEquals(123, $unserialized->createCommand('SELECT 123 from RDB$DATABASE')->queryScalar());
    }

    public function testQuoteValue()
    {
        $connection = $this->getConnection(false);
        $this->assertEquals(123, $connection->quoteValue(123));
        $this->assertEquals("'string'", $connection->quoteValue('string'));
        $this->assertEquals("'It''s interesting'", $connection->quoteValue("It's interesting"));
    }

    public function testQuoteTableName()
    {
        $connection = $this->getConnection(false);
        $this->assertEquals('table', $connection->quoteTableName('table'));
        $this->assertEquals('"table"', $connection->quoteTableName('"table"'));
        $this->assertEquals('schema.table', $connection->quoteTableName('schema.table'));
        $this->assertEquals('schema."table"', $connection->quoteTableName('schema."table"'));
        $this->assertEquals('"schema"."table"', $connection->quoteTableName('"schema"."table"'));
        $this->assertEquals('{{table}}', $connection->quoteTableName('{{table}}'));
        $this->assertEquals('(table)', $connection->quoteTableName('(table)'));

        $this->assertEquals('"order"', $connection->quoteTableName('order'));
        $this->assertEquals('"order"', $connection->quoteTableName('"order"'));
        $this->assertEquals('schema."order"', $connection->quoteTableName('schema.order'));
        $this->assertEquals('schema."order"', $connection->quoteTableName('schema."order"'));
        $this->assertEquals('"schema"."order"', $connection->quoteTableName('"schema"."order"'));
        $this->assertEquals('{{order}}', $connection->quoteTableName('{{order}}'));
        $this->assertEquals('(order)', $connection->quoteTableName('(order)'));
    }

    public function testQuoteColumnName()
    {
        $connection = $this->getConnection(false);
        $this->assertEquals('column', $connection->quoteColumnName('column'));
        $this->assertEquals('"column"', $connection->quoteColumnName('"column"'));
        $this->assertEquals('table.column', $connection->quoteColumnName('table.column'));
        $this->assertEquals('table."column"', $connection->quoteColumnName('table."column"'));
        $this->assertEquals('"table"."column"', $connection->quoteColumnName('"table"."column"'));
        $this->assertEquals('[[column]]', $connection->quoteColumnName('[[column]]'));
        $this->assertEquals('{{column}}', $connection->quoteColumnName('{{column}}'));
        $this->assertEquals('(column)', $connection->quoteColumnName('(column)'));

        $this->assertEquals('"time"', $connection->quoteColumnName('time'));
        $this->assertEquals('"time"', $connection->quoteColumnName('"time"'));
        $this->assertEquals('"order"."time"', $connection->quoteColumnName('order.time'));
        $this->assertEquals('"order"."time"', $connection->quoteColumnName('order."time"'));
        $this->assertEquals('"order"."time"', $connection->quoteColumnName('"order"."time"'));
        $this->assertEquals('[[time]]', $connection->quoteColumnName('[[time]]'));
        $this->assertEquals('{{time}}', $connection->quoteColumnName('{{time}}'));
        $this->assertEquals('(time)', $connection->quoteColumnName('(time)'));
    }
}

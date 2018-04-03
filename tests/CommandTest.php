<?php

namespace edgardmessias\unit\db\firebird;

use edgardmessias\db\firebird\Schema;
use yii\db\Expression;

/**
 * @group firebird
 */
class CommandTest extends \yiiunit\framework\db\CommandTest
{

    use FirebirdTestTrait;

    public $driverName = 'firebird';

    public function testAutoQuoting()
    {
        $db = $this->getConnection(false);

        $sql = 'SELECT [[id]], [[t.name]] FROM {{customer}} t';
        $command = $db->createCommand($sql);
        $this->assertEquals('SELECT id, t.name FROM customer t', $command->sql);
    }

    public function testColumnCase()
    {
        $this->markTestSkipped('Test for travis with exit code 139');
        return;
        
        $db = $this->getConnection(false);
        
        //Force to use LOWER CASE
        $this->assertEquals(\PDO::CASE_LOWER, $db->slavePdo->getAttribute(\PDO::ATTR_CASE));

        $sql = 'SELECT [[customer_id]], [[total]] FROM {{order}}';
        $rows = $db->createCommand($sql)->queryAll();
        $this->assertTrue(isset($rows[0]));
        $this->assertTrue(isset($rows[0]['customer_id']));
        $this->assertTrue(isset($rows[0]['total']));

        $db->slavePdo->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_LOWER);
        $rows = $db->createCommand($sql)->queryAll();
        $this->assertTrue(isset($rows[0]));
        $this->assertTrue(isset($rows[0]['customer_id']));
        $this->assertTrue(isset($rows[0]['total']));

        $db->slavePdo->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_UPPER);
        $rows = $db->createCommand($sql)->queryAll();
        $this->assertTrue(isset($rows[0]));
        $this->assertTrue(isset($rows[0]['CUSTOMER_ID']));
        $this->assertTrue(isset($rows[0]['TOTAL']));
    }
    
    public function testBindParamValue()
    {
        if (version_compare(phpversion('pdo_firebird'), '7.0.13', '<=')) {
            $this->markTestSkipped('BLOB bug for PHP <= 7.0.13, see https://bugs.php.net/bug.php?id=61183');
        }
        
        $db = $this->getConnection();

        // bindParam
        $sql = 'INSERT INTO {{customer}}([[email]], [[name]], [[address]]) VALUES (:email, :name, :address)';
        $command = $db->createCommand($sql);
        $email = 'user4@example.com';
        $name = 'user4';
        $address = 'address4';
        $command->bindParam(':email', $email);
        $command->bindParam(':name', $name);
        $command->bindParam(':address', $address);
        $command->execute();

        $sql = 'SELECT [[name]] FROM {{customer}} WHERE [[email]] = :email';
        $command = $db->createCommand($sql);
        $command->bindParam(':email', $email);
        $this->assertEquals($name, $command->queryScalar());

        $sql = <<<SQL
INSERT INTO {{type}} ([[int_col]], [[char_col]], [[float_col]], [[blob_col]], [[numeric_col]], [[bool_col]])
  VALUES (:int_col, :char_col, :float_col, :blob_col, :numeric_col, :bool_col)
SQL;
        $command = $db->createCommand($sql);
        $intCol = 123;
        $charCol = str_repeat('abc', 33) . 'x'; // a 100 char string
        $boolCol = false;
        $command->bindParam(':int_col', $intCol, \PDO::PARAM_INT);
        $command->bindParam(':char_col', $charCol);
        $command->bindParam(':bool_col', $boolCol, \PDO::PARAM_BOOL);
        
        $floatCol = 1.23;
        $numericCol = '1.23';
        $blobCol = "\x10\x11\x12";
        $command->bindParam(':float_col', $floatCol);
        $command->bindParam(':numeric_col', $numericCol);
        $command->bindParam(':blob_col', $blobCol);
        
        $this->assertEquals(1, $command->execute());

        $command = $db->createCommand('SELECT [[int_col]], [[char_col]], [[float_col]], [[blob_col]], [[numeric_col]], [[bool_col]] FROM {{type}}');
        
        $row = $command->queryOne();
        $this->assertEquals($intCol, $row['int_col']);
        $this->assertEquals($charCol, $row['char_col']);
        $this->assertEquals($floatCol, $row['float_col']);

        $this->assertEquals($blobCol, $row['blob_col']);
        $this->assertEquals($numericCol, $row['numeric_col']);
        $this->assertEquals($boolCol, (int) $row['bool_col']);

        // bindValue
        $sql = 'INSERT INTO {{customer}}([[email]], [[name]], [[address]]) VALUES (:email, \'user5\', \'address5\')';
        $command = $db->createCommand($sql);
        $command->bindValue(':email', 'user5@example.com');
        $command->execute();

        $sql = 'SELECT [[email]] FROM {{customer}} WHERE [[name]] = :name';
        $command = $db->createCommand($sql);
        $command->bindValue(':name', 'user5');
        $this->assertEquals('user5@example.com', $command->queryScalar());
    }

    /**
     * Test whether param binding works in other places than WHERE
     * @dataProvider paramsNonWhereProvider
     */
    public function testBindParamsNonWhere($sql)
    {
        $this->markTestSkipped('firebird does not support parameter in function');
    }

    public function testBatchInsert()
    {
        $command = $this->getConnection()->createCommand();
        $command->batchInsert(
            '{{customer}}',
            ['email', 'name', 'address'],
            [
                ['t1@example.com', 't1', 't1 address'],
                ['t2@example.com', null, false],
            ]
        );
        $this->assertEquals(2, $command->execute());

        // @see https://github.com/yiisoft/yii2/issues/11693
        $command = $this->getConnection(false)->createCommand();
        $command->batchInsert(
            '{{customer}}',
            ['email', 'name', 'address'],
            []
        );
        $this->assertEquals(0, $command->execute());
    }

    public function testInsertExpression()
    {
        $db = $this->getConnection();
        $db->createCommand('DELETE FROM {{order_with_null_fk}}')->execute();

        $expression = "EXTRACT(YEAR FROM TIMESTAMP 'now')";

        $command = $db->createCommand();
        $command->insert(
            '{{order_with_null_fk}}',
            [
                'created_at' => new Expression($expression),
                'total' => 1,
            ]
        )->execute();
        $this->assertEquals(1, $db->createCommand('SELECT COUNT(*) FROM {{order_with_null_fk}}')->queryScalar());
        $record = $db->createCommand('SELECT [[created_at]] FROM {{order_with_null_fk}}')->queryOne();
        $this->assertEquals([
            'created_at' => date('Y'),
        ], $record);
    }

    public function testCreateTable()
    {
        $db = $this->getConnection();

        if ($db->getSchema()->getTableSchema('testCreateTable') !== null) {
            $db->createCommand()->dropTable('testCreateTable')->execute();
            //Update metadata in connection
            $db->close();
            $db->open();
        }

        $db->createCommand()->createTable('testCreateTable', ['id' => Schema::TYPE_PK, 'bar' => Schema::TYPE_INTEGER])->execute();
        //Update metadata in connection
        $db->close();
        $db->open();

        $db->createCommand()->insert('testCreateTable', ['bar' => 1])->execute();
        $records = $db->createCommand('SELECT [[id]], [[bar]] FROM {{testCreateTable}};')->queryAll();
        $this->assertEquals([
            ['id' => 1, 'bar' => 1],
        ], $records);
    }

    public function testAlterTable()
    {
        $db = $this->getConnection();

        if ($db->getSchema()->getTableSchema('testAlterTable') !== null) {
            $db->createCommand()->dropTable('testAlterTable')->execute();
            //Update metadata in connection
            $db->close();
            $db->open();
        }

        $db->createCommand()->createTable('testAlterTable', ['id' => Schema::TYPE_PK, 'bar' => Schema::TYPE_INTEGER])->execute();
        //Update metadata in connection
        $db->close();
        $db->open();

        $db->createCommand()->insert('testAlterTable', ['bar' => 1])->execute();

        $db->createCommand()->alterColumn('testAlterTable', 'bar', Schema::TYPE_STRING)->execute();
        //Update metadata in connection
        $db->close();
        $db->open();

        $db->createCommand()->insert('testAlterTable', ['bar' => 'hello'])->execute();
        $records = $db->createCommand('SELECT [[id]], [[bar]] FROM {{testAlterTable}};')->queryAll();
        $this->assertEquals([
            ['id' => 1, 'bar' => 1],
            ['id' => 2, 'bar' => 'hello'],
        ], $records);
    }

    public function testRenameTable()
    {
        $this->setExpectedException('\yii\base\NotSupportedException');
        parent::testRenameTable();
    }

    public function testLastInsertId()
    {
        $db = $this->getConnection();

        $sql = 'INSERT INTO {{profile}}([[description]]) VALUES (\'non duplicate\')';
        $command = $db->createCommand($sql);
        $command->execute();
        $this->assertEquals(3, $db->getSchema()->getLastInsertID('gen_profile_id'));
    }
    
    public function testInsertSelect()
    {
        $db = $this->getConnection(false);
        
        /**
         * @see https://firebirdsql.org/file/documentation/reference_manuals/fblangref25-en/html/fblangref25-dml-insert.html#fblangref25-dml-insert-select-unstable
         */
        if (version_compare($db->firebird_version, '3.0.0', '<')) {
            $this->setExpectedException('\yii\base\NotSupportedException', 'Firebird < 3.0.0 has the "Unstable Cursor" problem');
        }
        parent::testInsertSelect();
    }
    
    public function testInsertSelectAlias()
    {
        $db = $this->getConnection(false);
        
        /**
         * @see https://firebirdsql.org/file/documentation/reference_manuals/fblangref25-en/html/fblangref25-dml-insert.html#fblangref25-dml-insert-select-unstable
         */
        if (version_compare($db->firebird_version, '3.0.0', '<')) {
            $this->setExpectedException('\yii\base\NotSupportedException', 'Firebird < 3.0.0 has the "Unstable Cursor" problem');
        }
        parent::testInsertSelectAlias();
    }
    
    /**
     * Test INSERT INTO ... SELECT SQL statement with wrong query object
     *
     * @dataProvider invalidSelectColumns
     * @expectedException \yii\base\InvalidParamException
     * @expectedExceptionMessage Expected select query object with enumerated (named) parameters
     */
    public function testInsertSelectFailed($invalidSelectColumns)
    {
        $db = $this->getConnection(false);
        
        /**
         * @see https://firebirdsql.org/file/documentation/reference_manuals/fblangref25-en/html/fblangref25-dml-insert.html#fblangref25-dml-insert-select-unstable
         */
        if (version_compare($db->firebird_version, '3.0.0', '<')) {
            $this->setExpectedException('\yii\base\NotSupportedException', 'Firebird < 3.0.0 has the "Unstable Cursor" problem');
        }
        parent::testInsertSelectFailed($invalidSelectColumns);
    }
    
    public function testAutoRefreshTableSchema()
    {
        $db = $this->getConnection(false);

        if ($db->getSchema()->getTableSchema('test') !== null) {
            $db->createCommand()->dropTable('test')->execute();
        }

        parent::testAutoRefreshTableSchema();
    }
    
    public function batchInsertSqlProvider() {
        $data = parent::batchInsertSqlProvider();
        
        $data['issue11242']['expected'] = "EXECUTE block AS BEGIN INSERT INTO type (int_col, float_col, char_col) VALUES (NULL, NULL, 'Kyiv {{city}}, Ukraine'); END;";
        $data['wrongBehavior']['expected'] = "EXECUTE block AS BEGIN INSERT INTO type (type.int_col, float_col, char_col) VALUES ('', NULL, 'Kyiv {{city}}, Ukraine'); END;";
        
        // Bingind on block not work
        unset($data['batchInsert binds params from expression']);
        
        return $data;
    }
    
    public function testCreateView() {
        $db = $this->getConnection(false);
        if ($db->getSchema()->getTableSchema('testCreateView') !== null) {
            $db->createCommand()->dropView('testCreateView')->execute();
        }
        parent::testCreateView();
    }
}

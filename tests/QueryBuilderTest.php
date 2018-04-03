<?php

namespace edgardmessias\unit\db\firebird;

use edgardmessias\db\firebird\Schema;
use yii\db\Expression;
use yii\db\Query;
use yiiunit\data\base\TraversableObject;

/**
 * @group firebird
 */
class QueryBuilderTest extends \yiiunit\framework\db\QueryBuilderTest
{

    use FirebirdTestTrait;
    use \yii\db\SchemaBuilderTrait;


    protected $driverName = 'firebird';
    
    protected $likeEscapeCharSql = " ESCAPE '\\'";

    public static function assertSame($expected, $actual, $message = '') {
        // Fix PDO VALUE Compare
        $filter = function ($obj) {
            return !($obj instanceof \yii\db\PdoValue);
        };
        if(is_array($expected)) {
           $expected = array_filter($expected, $filter);
        }
        if(is_array($actual)) {
           $actual = array_filter($actual, $filter);
        }
        
        return parent::assertSame($expected, $actual, $message);
    }
    
    /**
     * @throws \Exception
     * @return \edgardmessias\db\firebird\QueryBuilder
     * @param bool $reset
     * @param bool $open
     */
    protected function getQueryBuilder($reset = true, $open = false)
    {
        $connection = $this->getConnection($reset, $open);

        \Yii::$container->set('db', $connection);

        switch ($this->driverName) {
            case 'firebird':
                return new \edgardmessias\db\firebird\QueryBuilder($connection);
        }
        throw new \Exception('Test is not implemented for ' . $this->driverName);
    }

    /**
     * this is not used as a dataprovider for testGetColumnType to speed up the test
     * when used as dataprovider every single line will cause a reconnect with the database which is not needed here
     */
    public function columnTypes()
    {
        $columns = [
            [Schema::TYPE_BIGINT, $this->bigInteger(), 'bigint'],
            [Schema::TYPE_BIGINT . ' NOT NULL', $this->bigInteger()->notNull(), 'bigint NOT NULL'],
            [Schema::TYPE_BIGINT . ' CHECK (value > 5)', $this->bigInteger()->check('value > 5'), 'bigint CHECK (value > 5)'],
            [Schema::TYPE_BIGINT . '(8)', $this->bigInteger(8), 'bigint'],
            [Schema::TYPE_BIGINT . '(8) CHECK (value > 5)', $this->bigInteger(8)->check('value > 5'), 'bigint CHECK (value > 5)'],
            [Schema::TYPE_BIGPK, $this->bigPrimaryKey(), 'bigint NOT NULL PRIMARY KEY'],
            [Schema::TYPE_BINARY, $this->binary(), 'blob'],
            [Schema::TYPE_BOOLEAN . ' DEFAULT 1 NOT NULL', $this->boolean()->notNull()->defaultValue(1), 'smallint DEFAULT 1 NOT NULL'],
            [Schema::TYPE_BOOLEAN, $this->boolean(), 'smallint'],
            [Schema::TYPE_CHAR . " CHECK (value LIKE 'test%')", $this->char()->check("value LIKE 'test%'"), "char(1) CHECK (value LIKE 'test%')"],
            [Schema::TYPE_CHAR . ' NOT NULL', $this->char()->notNull(), 'char(1) NOT NULL'],
            [Schema::TYPE_CHAR . "(6) CHECK (value LIKE 'test%')", $this->char(6)->check("value LIKE 'test%'"), "char(6) CHECK (value LIKE 'test%')"],
            [Schema::TYPE_CHAR . '(6)', $this->char(6), 'char(6)'],
            [Schema::TYPE_CHAR, $this->char(), 'char(1)'],
            [Schema::TYPE_DATE . " CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')", $this->date()->check("value BETWEEN '2011-01-01' AND '2013-01-01'"), "date CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')"],
            [Schema::TYPE_DATE . ' NOT NULL', $this->date()->notNull(), 'date NOT NULL'],
            [Schema::TYPE_DATE, $this->date(), 'date'],
            [Schema::TYPE_DATETIME . " CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')", $this->dateTime()->check("value BETWEEN '2011-01-01' AND '2013-01-01'"), "timestamp CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')"],
            [Schema::TYPE_DATETIME . ' NOT NULL', $this->dateTime()->notNull(), 'timestamp NOT NULL'],
            [Schema::TYPE_DATETIME, $this->dateTime(), 'timestamp'],
            [Schema::TYPE_DECIMAL . ' CHECK (value > 5.6)', $this->decimal()->check('value > 5.6'), 'numeric(10,0) CHECK (value > 5.6)'],
            [Schema::TYPE_DECIMAL . ' NOT NULL', $this->decimal()->notNull(), 'numeric(10,0) NOT NULL'],
            [Schema::TYPE_DECIMAL . '(12,4) CHECK (value > 5.6)', $this->decimal(12, 4)->check('value > 5.6'), 'numeric(12,4) CHECK (value > 5.6)'],
            [Schema::TYPE_DECIMAL . '(12,4)', $this->decimal(12, 4), 'numeric(12,4)'],
            [Schema::TYPE_DECIMAL, $this->decimal(), 'numeric(10,0)'],
            [Schema::TYPE_DOUBLE . ' CHECK (value > 5.6)', $this->double()->check('value > 5.6'), 'double precision CHECK (value > 5.6)'],
            [Schema::TYPE_DOUBLE . ' NOT NULL', $this->double()->notNull(), 'double precision NOT NULL'],
            [Schema::TYPE_DOUBLE . '(16,5) CHECK (value > 5.6)', $this->double([16, 5])->check('value > 5.6'), 'double precision CHECK (value > 5.6)'],
            [Schema::TYPE_DOUBLE . '(16,5)', $this->double([16, 5]), 'double precision'],
            [Schema::TYPE_DOUBLE, $this->double(), 'double precision'],
            [Schema::TYPE_FLOAT . ' CHECK (value > 5.6)', $this->float()->check('value > 5.6'), 'float CHECK (value > 5.6)'],
            [Schema::TYPE_FLOAT . ' NOT NULL', $this->float()->notNull(), 'float NOT NULL'],
            [Schema::TYPE_FLOAT . '(16,5) CHECK (value > 5.6)', $this->float([16, 5])->check('value > 5.6'), 'float CHECK (value > 5.6)'],
            [Schema::TYPE_FLOAT . '(16,5)', $this->float([16, 5]), 'float'],
            [Schema::TYPE_FLOAT, $this->float(), 'float'],
            [Schema::TYPE_INTEGER . ' CHECK (value > 5)', $this->integer()->check('value > 5'), 'integer CHECK (value > 5)'],
            [Schema::TYPE_INTEGER . ' NOT NULL', $this->integer()->notNull(), 'integer NOT NULL'],
            [Schema::TYPE_INTEGER . '(8) CHECK (value > 5)', $this->integer(8)->check('value > 5'), 'integer CHECK (value > 5)'],
            [Schema::TYPE_INTEGER . '(8)', $this->integer(8), 'integer'],
            [Schema::TYPE_INTEGER, $this->integer(), 'integer'],
            [Schema::TYPE_MONEY . ' CHECK (value > 0.0)', $this->money()->check('value > 0.0'), 'numeric(18,4) CHECK (value > 0.0)'],
            [Schema::TYPE_MONEY . ' NOT NULL', $this->money()->notNull(), 'numeric(18,4) NOT NULL'],
            [Schema::TYPE_MONEY . '(16,2) CHECK (value > 0.0)', $this->money(16, 2)->check('value > 0.0'), 'numeric(16,2) CHECK (value > 0.0)'],
            [Schema::TYPE_MONEY . '(16,2)', $this->money(16, 2), 'numeric(16,2)'],
            [Schema::TYPE_MONEY, $this->money(), 'numeric(18,4)'],
            [Schema::TYPE_PK . ' CHECK (value > 5)', $this->primaryKey()->check('value > 5'), 'integer NOT NULL PRIMARY KEY CHECK (value > 5)'],
            [Schema::TYPE_PK . '(8) CHECK (value > 5)', $this->primaryKey(8)->check('value > 5'), 'integer NOT NULL PRIMARY KEY CHECK (value > 5)'],
            [Schema::TYPE_PK . '(8)', $this->primaryKey(8), 'integer NOT NULL PRIMARY KEY'],
            [Schema::TYPE_PK, $this->primaryKey(), 'integer NOT NULL PRIMARY KEY'],
            [Schema::TYPE_SMALLINT . '(8)', $this->smallInteger(8), 'smallint'],
            [Schema::TYPE_SMALLINT, $this->smallInteger(), 'smallint'],
            [Schema::TYPE_STRING . " CHECK (value LIKE 'test%')", $this->string()->check("value LIKE 'test%'"), "varchar(255) CHECK (value LIKE 'test%')"],
            [Schema::TYPE_STRING . ' NOT NULL', $this->string()->notNull(), 'varchar(255) NOT NULL'],
            [Schema::TYPE_STRING . "(32) CHECK (value LIKE 'test%')", $this->string(32)->check("value LIKE 'test%'"), "varchar(32) CHECK (value LIKE 'test%')"],
            [Schema::TYPE_STRING . '(32)', $this->string(32), 'varchar(32)'],
            [Schema::TYPE_STRING, $this->string(), 'varchar(255)'],
            [Schema::TYPE_TEXT . " CHECK (value LIKE 'test%')", $this->text()->check("value LIKE 'test%'"), "blob sub_type text CHECK (value LIKE 'test%')", Schema::TYPE_TEXT . " CHECK (value LIKE 'test%')"],
            [Schema::TYPE_TEXT . ' NOT NULL', $this->text()->notNull(), 'blob sub_type text NOT NULL', Schema::TYPE_TEXT . ' NOT NULL'],
            [Schema::TYPE_TEXT . "(255) CHECK (value LIKE 'test%')", $this->text(255)->check("value LIKE 'test%'"), "blob sub_type text CHECK (value LIKE 'test%')", Schema::TYPE_TEXT . " CHECK (value LIKE 'test%')"],
            [Schema::TYPE_TEXT . '(255) NOT NULL', $this->text(255)->notNull(), 'blob sub_type text NOT NULL', Schema::TYPE_TEXT . ' NOT NULL'],
            [Schema::TYPE_TEXT . '(255)', $this->text(255), 'blob sub_type text', Schema::TYPE_TEXT],
            [Schema::TYPE_TEXT, $this->text(), 'blob sub_type text', Schema::TYPE_TEXT],
            [Schema::TYPE_TIME . " CHECK (value BETWEEN '12:00:00' AND '13:01:01')", $this->time()->check("value BETWEEN '12:00:00' AND '13:01:01'"), "time CHECK (value BETWEEN '12:00:00' AND '13:01:01')"],
            [Schema::TYPE_TIME . ' NOT NULL', $this->time()->notNull(), 'time NOT NULL'],
            [Schema::TYPE_TIME, $this->time(), 'time'],
            [Schema::TYPE_TIMESTAMP . " CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')", $this->timestamp()->check("value BETWEEN '2011-01-01' AND '2013-01-01'"), "timestamp CHECK (value BETWEEN '2011-01-01' AND '2013-01-01')"],
            [Schema::TYPE_TIMESTAMP . ' NOT NULL', $this->timestamp()->notNull(), 'timestamp NOT NULL'],
            [Schema::TYPE_TIMESTAMP, $this->timestamp(), 'timestamp'],
            //Schema::TYPE_UPK not supported
            //Schema::TYPE_UBIGPK not supported
            //Schema::TYPE_INTEGER comment not supported
            //Schema::TYPE_PK comment not supported
        ];
        
        $db = $this->getConnection(false);
        if (version_compare($db->firebird_version, '3.0.0', '>=')) {
            $columns[5][2] = 'bigint GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY';
            $columns[7][2] = 'boolean DEFAULT 1 NOT NULL';
            $columns[8][2] = 'boolean';
            $columns[45][2] = 'integer GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY CHECK (value > 5)';
            $columns[46][2] = 'integer GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY CHECK (value > 5)';
            $columns[47][2] = 'integer GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY';
            $columns[48][2] = 'integer GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY';
        }

        return $columns;
    }

    public function conditionProvider()
    {
        $conditions = parent::conditionProvider();

        $conditions['composite in'] = [
                ['in', ['id', 'name'], [['id' =>1, 'name' => 'oy']]],
                '((id = :qp0 AND name = :qp1))',
                [':qp0' => 1, ':qp1' => 'oy']
            ];

        $conditions['composite in using array objects'] = [
                ['in', new TraversableObject(['id', 'name']), new TraversableObject([
                    ['id' => 1, 'name' => 'oy'],
                    ['id' => 2, 'name' => 'yo'],
                ])],
                '((id = :qp0 AND name = :qp1) OR (id = :qp2 AND name = :qp3))',
                [':qp0' => 1, ':qp1' => 'oy', ':qp2' => 2, ':qp3' => 'yo']
            ];

        $conditions[44] = [ ['=', 'date', (new Query())->select('max(date)')->from('test')->where(['id' => 5])], '"date" = (SELECT max(date) AS max_date FROM test WHERE id=:qp0)', [':qp0' => 5] ];
        $conditions[51] = [ ['in', ['id', 'name'], [['id' => 1, 'name' => 'foo'], ['id' => 2, 'name' => 'bar']]], '((id = :qp0 AND name = :qp1) OR (id = :qp2 AND name = :qp3))', [':qp0' => 1, ':qp1' => 'foo', ':qp2' => 2, ':qp3' => 'bar']];
        $conditions[52] = [ ['not in', ['id', 'name'], [['id' => 1, 'name' => 'foo'], ['id' => 2, 'name' => 'bar']]], '((id != :qp0 OR name != :qp1) AND (id != :qp2 OR name != :qp3))', [':qp0' => 1, ':qp1' => 'foo', ':qp2' => 2, ':qp3' => 'bar']];
        
        return $conditions;
    }
    
    /**
     * @dataProvider primaryKeysProvider
     * @param string $sql
     */
    public function testAddDropPrimaryKey($sql, \Closure $builder)
    {
        $tableName = 'constraints';

        // Change field1 to not null
        $qb = $this->getQueryBuilder();
        $qb->db->createCommand()->alterColumn($tableName, 'field1', 'string(255) not null')->execute();
        
        parent::testAddDropPrimaryKey($sql, $builder);
    }

    public function defaultValuesProvider()
    {
        $tableName = 'T_constraints_1';
        $name = 'CN_default';
        return [
            'drop' => [
                "ALTER TABLE {{{$tableName}}} ALTER COLUMN [[$name]] DROP DEFAULT",
                function (\yii\db\QueryBuilder $qb) use ($tableName, $name) {
                    return $qb->dropDefaultValue($name, $tableName);
                },
            ],
            'add' => [
                "ALTER TABLE {{{$tableName}}} ALTER COLUMN [[C_default]] SET DEFAULT 0",
                function (\yii\db\QueryBuilder $qb) use ($tableName, $name) {
                    return $qb->addDefaultValue($name, $tableName, 'C_default', 0);
                },
            ],
        ];
    }

    /**
     * This test contains three select queries connected with UNION and UNION ALL constructions.
     * It could be useful to use "phpunit --group=db --filter testBuildUnion" command for run it.
     */
    public function testBuildUnion()
    {
        $expectedQuerySql = $this->replaceQuotes(
            'SELECT [[id]] FROM [[TotalExample]] [[t1]] WHERE (w > 0) AND (x < 2) UNION SELECT [[id]] FROM [[TotalTotalExample]] [[t2]] WHERE w > 5 UNION ALL SELECT [[id]] FROM [[TotalTotalExample]] [[t3]] WHERE w = 3'
        );
        $query = new Query();
        $secondQuery = new Query();
        $secondQuery->select('id')
              ->from('TotalTotalExample t2')
              ->where('w > 5');
        $thirdQuery = new Query();
        $thirdQuery->select('id')
              ->from('TotalTotalExample t3')
              ->where('w = 3');
        $query->select('id')
              ->from('TotalExample t1')
              ->where(['and', 'w > 0', 'x < 2'])
              ->union($secondQuery)
              ->union($thirdQuery, true);
        list($actualQuerySql, $queryParams) = $this->getQueryBuilder()->build($query);
        $this->assertEquals($expectedQuerySql, $actualQuerySql);
        $this->assertEquals([], $queryParams);
    }

    public function testSelectSubquery()
    {
        $subquery = (new Query())
            ->select('COUNT(*)')
            ->from('operations')
            ->where('account_id = accounts.id');
        $query = (new Query())
            ->select('*')
            ->from('accounts')
            ->addSelect(['operations_count' => $subquery]);
        list ($sql, $params) = $this->getQueryBuilder()->build($query);
        $expected = $this->replaceQuotes('SELECT *, (SELECT COUNT(*) AS COUNT_ALL FROM [[operations]] WHERE account_id = accounts.id) AS [[operations_count]] FROM [[accounts]]');
        $this->assertEquals($expected, $sql);
        $this->assertEmpty($params);
    }
        
    public function testRenameTable()
    {
        $this->setExpectedException('\yii\base\NotSupportedException');

        $qb = $this->getQueryBuilder();
        $qb->renameTable('null_values', 'null_values2');
    }
    
    public function testTruncateTable()
    {
        $countBefore = (new Query())->from('animal')->count('*', $this->getConnection(false));
        $this->assertEquals(2, $countBefore);

        $qb = $this->getQueryBuilder();
        
        $sqlTruncate = $qb->truncateTable('animal');
        $this->assertEquals('DELETE FROM animal', $sqlTruncate);
        
        $this->getConnection(false)->createCommand($sqlTruncate)->execute();
        $countAfter = (new Query())->from('animal')->count('*', $this->getConnection(false));
        $this->assertEquals(0, $countAfter);
    }
    
    public function testDropColumn()
    {
        $connection = $this->getConnection(true);
        $qb = $this->getQueryBuilder();
        
        $columns = $connection->getTableSchema('type', true)->columnNames;
        array_shift($columns); //Prevent to remove all columns
        
        foreach ($columns as $column) {
            $connection->createCommand($qb->dropColumn('type', $column))->execute();
        }
        
        $schema = $connection->getTableSchema('type', true);
        foreach ($columns as $column) {
            $this->assertNotContains($column, $schema->columnNames);
        }
    }
    
    public function testRenameColumn()
    {
        $connection = $this->getConnection(true);
        $qb = $this->getQueryBuilder();
        
        $columns = $connection->getTableSchema('type', true)->columnNames;
        
        foreach ($columns as $column) {
            $connection->createCommand($qb->renameColumn('type', $column, $column . '_new'))->execute();
        }
        
        $schema = $connection->getTableSchema('type', true);
        foreach ($columns as $column) {
            $this->assertNotContains($column, $schema->columnNames);
            $this->assertContains($column . '_new', $schema->columnNames);
        }
    }
    
    public function testAlterColumn()
    {
        $connection = $this->getConnection(true);
        $qb = $this->getQueryBuilder();
        
        $connection->createCommand($qb->alterColumn('customer', 'email', Schema::TYPE_STRING . '(128) NULL'))->execute();
        $connection->createCommand($qb->alterColumn('customer', 'name', "SET DEFAULT 'NO NAME'"))->execute();
        $connection->createCommand($qb->alterColumn('customer', 'name', Schema::TYPE_STRING . '(128) NOT NULL'))->execute();
        $connection->createCommand($qb->alterColumn('customer', 'profile_id', Schema::TYPE_INTEGER . ' NOT NULL'))->execute();

        $newColumns = $connection->getTableSchema('customer', true)->columns;
        
        $this->assertEquals(true, $newColumns['email']->allowNull);
        $this->assertEquals(false, $newColumns['name']->allowNull);
        $this->assertEquals('NO NAME', $newColumns['name']->defaultValue);
        $this->assertEquals(false, $newColumns['profile_id']->allowNull);
        $this->assertEquals(0, $newColumns['profile_id']->defaultValue);
    }
        
    public function testDropIndex()
    {
        $connection = $this->getConnection(true);
        $qb = $this->getQueryBuilder();
        
        $this->assertEquals('DROP INDEX idx_int_col', $qb->dropIndex('idx_int_col', 'type'));
        
        $columns = $connection->getTableSchema('type', true)->columns;
        
        foreach ($columns as $column) {
            if (strpos($column->dbType, 'blob') !== false) {
                continue;
            }
            $result = $connection->createCommand($qb->createIndex('idx_' . $column->name, 'type', $column->name))->execute();
        }
        
        foreach ($columns as $column) {
            if (strpos($column->dbType, 'blob') !== false) {
                continue;
            }
            $result = $connection->createCommand($qb->dropIndex('idx_' . $column->name, 'type'))->execute();
        }
    }
    
    public function testResetSequence()
    {
        $connection = $this->getConnection(true);
        $qb = $this->getQueryBuilder();
        
        $this->assertEquals('ALTER SEQUENCE seq_animal_id RESTART WITH 3', $qb->resetSequence('animal'));
        $this->assertEquals('ALTER SEQUENCE seq_animal_id RESTART WITH 10', $qb->resetSequence('animal', 10));
        
        $this->assertEquals('ALTER SEQUENCE gen_profile_id RESTART WITH 3', $qb->resetSequence('profile'));
        $this->assertEquals('ALTER SEQUENCE gen_profile_id RESTART WITH 10', $qb->resetSequence('profile', 10));
        
        $this->assertEquals(2, (new Query())->from('profile')->max('id', $connection));
        
        $connection->createCommand()->insert('profile', ['description' => 'profile customer 3'])->execute();
        $this->assertEquals(3, (new Query())->from('profile')->max('id', $connection));
        
        $connection->createCommand($qb->resetSequence('profile'))->execute();
        $connection->createCommand()->insert('profile', ['description' => 'profile customer 4'])->execute();
        $this->assertEquals(4, (new Query())->from('profile')->max('id', $connection));
        
        $connection->createCommand($qb->resetSequence('profile', 10))->execute();
        $connection->createCommand()->insert('profile', ['description' => 'profile customer 11'])->execute();
        $this->assertEquals(11, (new Query())->from('profile')->max('id', $connection));
    }
    
    public function testCreateTableWithAutoIncrement()
    {
        $qb = $this->getQueryBuilder();
        if ($qb->db->getTableSchema('autoincrement_table', true) !== null) {
            $this->getConnection(false)->createCommand($qb->dropTable('autoincrement_table'))->execute();
        }
        $columns = [
            'id' => Schema::TYPE_PK,
            'description' => Schema::TYPE_STRING,
        ];
        $this->getConnection(false)->createCommand($qb->createTable('autoincrement_table', $columns))->execute();
        $qb->db->getTableSchema('autoincrement_table', true); //Force update schema
        
        $this->assertEquals(1, $this->getConnection(false)->getSchema()->insert('autoincrement_table', ['description' => 'auto increment 1'])['id']);
        $this->assertEquals(2, $this->getConnection(false)->getSchema()->insert('autoincrement_table', ['description' => 'auto increment 2'])['id']);
        $this->assertEquals(3, $this->getConnection(false)->getSchema()->insert('autoincrement_table', ['description' => 'auto increment 3'])['id']);
        $this->assertEquals(4, $this->getConnection(false)->getSchema()->insert('autoincrement_table', ['description' => 'auto increment 4'])['id']);
        
        $this->assertEquals(4, (new Query())->from('autoincrement_table')->max('id', $this->getConnection(false)));
        
        //Drop and recreate, for test sequences
        $this->getConnection(false)->createCommand($qb->dropTable('autoincrement_table'))->execute();
        $this->getConnection(false)->createCommand($qb->createTable('autoincrement_table', $columns))->execute();
        
        $this->assertEquals(1, $this->getConnection(false)->getSchema()->insert('autoincrement_table', ['description' => 'auto increment 1'])['id']);
        $this->assertEquals(2, $this->getConnection(false)->getSchema()->insert('autoincrement_table', ['description' => 'auto increment 2'])['id']);
        $this->assertEquals(3, $this->getConnection(false)->getSchema()->insert('autoincrement_table', ['description' => 'auto increment 3'])['id']);
        $this->assertEquals(4, $this->getConnection(false)->getSchema()->insert('autoincrement_table', ['description' => 'auto increment 4'])['id']);
        
        $this->assertEquals(4, (new Query())->from('autoincrement_table')->max('id', $this->getConnection(false)));
    }
    
    /**
     * @dataProvider insertProvider
     * @param string $table
     * @param array $columns
     * @param array $params
     * @param string $expectedSQL
     * @param array $expectedParams
     */
    public function testInsert($table, $columns, $params, $expectedSQL, $expectedParams) {
        $db = $this->getConnection(false);
        if (is_object($columns) && $columns instanceof \yii\db\Query && version_compare($db->firebird_version, '3.0.0', '<')) {
            $this->setExpectedException('\yii\base\NotSupportedException', 'Firebird < 3.0.0 has the "Unstable Cursor" problem');
        }
        parent::testInsert($table, $columns, $params, $expectedSQL, $expectedParams);
    }

    public function upsertProvider()
    {
        $concreteData = [
            'regular values' => [
                3 => 'MERGE INTO T_upsert USING (SELECT CAST(:qp0 AS varchar(128)) AS email, CAST(:pv1 AS blob sub_type text) AS address, CAST(:qp2 AS smallint) AS status, CAST(:qp3 AS integer) AS profile_id FROM RDB$DATABASE) "EXCLUDED" ON (T_upsert.email="EXCLUDED".email) WHEN MATCHED THEN UPDATE SET address="EXCLUDED".address, status="EXCLUDED".status, profile_id="EXCLUDED".profile_id WHEN NOT MATCHED THEN INSERT (email, address, status, profile_id) VALUES ("EXCLUDED".email, "EXCLUDED".address, "EXCLUDED".status, "EXCLUDED".profile_id)',
                4 => [
                    ':qp0' => 'test@example.com',
                    ':pv1' => new \yii\db\PdoValue('bar {{city}}', \PDO::PARAM_LOB),
                    ':qp2' => 1,
                    ':qp3' => null,
                ],

            ],
            'regular values with update part' => [
                3 => 'MERGE INTO T_upsert USING (SELECT CAST(:qp0 AS varchar(128)) AS email, CAST(:pv1 AS blob sub_type text) AS address, CAST(:qp2 AS smallint) AS status, CAST(:qp3 AS integer) AS profile_id FROM RDB$DATABASE) "EXCLUDED" ON (T_upsert.email="EXCLUDED".email) WHEN MATCHED THEN UPDATE SET address=:pv4, status=:qp5, orders=T_upsert.orders + 1 WHEN NOT MATCHED THEN INSERT (email, address, status, profile_id) VALUES ("EXCLUDED".email, "EXCLUDED".address, "EXCLUDED".status, "EXCLUDED".profile_id)',
                4 => [
                    ':qp0' => 'test@example.com',
                    ':pv1' => new \yii\db\PdoValue('bar {{city}}', \PDO::PARAM_LOB),
                    ':qp2' => 1,
                    ':qp3' => null,
                    ':pv4' => new \yii\db\PdoValue('foo {{city}}', \PDO::PARAM_LOB),
                    ':qp5' => 2,
                ],
            ],
            'regular values without update part' => [
                3 => 'MERGE INTO T_upsert USING (SELECT CAST(:qp0 AS varchar(128)) AS email, CAST(:pv1 AS blob sub_type text) AS address, CAST(:qp2 AS smallint) AS status, CAST(:qp3 AS integer) AS profile_id FROM RDB$DATABASE) "EXCLUDED" ON (T_upsert.email="EXCLUDED".email) WHEN NOT MATCHED THEN INSERT (email, address, status, profile_id) VALUES ("EXCLUDED".email, "EXCLUDED".address, "EXCLUDED".status, "EXCLUDED".profile_id)',
                4 => [
                    ':qp0' => 'test@example.com',
                    ':pv1' => new \yii\db\PdoValue('bar {{city}}', \PDO::PARAM_LOB),
                    ':qp2' => 1,
                    ':qp3' => null,
                ],
            ],
            'query' => [
                3 => 'MERGE INTO T_upsert USING (SELECT FIRST 1 email, 2 AS status FROM customer WHERE name=:qp0) "EXCLUDED" ON (T_upsert.email="EXCLUDED".email) WHEN MATCHED THEN UPDATE SET status="EXCLUDED".status WHEN NOT MATCHED THEN INSERT (email, status) VALUES ("EXCLUDED".email, "EXCLUDED".status)',
            ],
            'query with update part' => [
                3 => 'MERGE INTO T_upsert USING (SELECT FIRST 1 email, 2 AS status FROM customer WHERE name=:qp0) "EXCLUDED" ON (T_upsert.email="EXCLUDED".email) WHEN MATCHED THEN UPDATE SET address=:pv1, status=:qp2, orders=T_upsert.orders + 1 WHEN NOT MATCHED THEN INSERT (email, status) VALUES ("EXCLUDED".email, "EXCLUDED".status)',
                4 => [
                    ':qp0' => 'user1',
                    ':pv1' => new \yii\db\PdoValue('foo {{city}}', \PDO::PARAM_LOB),
                    ':qp2' => 2,
                ],
            ],
            'query without update part' => [
                3 => 'MERGE INTO T_upsert USING (SELECT FIRST 1 email, 2 AS status FROM customer WHERE name=:qp0) "EXCLUDED" ON (T_upsert.email="EXCLUDED".email) WHEN NOT MATCHED THEN INSERT (email, status) VALUES ("EXCLUDED".email, "EXCLUDED".status)',
            ],
            'values and expressions' => [
                3 => 'UPDATE OR INSERT INTO {{%T_upsert}} ({{%T_upsert}}.[[email]], [[ts]]) VALUES (:qp0, now())',
            ],
            'values and expressions with update part' => [
                3 => 'UPDATE OR INSERT INTO {{%T_upsert}} ({{%T_upsert}}.[[email]], [[ts]]) VALUES (:qp0, now())',
            ],
            'values and expressions without update part' => [
                3 => 'UPDATE OR INSERT INTO {{%T_upsert}} ({{%T_upsert}}.[[email]], [[ts]]) VALUES (:qp0, now())',
            ],
            'query, values and expressions with update part' => [
                3 => 'MERGE INTO {{%T_upsert}} USING (SELECT :phEmail AS email, now() AS [[time]]) "EXCLUDED" ON ({{%T_upsert}}.email="EXCLUDED".email) WHEN MATCHED THEN UPDATE SET ts=:qp1, [[orders]]=T_upsert.orders + 1 WHEN NOT MATCHED THEN INSERT (email, [[time]]) VALUES ("EXCLUDED".email, "EXCLUDED".[[time]])',
            ],
            'query, values and expressions without update part' => [
                3 => 'MERGE INTO {{%T_upsert}} USING (SELECT :phEmail AS email, now() AS [[time]]) "EXCLUDED" ON ({{%T_upsert}}.email="EXCLUDED".email) WHEN MATCHED THEN UPDATE SET ts=:qp1, [[orders]]=T_upsert.orders + 1 WHEN NOT MATCHED THEN INSERT (email, [[time]]) VALUES ("EXCLUDED".email, "EXCLUDED".[[time]])',
            ],
        ];

        $newData = parent::upsertProvider();
        foreach ($concreteData as $testName => $data) {
            $newData[$testName] = array_replace($newData[$testName], $data);
        }        
        return $newData;
    }
    
    /**
     * @depends testInitFixtures
     * @dataProvider upsertProvider
     * @param string $table
     * @param array $insertColumns
     * @param array|null $updateColumns
     * @param string|string[] $expectedSQL
     * @param array $expectedParams
     */
    public function testUpsert($table, $insertColumns, $updateColumns, $expectedSQL, $expectedParams) {
        $db = $this->getConnection(false);
        if (is_object($insertColumns) && $insertColumns instanceof \yii\db\Query && version_compare($db->firebird_version, '3.0.0', '<')) {
            $this->setExpectedException('\yii\base\NotSupportedException', 'Firebird < 3.0.0 has the "Unstable Cursor" problem');
        }
        parent::testUpsert($table, $insertColumns, $updateColumns, $expectedSQL, $expectedParams);
    }
    
    public function batchInsertProvider()
    {
        $tests = parent::batchInsertProvider();
        
        foreach ($tests as &$test) {
            $index = isset($test['expected']) ? 'expected' : 3;

            if ($test[$index]) {
                $test[$index] = 'EXECUTE block AS BEGIN ' . $test[$index] . '; END;';
            }
        }
        
        $tests['escape-danger-chars']['expected'] = "EXECUTE block AS BEGIN INSERT INTO customer (address) VALUES ('SQL-danger chars are escaped: ''); --'); END;";
        
        return $tests;
    }

    public function testCommentColumn()
    {
        $qb = $this->getQueryBuilder();

        $expected = $this->replaceQuotes("COMMENT ON COLUMN [[comment]].[[add_comment]] IS 'This is my column.'");
        $sql = $qb->addCommentOnColumn('comment', 'add_comment', 'This is my column.');
        $this->assertEquals($expected, $sql);

        $expected = $this->replaceQuotes("COMMENT ON COLUMN [[comment]].[[replace_comment]] IS 'This is my column.'");
        $sql = $qb->addCommentOnColumn('comment', 'replace_comment', 'This is my column.');
        $this->assertEquals($expected, $sql);

        $expected = $this->replaceQuotes('COMMENT ON COLUMN [[comment]].[[delete_comment]] IS NULL');
        $sql = $qb->dropCommentFromColumn('comment', 'delete_comment');
        $this->assertEquals($expected, $sql);
    }

    public function testCommentTable()
    {
        $qb = $this->getQueryBuilder();

        $expected = $this->replaceQuotes("COMMENT ON TABLE [[comment]] IS 'This is my table.'");
        $sql = $qb->addCommentOnTable('comment', 'This is my table.');
        $this->assertEquals($expected, $sql);

        $expected = $this->replaceQuotes('COMMENT ON TABLE [[comment]] IS NULL');
        $sql = $qb->dropCommentFromTable('comment');
        $this->assertEquals($expected, $sql);
    }

    public function testReplaceQuotes()
    {
        //Normal words
        $this->assertEquals('comment', $this->replaceQuotes('[[comment]]'));
        $this->assertEquals('test', $this->replaceQuotes('[[test]]'));
        
        //Reserved Words lower case
        $this->assertEquals('"order"', $this->replaceQuotes('[[order]]'));
        $this->assertEquals('"time"', $this->replaceQuotes('[[time]]'));
        //Reserved Words UPPER CASE
        $this->assertEquals('"ORDER"', $this->replaceQuotes('[[ORDER]]'));
        $this->assertEquals('"TIME"', $this->replaceQuotes('[[TIME]]'));
        //Reserved Words Multiple
        $this->assertEquals('"order".comment', $this->replaceQuotes('[[order]].[[comment]]'));
        $this->assertEquals('"order"."time"', $this->replaceQuotes('[[order]].[[time]]'));
    }

    public function indexesProvider()
    {
        $tests = parent::indexesProvider();
        
        $tests['drop'][0] = 'DROP INDEX [[CN_constraints_2_single]]';
        
        return $tests;
    }
}

<?php

namespace edgardmessias\unit\db\firebird;

use yiiunit\data\ar\Customer;

/**
 * @group firebird
 */
class ActiveRecordTest extends \yiiunit\framework\db\ActiveRecordTest
{

    use FirebirdTestTrait;

    public $driverName = 'firebird';
    
    public function testAutoId()
    {
        $animal = new \yiiunit\data\ar\Animal();
        $animal->type = 'cat';

        $this->assertTrue($animal->save());

        $this->assertEquals(3, $animal->id);

        $customer = new Customer();
        $customer->email = 'test@test.net';
        $this->assertTrue($customer->save());
        
        $this->assertEquals(4, $customer->id);
    }
    
    
    public function testCustomColumns()
    {
        // find custom column
        $customer = Customer::find()->select(['{{customer}}.*', '([[status]]*2) AS [[status2]]'])
            ->where(['name' => 'user3'])->one();
        $this->assertEquals(3, $customer->id);
        $this->assertEquals(4, $customer->status2);
    }

    public function testPopulateWithoutPk()
    {
        $this->markTestSkipped();
    }
    
    public function testCastValues()
    {
        if (!$this->getConnection(false)->supportBlobDataType) {
            $this->markTestSkipped('BLOB bug for PHP <= 7.0.13, see https://bugs.php.net/bug.php?id=61183');
        }
        parent::testCastValues();
    }
}

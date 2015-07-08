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
    
    public function testCustomColumns()
    {
        // find custom column
        $customer = Customer::find()->select(['{{customer}}.*', '([[status]]*2) AS [[status2]]'])
            ->where(['name' => 'user3'])->one();
        $this->assertEquals(3, $customer->id);
        $this->assertEquals(4, $customer->status2);
    }
}

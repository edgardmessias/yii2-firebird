<?php

namespace edgardmessias\unit\db\firebird;

/**
 * @group firebird
 */
class ActiveDataProviderTest extends \yiiunit\framework\data\ActiveDataProviderTest
{

    use FirebirdTestTrait;

    protected $driverName = 'firebird';
}

<?php

namespace edgardmessias\unit\db\firebird;

/**
 * @group firebird
 */
class BatchQueryResultTest extends \yiiunit\framework\db\BatchQueryResultTest
{

    use FirebirdTestTrait;

    public $driverName = 'firebird';
}

<?php

namespace edgardmessias\unit\db\firebird;

/**
 * @group firebird
 */
class UniqueValidatorTest extends \yiiunit\framework\validators\UniqueValidatorTest
{

    use FirebirdTestTrait;

    protected $driverName = 'firebird';
}

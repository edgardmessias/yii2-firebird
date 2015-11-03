<?php

namespace edgardmessias\unit\db\firebird;

/**
 * @group firebird
 */
class ExistValidatorTest extends \yiiunit\framework\validators\ExistValidatorTest
{

    use FirebirdTestTrait;

    protected $driverName = 'firebird';
}

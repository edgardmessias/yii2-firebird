<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace edgardmessias\db\firebird;

use yii\db\Expression;
use yii\db\ExpressionBuilderInterface;
use yii\db\ExpressionBuilderTrait;
use yii\db\ExpressionInterface;

/**
 * {@inheritdoc}
 */
class ExpressionBuilder implements ExpressionBuilderInterface
{
    use ExpressionBuilderTrait;


    /**
     * {@inheritdoc}
     * @param Expression|ExpressionInterface $expression the expression to be built
     */
    public function build(ExpressionInterface $expression, array &$params = [])
    {
        $params = array_merge($params, $expression->params);
        $string = $expression->__toString();
        
        static $expressionMap = [
            "strftime('%Y')" => "EXTRACT(YEAR FROM TIMESTAMP 'now')"
        ];
        
        if (isset($expressionMap[$string])) {
            return $expressionMap[$string];
        }
        
        return $string;
    }
}

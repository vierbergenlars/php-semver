<?php

namespace vierbergenlars\SemVer\Internal\Expr;

use vierbergenlars\SemVer\Internal\AbstractVersion;

class AndExpression implements ExpressionInterface
{
    /**
     *
     * @var ExpressionInterface[]
     */
    private $expressions;

    public function __construct(array $expressions)
    {
        $this->expressions = $expressions;
    }

    public function matches(AbstractVersion $v)
    {
        foreach($this->expressions as $expression) {
            if(!$expression->matches($v)) {
                //var_dump($expression);
                return false;
            }
        }
        return true;
    }

    public function __toString()
    {
        return implode(' ', $this->expressions);
    }
}
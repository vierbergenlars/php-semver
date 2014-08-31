<?php

namespace vierbergenlars\SemVer\Internal\Expr;

use vierbergenlars\SemVer\Internal\AbstractVersion;

class OrExpression implements ExpressionInterface
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
            if($expression->matches($v)) {
                return true;
            }
        }
        return false;
    }

    public function __toString()
    {
        return implode('||', $this->expressions);
    }
}
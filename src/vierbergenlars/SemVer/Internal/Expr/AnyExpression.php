<?php

namespace vierbergenlars\SemVer\Internal\Expr;

use vierbergenlars\SemVer\Internal\AbstractVersion;

class AnyExpression implements ExpressionInterface
{
    public function matches(AbstractVersion $v)
    {
        return true;
    }

    public function __toString()
    {
        return '*';
    }

    public function getNormalized()
    {
        return (string)$this;
    }
}

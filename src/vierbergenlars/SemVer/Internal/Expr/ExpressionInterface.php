<?php

namespace vierbergenlars\SemVer\Internal\Expr;

use vierbergenlars\SemVer\Internal\AbstractVersion;

interface ExpressionInterface
{
    public function matches(AbstractVersion $v);
    public function __toString();
    public function getNormalized();
}

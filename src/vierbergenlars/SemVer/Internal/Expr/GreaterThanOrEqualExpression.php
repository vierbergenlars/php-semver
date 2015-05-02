<?php

namespace vierbergenlars\SemVer\Internal\Expr;

use vierbergenlars\SemVer\Internal\AbstractVersion;

class GreaterThanOrEqualExpression implements ExpressionInterface
{
    /**
     *
     * @var AbstractVersion
     */
    private $version;

    public function __construct(AbstractVersion $version)
    {
        $this->version = $version;
    }

    public function matches(AbstractVersion $version)
    {
        return $version->compare($this->version) >= 0;
    }

    public function __toString()
    {
        return '>='.$this->version;
    }
public function getNormalized()
{
    return '>='.$this->version->getNormalized();
}

}

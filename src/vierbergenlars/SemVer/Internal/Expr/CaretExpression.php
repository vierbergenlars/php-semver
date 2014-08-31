<?php

namespace vierbergenlars\SemVer\Internal\Expr;

use vierbergenlars\SemVer\Internal\Version;
use vierbergenlars\SemVer\Internal\PartialVersion;
use vierbergenlars\SemVer\Internal\AbstractVersion;

class CaretExpression implements ExpressionInterface
{
    /**
     *
     * @var PartialVersion
     */
    private $version;

    public function __construct(PartialVersion $version)
    {
        $this->version = $version;
    }

    public function matches(AbstractVersion $version)
    {
        $expr = new AndExpression(array(
            new GreaterThanOrEqualExpression($this->version),
            new LessThanExpression($this->version->increment(Version::MAJOR)->setPreRelease('0')),
        ));

        return $expr->matches($version);
    }

    public function __toString()
    {
        return '^'.$this->version;
    }
}
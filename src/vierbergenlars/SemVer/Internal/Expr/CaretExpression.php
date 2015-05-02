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

    /**
     * @var ExpressionInterface
     */
    private $normalized;

    public function __construct(PartialVersion $version)
    {
        $this->version = $version;
        $this->normalized = new AndExpression(array(
            new GreaterThanOrEqualExpression($this->version),
            new LessThanExpression($this->version->increment(Version::MAJOR)->setPreRelease('0')),
        ));
    }

    public function matches(AbstractVersion $version)
    {
        return $this->normalized->matches($version);
    }

    public function __toString()
    {
        return '^'.$this->version;
    }

    public function getNormalized()
    {
        return $this->normalized->getNormalized();
    }
}

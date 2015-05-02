<?php

namespace vierbergenlars\SemVer\Internal\Expr;

use vierbergenlars\SemVer\Internal\Version;
use vierbergenlars\SemVer\Internal\PartialVersion;
use vierbergenlars\SemVer\Internal\AbstractVersion;

class SquiggleExpression implements ExpressionInterface
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

        if($this->version->getMajor() === null) {
            $this->normalized = new AnyExpression();
        } elseif($this->version->getMinor() === null) {
            $this->normalized = new AndExpression(array(
                new GreaterThanOrEqualExpression($this->version->unsetToZero()),
                new LessThanExpression($this->version->increment(Version::MAJOR))
            ));
        } elseif($this->version->getPatch() === null) {
            $this->normalized = new AndExpression(array(
                new GreaterThanOrEqualExpression($this->version->unsetToZero()),
                new LessThanExpression($this->version->increment(Version::MINOR)),
            ));
        } else {
            $this->normalized = new AndExpression(array(
                new GreaterThanOrEqualExpression($this->version->unsetToZero()),
                new LessThanExpression($this->version->increment(Version::MINOR)),
            ));
        }
    }

    public function matches(AbstractVersion $version)
    {

        return $this->normalized->matches($version);
    }

    public function __toString()
    {
        return '~'.$this->version;
    }
    public function getNormalized()
    {
        return $this->normalized->getNormalized();
    }

}

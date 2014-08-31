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

    public function __construct(PartialVersion $version)
    {
        $this->version = $version;
    }

    public function matches(AbstractVersion $version)
    {
        /*if($this->version->getMajor() === null) { // ~
            return true;
        }
        if($this->version->getMinor() === null) { // ~2
             return $this->version->getMajor() === $version->getMajor();
        }
        if($this->version->getPatch() === null) { // ~2.1
            if($this->version->getMajor() !== $version->getMajor()) { // 1.2.0, 3.5.0
                return false;
            } else { // 2.0.0, 2.1.0, 2.2.0, 2.3.0
                return $this->version->getMinor() === $version->getMinor();
            }
        }

        // ~2.1.5
        if($this->version->getMajor() !== $version->getMajor()) { // 3.2.1
            return false;
        }
        if($this->version->getMinor() !== $version->getMinor()) { // 2.0.0, 2.2.2
            return false;
        }
        if($this->version->getPatch() > $version->getPatch()) { // 2.1.2, 2.1.4
            return false;
        }
        return $this->version->compare($version) < 1; // Compare prerelease versions*/

        $expr = new AndExpression(array(
            new GreaterThanOrEqualExpression($this->version),
            new LessThanExpression($this->version->increment(Version::MINOR)->setPreRelease('0')),
        ));

        return $expr->matches($version);
    }

    public function __toString()
    {
        return '~'.$this->version;
    }
}
<?php

namespace vierbergenlars\SemVer\Internal;

use vierbergenlars\SemVer\Internal\Expr\ExpressionInterface;
use vierbergenlars\SemVer\Internal\Expr\XRangeExpression;

class PartialVersion extends AbstractVersion implements ExpressionInterface
{
    public function __construct($M, $m, $p, array $r, array $b)
    {
        if(self::isX($M)) {
            $M = null;
        }
        if(self::isX($m)) {
            $m = null;
        }
        if(self::isX($p)) {
            $p = null;
        }
        parent::__construct($M, $m, $p, $r, $b);
    }

    public static function fromVersion($version, $loose = false)
    {
        return self::create($version, $loose, true);
    }

    public function matches(AbstractVersion $version)
    {
        if($this->M === null || $this->m === null || $this->p === null) {
            $expr = new XRangeExpression($this->M === null?'x':(int)$this->M, $this->m === null?'x':(int)$this->m, $this->p===null?'x':(int)$this->p);
            return $expr->matches($version);
        } else {
            return (string)$this === (string)$version;
        }
    }

    public function getNormalized()
    {
      return (string)$this;
    }
}

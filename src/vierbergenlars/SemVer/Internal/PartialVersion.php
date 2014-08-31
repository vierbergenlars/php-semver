<?php

namespace vierbergenlars\SemVer\Internal;

class PartialVersion extends AbstractVersion
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
}
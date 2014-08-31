<?php

namespace vierbergenlars\SemVer\Internal;

class Version extends AbstractVersion
{
    static public function fromVersion($version, $loose = false)
    {
        return self::create($version, $loose, false);
    }

    public static function cmp(Version $v1, Version $v2) {
        return $v1->compare($v2);
    }
}
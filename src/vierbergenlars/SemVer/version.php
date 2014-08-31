<?php

namespace vierbergenlars\SemVer;

use vierbergenlars\SemVer\Internal\Version as IVersion;
use vierbergenlars\Semver\Internal\Expr;

class version
{
    /**
     *
     * @var IVersion
     */
    private $version;

    /**
     * Initializes the version object with a simple version
     * @param  string          $version A simple, single version string
     * @param  bool            $loose
     * @throws SemVerException
     */
    public function __construct($version, $loose = false)
    {
        $this->version = IVersion::fromVersion($version, $loose);
    }

    /**
     * Get the full version
     * @return string
     */
    public function getVersion()
    {
        return (string) $this->version;
    }

    /**
     * Get the major version number
     * @return int
     */
    public function getMajor()
    {
        return $this->version->getMajor();
    }

    /**
     * Get the minor version number
     * @return int
     */
    public function getMinor()
    {
        return $this->version->getMinor();
    }

    /**
     * Get the patch version number
     * @return int
     */
    public function getPatch()
    {
        return $this->version->getPatch();
    }

    /**
     * Get the build number
     * @return array
     */
    public function getBuild()
    {
        return $this->version->getBuild();
    }

    /**
     * Get the prerelease appended to the version
     * @return array
     */
    public function getPrerelease()
    {
        return $this->version->getPreRelease();
    }

    /**
     * Returns a valid version
     * @return string
     * @see self::getVersion()
     */
    public function valid()
    {
        return $this->getVersion();
    }

    /**
     * Increment the version number
     * @param  string                         $what One of 'major', 'minor', 'patch' or 'prerelease'
     * @return \vierbergenlars\SemVer\version
     * @throws SemVerException                When an invalid increment value is given
     */
    public function inc($what)
    {
        switch($what) {
            case 'major':
                $this->version = $this->version->increment(IVersion::MAJOR);
                break;
            case 'minor':
                $this->version = $this->version->increment(IVersion::MINOR);
                break;
            case 'patch':
                $this->version = $this->version->increment(IVersion::PATCH);
                break;
            case 'prerelease':
                $this->version = $this->version->increment(IVersion::PRERELEASE);
                break;
            default:
              throw new SemVerException(sprintf('Invalid increment value %s', $what));
        }
        return $this;
    }

    /**
     * Checks whether this version satisfies an expression
     * @param  expression $versions The expression to check against
     * @return bool
     */
    public function satisfies($versions)
    {
        if(!$versions instanceof expression)
            $versions = new expression($versions);
        return $versions->satisfiedBy($this);
    }

    public function __toString()
    {
        return $this->getVersion();
    }

    /**
     * Compare two versions
     * @param  string                   $v1  The first version
     * @param  string                   $cmp The comparator, one of '==', '!=', '>', '>=', '<', '<=', '===', '!=='
     * @param  string                   $v2  The second version
     * @param  bool                     $loose
     * @return bool
     * @throws UnexpectedValueException
     */
    public static function cmp($v1, $cmp, $v2, $loose = false)
    {
        if(!$v1 instanceof self)
          $v1 = new static($v1, $loose);
        if(!$v2 instanceof self)
          $v2 = new static($v2, $loose);

        $v1 = $v1->_getInternalVersion();
        $v2 = $v2->_getInternalVersion();

        switch($cmp) {
            case '==':
            case '===':
                return (string)$v1 === (string)$v2;
            case '!=':
            case '!==':
                return (string)$v1 !== (string)$v2;
            case '>':
                $expr = new Expr\GreaterThanExpression($v1);
                break;
            case '>=':
                $expr = new Expr\GreaterThanOrEqualExpression($v1);
                break;
            case '<':
                $expr = new Expr\LessThanExpression($v1);
                break;
            case '<=':
                $expr = new Expr\LessThanOrEqualExpression($v1);
                break;
            default:
                throw new \UnexpectedValueException(sprintf('Invalid comparator %s', $cmp));
        }
        return $expr->matches($v2);
    }

    /**
     * Checks ifa given string is greater than another
     * @param  string|version $v1 The first version
     * @param  string|version $v2 The second version
     * @param  bool           $loose
     * @return boolean
     */
    public static function gt($v1, $v2, $loose = false)
    {
        return self::cmp($v1, '>', $v2, $loose);
    }

    /**
     * Checks ifa given string is greater than, or equal to another
     * @param  string|version $v1 The first version
     * @param  string|version $v2 The second version
     * @param  bool           $loose
     * @return boolean
     */
    public static function gte($v1, $v2, $loose = false)
    {
        return self::cmp($v1, '>=', $v2, $loose);
    }

    /**
     * Checks ifa given string is less than another
     * @param  string|version $v1 The first version
     * @param  string|version $v2 The second version
     * @param  bool           $loose
     * @return boolean
     */
    public static function lt($v1, $v2, $loose = false)
    {
        return self::cmp($v1, '<', $v2, $loose);
    }

    /**
     * Checks ifa given string is less than, or equal to another
     * @param  string|version $v1 The first version
     * @param  string|version $v2 The second version
     * @param  bool           $loose
     * @return boolean
     */
    public static function lte($v1, $v2, $loose = false)
    {
        return self::cmp($v1, '<=', $v2, $loose);
    }

    /**
     * Checks ifa given string is equal to another
     * @param  string|version $v1 The first version
     * @param  string|version $v2 The second version
     * @param  bool           $loose
     * @return boolean
     */
    public static function eq($v1, $v2, $loose = false)
    {
        return self::cmp($v1, '==', $v2, $loose);
    }

    /**
     * Checks ifa given string is not equal to another
     * @param  string|version $v1 The first version
     * @param  string|version $v2 The second version
     * @param  bool           $loose
     * @return boolean
     */
    public static function neq($v1, $v2, $loose = false)
    {
        return self::cmp($v1, '!=', $v2, $loose);
    }

    /**
     * Compares two versions, can be used with usort()
     * @param  string|version $v1 The first version
     * @param  string|version $v2 The second version
     * @param  bool           $loose
     * @return int            0 when they are equal, -1 ifthe second version is smaller, 1 ifthe second version is greater
     */
    public static function compare($v1, $v2, $loose = false)
    {
        return self::eq($v1, $v2, $loose)?0:(self::gt($v1, $v2, $loose)?1:-1);
    }

    /**
     * Reverse compares two versions, can be used with usort()
     * @param  string|version $v1 The first version
     * @param  string|version $v2 The second version
     * @param  bool           $loose
     * @return int            0 when they are equal, 1 ifthe second version is smaller, -1 ifthe second version is greater
     */
    public static function rcompare($v1, $v2, $loose = false)
    {
        return self::compare($v2, $v1, $loose);
    }

    /**
     * @internal
     */
    public function _getInternalVersion()
    {
      return $this->version;
    }
}

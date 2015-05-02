<?php

namespace vierbergenlars\SemVer;

use vierbergenlars\SemVer\Internal\LooseSemVerParser;
use vierbergenlars\SemVer\Internal\SemVerParser;
use vierbergenlars\SemVer\Internal\Expr\ExpressionInterface;

class expression
{
    /**
     *
     * @var ExpressionInterface
     */
    private $expression;

    /**
     * standardizes the comparator/range/whatever-string to chunks
     * @param string $versions
     */
    public function __construct($versions, $loose = false)
    {
        try {
            $parser = $loose?new LooseSemVerParser:new SemVerParser;
            $this->expression = $parser->parse($versions);
        } catch(\Exception $e) {
            throw new SemVerException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Checks ifthis range is satisfied by the given version
     * @param  version $version
     * @return boolean
     */
    public function satisfiedBy(version $version)
    {
        return $this->expression->matches($version->_getInternalVersion());
    }

    /**
     * Get the whole or object as a string
     * @return string
     */
    public function getString()
    {
        return (string)$this->expression;
    }

    /**
     * Get the object as an expression
     * @return string
     */
    public function __toString()
    {
        return $this->getString();
    }

    /**
     * Get the object as a range expression
     * @return string
     */
    public function validRange()
    {
        return $this->getString();
    }

    /**
     * Find the maximum satisfying version
     * @param  array|string                        $versions An array of version objects or version strings, one version string
     * @return \vierbergenlars\SemVer\version|null
     */
    public function maxSatisfying($versions, $loose = false)
    {
        if(!is_array($versions))
            $versions = array($versions);
        $versions = array_map(function($version)use($loose) {
            return ($version instanceof version)?$version:new version($version, $loose);
        });
        $expr = $this->expression;
        $matching = array_filter($versions, function(version $version) use($expr) {
            return $expr->matches($version);
        });
        usort($matching, array('vierbergenlars\SemVer\version','rcompare'));
        return current($matching);
    }

    public function getNormalized()
    {
      return $this->expression->getNormalized();
    }
}

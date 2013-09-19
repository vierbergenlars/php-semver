<?php

namespace vierbergenlars\SemVer;

class version extends expression {

    private $version = '0.0.0';
    private $major = '0';
    private $minor = '0';
    private $patch = '0';
    private $build = '';
    private $prtag = '';

    /**
     * Initializes the version object with a simple version
     * @param string $version A simple, single version string
     * @param bool $padZero Set empty version pieces to zero?
     * @throws SemVerException 
     */
    function __construct($version, $padZero=false) {
        $version = (string) $version;
        $expression = sprintf(parent::$dirty_regexp_mask, parent::$global_single_version);
      	if(!preg_match($expression, $version, $matches)) {
            throw new SemVerException('This is not a valid version');
	}

        parent::matchesToVersionParts($matches, $this->major, $this->minor, $this->patch, $this->build, $this->prtag, $padZero?0:null);

        if($this->build === '') 
        $this->build = null;
	$this->version = parent::constructVersionFromParts($padZero, $this->major, $this->minor, $this->patch, $this->build, $this->prtag);

        if ($this->major === null)
            $this->major = -1;
        if ($this->minor === null)
            $this->minor = -1;
        if ($this->patch === null)
            $this->patch = -1;
        if ($this->build === null)
            $this->build = -1;

    }



    /**
     * Get the full version
     * @return string 
     */
    function getVersion() {
        return (string)$this->version;
    }

    /**
     * Get the major version number
     * @return int
     */
    function getMajor() {
        return (int) $this->major;
    }

    /**
     * Get the minor version number
     * @return int
     */
    function getMinor() {
        return (int) $this->minor;
    }

    /**
     * Get the patch version number
     * @return int
     */
    function getPatch() {
        return (int) $this->patch;
    }

    /**
     * Get the build number
     * @return int
     */
    function getBuild() {
        return (int) $this->build;
    }

    /**
     * Get the tag appended to the version
     * @return int
     */
    function getTag() {
        return (string) $this->prtag;
    }

    /**
     * Returns a valid version
     * @return string
     * @see self::getVersion()
     */
    function valid() {
        return $this->getVersion();
    }

    /**
     * Increment the version number
     * @param string $what One of 'major', 'minor', 'patch' or 'build'
     * @return \vierbergenlars\SemVer\version
     * @throws SemVerException When an invalid increment value is given
     */
    function inc($what) {
        if ($what == 'major')
            return new version(($this->major + 1) . '.0.0');
        if ($what == 'minor')
            return new version($this->major . '.' . ($this->minor + 1) . '.0');
        if ($what == 'patch')
            return new version($this->major . '.' . $this->minor . '.' . ($this->patch + 1));
        if ($what == 'build') {
            if ($this->build == -1)
                return new version($this->major . '.' . $this->minor . '.' . $this->patch . '-1');
            return new version($this->major . '.' . $this->minor . '.' . $this->patch . '-' . ($this->build + 1));
        }
        throw new SemVerException('Invalid increment value given', $what);
    }

    /**
     * Checks whether this version satisfies an expression
     * @param expression $versions The expression to check against
     * @return bool 
     */
    function satisfies(expression $versions) {
        return $versions->satisfiedBy($this) !== false;
    }
    
    function __toString() {
        return $this->version;
    }

    /**
     * Compare two versions
     * @param string $v1 The first version
     * @param string $cmp The comparator, one of '==', '!=', '>', '>=', '<', '<=', '===', '!=='
     * @param string $v2 The second version
     * @return bool
     * @throws UnexpectedValueException 
     */
    static function cmp($v1, $cmp, $v2) {
        switch ($cmp) {
            case '==': return self::eq($v1, $v2);
            case '!=': return self::neq($v1, $v2);
            case '>': return self::gt($v1, $v2);
            case '>=': return self::gte($v1, $v2);
            case '<': return self::lt($v1, $v2);
            case '<=': return self::lte($v1, $v2);
            case '===': return $v1 === $v2;
            case '!==': return $v1 !== $v2;
            default: throw new UnexpectedValueException('Invalid comparator');
        }
    }

    /**
     * Checks if a given string is greater than another
     * @param string|version $v1 The first version
     * @param string|version $v2 The second version
     * @return boolean 
     */
    static function gt($v1, $v2) {
        $v1 = new version($v1);
        $v2 = new version($v2);

        $ma1 = $v1->getMajor();
        $ma2 = $v2->getMajor();

        if($ma1 < 0 &&$ma2 >= 0)
            return false;
        if($ma1 >=0 && $ma2 <0)
            return true; 
        if ($ma1 > $ma2)
            return true;
        if ($ma1 < $ma2)
            return false;

        $mi1 = $v1->getMinor();
        $mi2 = $v2->getMinor();
        
        if($mi1 < 0 &&$mi2 >= 0)
            return false;
        if($mi1 >=0 && $mi2 <0)
            return true; 
        if ($mi1 > $mi2)
            return true;
        if ($mi1 < $mi2)
            return false;

        $p1 = $v1->getPatch();
        $p2 = $v2->getPatch();
        
        if($p1 < 0 &&$p2 >= 0)
            return false;
        if($p1 >=0 && $p2 <0)
            return true; 
        if ($p1 > $p2)
            return true;
        if ($p1 < $p2)
            return false;
            
        $b1 = $v1->getBuild();
        $b2 = $v2->getBuild();

        if($b1 < 0 &&$b2 >= 0)
            return false;
        if($b1 >=0 && $b2 <0)
            return true; 
        if ($b1 > $b2)
            return true;
        if ($b1 < $b2)
            return false;

        if ($v1->getTag() === '' && $v2->getTag() === '')
            return false;
        if ($v1->getTag() === '' && $v2->getTag() !== '')
            return true; //v1 has no tag, v2 has tag
        if ($v1->getTag() !== '' && $v2->getTag() === '')
            return false; //v1 has tag, v2 has no tag
         
        // both have tags, sort them naturally to see which one is greater.
        $array = array($v1->getTag(), $v2->getTag());
        natsort($array);
        return reset($array) != $v1->getTag();
        
    }

    /**
     * Checks if a given string is greater than, or equal to another
     * @param string|version $v1 The first version
     * @param string|version $v2 The second version
     * @return boolean 
     */
    static function gte($v1, $v2) {
        return self::gt($v1, $v2)||self::eq($v1, $v2);
    }

    /**
     * Checks if a given string is less than another
     * @param string|version $v1 The first version
     * @param string|version $v2 The second version
     * @return boolean 
     */
    static function lt($v1, $v2) {
        return self::gt($v2, $v1);
    }

    /**
     * Checks if a given string is less than, or equal to another
     * @param string|version $v1 The first version
     * @param string|version $v2 The second version
     * @return boolean 
     */
    static function lte($v1, $v2) {
        return self::lt($v1, $v2)||self::eq($v1, $v2);
    }

    /**
     * Checks if a given string is equal to another
     * @param string|version $v1 The first version
     * @param string|version $v2 The second version
     * @return boolean 
     */
    static function eq($v1, $v2) {
        $v1 = new version($v1, true);
        $v2 = new version($v2, true);
        return $v1->getVersion() == $v2->getVersion();
    }

    /**
     * Checks if a given string is not equal to another
     * @param string|version $v1 The first version
     * @param string|version $v2 The second version
     * @return boolean 
     */
    static function neq($v1, $v2) {
        return !self::eq($v1, $v2);
    }

    /**
     * Compares two versions, can be used with usort()
     * @param string|version $v1 The first version
     * @param string|version $v2 The second version
     * @return int 0 when they are equal, -1 if the second version is smaller, 1 if the second version is greater
     */
    static function compare($v1, $v2) {
        $g = self::gt($v1, $v2);
        if ($g === null)
            return 0;
        if ($g)
            return 1;
        return -1;
    }

    /**
     * Reverse compares two versions, can be used with usort()
     * @param string|version $v1 The first version
     * @param string|version $v2 The second version
     * @return int 0 when they are equal, 1 if the second version is smaller, -1 if the second version is greater
     */
    static function rcompare($v1, $v2) {
        return self::compare($v2, $v1);
    }

}

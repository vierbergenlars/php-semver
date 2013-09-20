<?php

namespace vierbergenlars\SemVer;

class expression {
    static protected $global_single_version = '(([0-9]+)(\\.([0-9]+)(\\.([0-9]+)(-([0-9]+))?(-?([a-zA-Z-+][a-zA-Z0-9\\.\\-:]*)?)?)?)?)';
    static protected $global_single_xrange = '(([0-9]+|[xX*])(\\.([0-9]+|[xX*])(\\.([0-9]+|[xX*])(-([0-9]+))?(-?([a-zA-Z-+][a-zA-Z0-9\\.\\-:]*)?)?)?)?)';
    static protected $global_single_comparator = '([<>]=?)?\\s*';
    static protected $global_single_spermy = '(~?)>?\\s*';
    static protected $range_mask = '%1$s\\s+-\\s+%1$s';
    static protected $regexp_mask = '/%s/';
    static protected $dirty_regexp_mask = '/^[v= ]*%s$/';
    private $chunks = array();

    /**
     * standardizes the comparator/range/whatever-string to chunks
     * @param string $versions
     */
    function __construct($versions) {
        $versions = preg_replace(sprintf(self::$dirty_regexp_mask, self::$global_single_comparator . '(\\s+-\\s+)?' . self::$global_single_xrange), '$1$2$3', $versions); //Paste comparator and version together
        $versions = preg_replace('/\\s+/', ' ', $versions); //Condense multiple spaces to one
        $versions = str_replace(array('*', 'X'), 'x', $versions); // All the same wildcards, plz
        if (strstr($versions, ' - '))
            $versions = self::rangesToComparators($versions); //Replace all ranges with comparators
        if (strstr($versions, '~'))
            $versions = self::spermiesToComparators($versions); //Replace all spermies with comparators
        if(strstr($versions, 'x') && (strstr($versions, '<')|| strstr($versions, '>')))
            $versions = self::compAndxRangesToComparators($versions);
        if (strstr($versions, 'x'))
            $versions = self::xRangesToComparators($versions); //Replace all x-ranges with comparators
        $or = explode('||', $versions);
        foreach ($or as &$orchunk) {
            $and = explode(' ', trim($orchunk));
            foreach ($and as $order => &$achunk) {
                $achunk = self::standardizeSingleComparator($achunk);
                if (strstr($achunk, ' ')) {
                    $pieces = explode(' ', $achunk);
                    unset($and[$order]);
                    $and = array_merge($and, $pieces);
                }
            }
            $orchunk = $and;
        }
        $this->chunks = $or;
    }

    /**
     * Checks if this range is satisfied by the given version
     * @param version $version
     * @return boolean
     */
    function satisfiedBy(version $version) {
        $version1 = $version->getVersion();
        $expression = sprintf(self::$regexp_mask, self::$global_single_comparator . self::$global_single_version);
        $ok = false;
        foreach ($this->chunks as $orblocks) { //Or loop
            foreach ($orblocks as $ablocks) { //And loop
                $matches = array();
                preg_match($expression, $ablocks, $matches);
                $comparators = $matches[1];
                $version2 = $matches[2];
                if ($comparators === '')
                    $comparators = '=='; //Use equal if no comparator is set
                if (!version::cmp($version1, $comparators, $version2)) { //If one chunk of the and-loop does not match...
                    $ok = false; //It is not okay
                    break; //And this loop will surely fail: return to or-loop
                } else {
                    $ok = true;
                }
            }
            if ($ok)
                return true; //Only one or block has to match
        }
        return false; //No matches found :(
    }

    /**
     * Get the raw data blocks
     * @return array
     */
    function getChunks() {
        return $this->chunks;
    }

    /**
     * Get the raw data block at a given offset
     * @param int $x
     * @param int $y
     * @return string
     */
    function getChunk($x, $y) {
        return $this->chunks[$x][$y];
    }

    /**
     * Get the whole or object as a string
     * @return string
     */
    function getString() {
        $or = $this->chunks;
        foreach ($or as &$orchunk) {
            $orchunk = implode(' ', $orchunk);
        }
        return implode('||', $or);
    }

    /**
     * Get the object as an expression
     * @return string
     */
    function __toString() {
        return $this->getString();
    }

    /**
     * Get the object as a range expression
     * @return string
     */
    function validRange() {
        return $this->getString();
    }

    /**
     * Find the maximum satisfying version
     * @param array|string $versions An array of version objects or version strings, one version string
     * @return \vierbergenlars\SemVer\version|boolean
     */
    function maxSatisfying($versions) {
        if (!is_array($versions))
            $versions = array($versions);
        usort($versions, __NAMESPACE__ . '\\version::rcompare');
        foreach ($versions as $version) {
            try {
                if (!is_a($version, 'version'))
                    $version = new version($version);
            } catch (SemVerException $e) {
                continue;
            }
            if ($version->satisfies($this))
                return $version;
        }
        return false;
    }

    /**
     * standardizes a single version
     * @param string $version
     * @param bool $padZero Set to true if the version string should be padded with zeros instead of x-es
     * @throws SemVerException
     * @return string
     */
    static function standardize($version, $padZero = false) {
        $matches = array();
        $expression = sprintf(self::$dirty_regexp_mask, self::$global_single_version);
        if (!preg_match($expression, $version, $matches))
            throw new SemVerException('Invalid version string given', $version);
        if ($padZero) { //If there is a comparator drop undefined parts
            self::matchesToVersionParts($matches, $major, $minor, $patch, $build, $prtag, null);
            if($build === '') $build = null;
            if($prtag === '') $prtag = null;
            return self::constructVersionFromParts(false, $major, $minor, $patch, $build, $prtag);
        }
        else { //If it is just a number, convert to a range
            self::matchesToVersionParts($matches, $major, $minor, $patch, $build, $prtag, 'x');
            if($build === '') $build = null;
            if($prtag === '') $prtag = null;
            $version = self::constructVersionFromParts(false, $major, $minor, $patch, $build, $prtag);
            return self::xRangesToComparators($version);
        }
    }
    
    /**
     * standardizes a single version (typeo'd version for BC)
     * @deprecated 2.1.0
     * @param string $version
     * @param bool $padZero Set to true if the version string should be padded with zeros instead of x-es
     * @throws SemVerException
     * @return string
     */
    static function standarize($version, $padZero = false) {
        return self::standardize($version, $padZero);
    }

    /**
     * standardizes a single version with comparators
     * @param string $version
     * @throws SemVerException
     * @return string
     */
    static protected function standardizeSingleComparator($version) {
        $expression = sprintf(self::$regexp_mask, self::$global_single_comparator . self::$global_single_version);
        $matches = array();
        if (!preg_match($expression, $version, $matches))
            throw new SemVerException('Invalid version string given', $version);
        $comparators = $matches[1];
        $version = $matches[2];
        $hasComparators = true;
        if ($comparators === '')
            $hasComparators = false;
        $version = self::standardize($version, $hasComparators);
        return $comparators . $version;
    }

    /**
     * standardizes a bunch of versions with comparators
     * @param string $versions
     * @return string
     */
    static protected function standardizeMultipleComparators($versions) {
        $versions = preg_replace('/' . self::$global_single_comparator . self::$global_single_xrange . '/', '$1$2', $versions); //Paste comparator and version together
        $versions = preg_replace('/\\s+/', ' ', $versions); //Condense multiple spaces to one
        $or = explode('||', $versions);
        foreach ($or as &$orchunk) {
            $orchunk = trim($orchunk); //Remove spaces
            $and = explode(' ', $orchunk);
            foreach ($and as &$achunk) {
                $achunk = self::standardizeSingleComparator($achunk);
            }
            $orchunk = implode(' ', $and);
        }
        $versions = implode('||', $or);
        return $versions;
    }

    /**
     * standardizes a bunch of version ranges to comparators
     * @param string $range
     * @throws SemVerException
     * @return string
     */
    static protected function rangesToComparators($range) {
        $range_expression = sprintf(self::$range_mask, self::$global_single_version);
        $expression = sprintf(self::$regexp_mask, $range_expression);
        if (!preg_match($expression, $range, $matches))
            throw new SemVerException('Invalid range given', $range);
        $versions = preg_replace($expression, '>=$1 <=$11', $range);
        $versions = self::standardizeMultipleComparators($versions);
        return $versions;
    }

    /**
     * standardizes a bunch of x-ranges to comparators
     * @param string $ranges
     * @return string
     */
    static protected function xRangesToComparators($ranges) {
        $expression = sprintf(self::$regexp_mask, self::$global_single_xrange);
        return preg_replace_callback($expression, array('self', 'xRangesToComparatorsCallback'), $ranges);
    }

    /**
     * Callback for xRangesToComparators()
     * @internal
     * @param array $matches
     * @return string
     */
    static private function xRangesToComparatorsCallback($matches) {
        self::matchesToVersionParts($matches, $major, $minor, $patch, $build, $prtag, 'x');
        if ($build !== '')
            $build = '-' . $build;
        if ($major === 'x')
            return '>=0';
        if ($minor === 'x')
            return '>=' . $major . ' <' . ($major + 1) . '.0.0-';
        if ($patch === 'x')
            return '>=' . $major . '.' . $minor . ' <' . $major . '.' . ($minor + 1) . '.0-';
        return $major . '.' . $minor . '.' . $patch . $build . $prtag;
    }

    /**
     * standardizes a bunch of ~-ranges to comparators
     * @param string $spermies
     * @return string
     */
    static protected function spermiesToComparators($spermies) {
        $expression = sprintf(self::$regexp_mask, self::$global_single_spermy . self::$global_single_xrange);
        return preg_replace_callback($expression, array('self', 'spermiesToComparatorsCallback'), $spermies);
    }

    /**
     * Callback for spermiesToComparators()
     * @internal
     * @param unknown_type $matches
     * @return string
     */
    static private function spermiesToComparatorsCallback($matches) {
        self::matchesToVersionParts($matches, $major, $minor, $patch, $build, $prtag, 'x', 3);
        if ($build !== '')
            $build = '-' . $build;
        if ($major === 'x')
            return '>=0';
        if ($minor === 'x')
            return '>=' . $major . ' <' . ($major + 1) . '.0.0-';
        if ($patch === 'x')
            return '>=' . $major . '.' . $minor . ' <' . $major . '.' . ($minor + 1) . '.0-';
        return '>=' . $major . '.' . $minor . '.' . $patch . $build . $prtag . ' <' . $major . '.' . ($minor + 1) . '.0-';
    }

    /**
     * Standarizes a bunch of x-ranges with comparators in front of them to comparators
     *
     * @param string $versions
     * @return string
     */
    static private function compAndxRangesToComparators($versions) {
        $regex = sprintf(self::$regexp_mask, self::$global_single_comparator.self::$global_single_xrange);
        return preg_replace_callback($regex, array('self', 'compAndxRangesToComparatorsCallback'), $versions);
    }

    /**
     * Callback for compAndxRangesToComparators()
     *
     * @internal
     * @param array $matches
     * @return string
     */
    static private function compAndxRangesToComparatorsCallback($matches) {
        $comparators = $matches[1];
        self::matchesToVersionParts($matches, $major, $minor, $patch, $build, $prtag, 'x', 3);
        if($comparators[0] === '<') {
            if($major === 'x')
                return $comparators.'0';
            if($minor === 'x')
                return $comparators.$major.'.0';
            if($patch === 'x')
                return $comparators.$major.'.'.$minor.'.0';
            return $comparators.self::constructVersionFromParts(false, $major, $minor, $patch, $build, $prtag);
        }
        elseif($comparators[0] === '>') {
            return $comparators.self::constructVersionFromParts(false, ($major==='x'?0:$major), ($minor==='x'?0:$minor), ($patch==='x'?0:$patch), $build, $prtag);
        }
    }

    /**
     * Converts matches to named version parts
     * @param array $matches Matches array from preg_match
     * @param int|string $major Reference to major version
     * @param int|string $minor Reference to minor version
     * @param int|string $patch Reference to patch version
     * @param int|string $build Reference to build number
     * @param int|string $prtag Reference to pre-release tags
     * @param int|string $default Default value for a version if not found in matches array
     * @param int $offset The position of the raw occurrence of the major version number
     */
    static protected function matchesToVersionParts($matches, &$major, &$minor, &$patch, &$build, &$prtag, $default = 0, $offset = 2) {
        $major = $minor = $patch = $default;
        $build = '';
        $prtag = '';
        switch (count($matches)) {
            default:
            case $offset + 8: $prtag = $matches[$offset + 7];
            case $offset + 7: $build = $matches[$offset + 6];
            case $offset + 6:
            case $offset + 5: $patch = $matches[$offset + 4];
            case $offset + 4:
            case $offset + 3: $minor = $matches[$offset + 2];
            case $offset + 2:
            case $offset + 1: $major = $matches[$offset];
            case $offset:
            case 0:
        }
        if (is_numeric($build))
            $build = intval($build);
        if (is_numeric($patch))
            $patch = intval($patch);
        if (is_numeric($minor))
            $minor = intval($minor);
        if (is_numeric($major))
            $major = intval($major);
    }

    /**
     * Converts all parameters to a version string
     * @param bool $padZero Pad the missing version parts with zeroes or not?
     * @param int $ma The major version number
     * @param int $mi The minor version number
     * @param int $p The patch number
     * @param int $b The build number
     * @param int $t The version tag
     */
    static protected function constructVersionFromParts($padZero = true, $ma=null, $mi=null, $p=null, $b=null, $t=null) {
        if($padZero) {
            if($ma === null) return '0.0.0';
            if($mi===null) return $ma.'.0.0';
            if($p===null) return $ma.'.'.$mi.'.0';
            if($b===null&&$t===null) return $ma.'.'.$mi.'.'.$p;
	        if($b!==null&&$t===null) return $ma.'.'.$mi.'.'.$p.'-'.$b;
            if($b===null&&$t!==null) return $ma.'.'.$mi.'.'.$p.$t;
            if($b!==null&&$t!==null) return $ma.'.'.$mi.'.'.$p.'-'.$b.$t;
        }
        else {
            if($ma === null) return '';
            if($mi===null) return $ma.'';
            if($p===null) return $ma.'.'.$mi.'';
            if($b===null&&$t===null) return $ma.'.'.$mi.'.'.$p;
	        if($b!==null&&$t===null) return $ma.'.'.$mi.'.'.$p.'-'.$b;
            if($b===null&&$t!==null) return $ma.'.'.$mi.'.'.$p.$t;
            if($b!==null&&$t!==null) return $ma.'.'.$mi.'.'.$p.'-'.$b.$t;
        }
    }

}

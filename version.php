<?php
class versionExpression {
	const version='0.1.0';
	static protected $global_single_version='(([0-9]+)(\\.([0-9]+)(\\.([0-9]+))?)?)';
	static protected $global_single_xrange='(([0-9xX*]+)(\\.([0-9xX*]+)(\\.([0-9xX*]+))?)?)';
	static protected $global_single_comparator='([<>]=?)?\\s*';
	static protected $range_mask='%1$s\\s+-\\s+%1$s';
	static protected $regexp_mask='/%s/';
	private $chunks=array();
	/**
	 * standarizes the comparator/range/whatever-string to chunks
	 * Enter description here ...
	 * @param unknown_type $versions
	 */
	function __construct($versions) {
		$versions=preg_replace('/'.self::$global_single_comparator.'(\\s+-\\s+)?'.self::$global_single_xrange.'/','$1$2$3',$versions); //Paste comparator and version together
		$versions=preg_replace('/\\s+/', ' ', $versions); //Condense multiple spaces to one
		if(strstr($versions, '-')) $versions=self::rangesToComparators($versions); //Replace all ranges with comparators
		if(strstr($versions, 'x')||strstr($versions,'X')||strstr($versions,'*')) $versions=self::xRangesToComparators($versions); //Replace all x-ranges with comparators
		$or=explode('||', $versions);
		foreach($or as &$orchunk) {
			$orchunk=trim($orchunk); //Remove spaces
			$and=explode(' ', $orchunk);
			foreach($and as &$achunk) {
				$achunk=self::standarizeSingleComparator($achunk);
			}
			$orchunk=$and;
		}
		$this->chunks=$or;
	}
	function satisfiedBy(version $version) {
		$version1=$version->getString();
		$expression=sprintf(self::$regexp_mask,self::$global_single_comparator.self::$global_single_version);
		$ok=false;
		foreach($this->chunks as $orblocks) { //Or loop
			foreach($orblocks as $ablocks) { //And loop
				$matches=array();
				preg_match($expression, $ablocks, $matches);
				$comparators=$matches[1];
				$version2=$matches[2];
				if($comparators=='') $comparators='=='; //Use equal if no comparator is set
				if(!version_compare($version, $version2, $comparators)) { //If one chunk of the and-loop does not match...
					$ok=false; //It is not okay
					break; //And this loop will surely fail: return to or-loop
				}
				else {
					$ok=true;
				}
			}
			if($ok) return true; //Only one or block has to match
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
	 * Get the whole or object as a string
	 * Enter description here ...
	 */
	function getString() {
		$or=$this->chunks;
		foreach($or as &$orchunk) {
			$orchunk=implode(' ',$orchunk);
		}
		return implode('||', $or);
	}
	function __toString() {
		return $this->getString();
	}
	/**
	 * standarizes a single version
	 * @param string $version
	 * @param bool $hasComparator Set to true if the version string has a comparator in front of it
	 * @throws versionException
	 * @return string
	 */
	static function standarize($version,$hasComparator=false) {
		$matches=array();
		$expression=sprintf(self::$regexp_mask,self::$global_single_version);
		if(!preg_match($expression,$version,$matches)) throw new versionException('Invalid version string given');
		if($hasComparator) { //If there is a comparator set undefined parts to 0
			self::matchesToVersionParts($matches, $major, $minor, $patch);
			return $major.'.'.$minor.'.'.$patch;
		}
		else { //If it is just a number, convert to a range
			self::matchesToVersionParts($matches, $major, $minor, $patch, 'x');
			$version=$major.'.'.$minor.'.'.$patch;
			return self::xRangesToComparators($version);
		}
	}
	/**
	 * standarizes a single version with comparators
	 * @param string $version
	 * @throws versionException
	 * @return string
	 */
	static protected function standarizeSingleComparator($version) {
		$expression=sprintf(self::$regexp_mask,self::$global_single_comparator.self::$global_single_version);
		$matches=array();
		if(!preg_match($expression,$version,$matches)) throw new versionException('Invalid version string given');
		$comparators=$matches[1];
		$version=$matches[2];
		$hasComparators=true;
		if($comparators=='') $hasComparators=false;
		$version=self::standarize($version, $hasComparators);
		return $comparators.$version;
	}
	/**
	 * standarizes a bunch of versions with comparators
	 * @param string $versions
	 * @return string
	 */
	static protected function standarizeMultipleComparators($versions) {
		$versions=preg_replace('/'.self::$global_single_comparator.self::$global_single_xrange.'/','$1$2',$versions); //Paste comparator and version together
		$versions=preg_replace('/\\s+/', ' ', $versions); //Condense multiple spaces to one
		$or=explode('||', $versions);
		foreach($or as &$orchunk) {
			$orchunk=trim($orchunk); //Remove spaces
			$and=explode(' ', $orchunk);
			foreach($and as &$achunk) {
				$achunk=self::standarizeSingleComparator($achunk);
			}
			$orchunk=implode(' ',$and);
		}
		$versions=implode('||',$or);
		return $versions;
	}
	/**
	 * standarizes a bunch of version ranges to comparators
	 * @param string $range
	 * @throws versionException
	 * @return string
	 */
	static protected function rangesToComparators($range) {
		$range_expression=sprintf(self::$range_mask,self::$global_single_version);
		$expression=sprintf(self::$regexp_mask,$range_expression);
		if(!preg_match($expression,$range)) throw new versionException('Invalid range given');
		$versions=preg_replace($expression, '>=$1 <$7', $range);
		$versions=self::standarizeMultipleComparators($versions);
		return $versions;
	}
	/**
	 * standarizes a bunch of x-ranges to comparators
	 * @param string $ranges
	 * @return string
	 */
	static protected function xRangesToComparators($ranges) {
		$expression=sprintf(self::$regexp_mask,self::$global_single_xrange);
		return preg_replace_callback($expression, array('self','xRangesToComparatorsCallback'), $ranges);
	}
	/**
	 * Callback for xRangesToComparators()
	 * @internal
	 * @param array $matches
	 */
	static private function xRangesToComparatorsCallback($matches) {
		self::matchesToVersionParts($matches, $major, $minor, $patch);
		$wildcards=array('x','X','*');
		if(in_array($major, $wildcards,true)) return '>=0.0.0';
		if(in_array($minor, $wildcards,true)) return '>='.$major.'.0.0 <'.($major+1).'.0.0';
		if(in_array($patch, $wildcards,true)) return '>='.$major.'.'.$minor.'.0 <'.$major.'.'.($minor+1).'.0';
		return $major.'.'.$minor.'.'.$patch;
	}
	/**
	 * Converts matches to named version parts
	 * @param array $matches
	 * @param string $major
	 * @param string $minor
	 * @param string $patch
	 * @param string $default
	 * @param int $offset
	 */
	static private function matchesToVersionParts($matches, &$major, &$minor, &$patch, $default=0, $offset=2) {
		$major=$minor=$patch=$default;
		switch(count($matches)) {
			case $offset+5: $patch=$matches[$offset+4];
			case $offset+3: $minor=$matches[$offset+2];
			case $offset+1: $major=$matches[$offset];
		}
		if(is_numeric($patch)) $patch=intval($patch);
		if(is_numeric($minor)) $minor=intval($minor);
		if(is_numeric($major)) $major=intval($major);
	}
}
class version extends versionExpression {
	const version='0.1.0';
	function __construct($version) {
		$expression=sprintf(parent::$regexp_mask,parent::$global_single_version);
		if(!preg_match($expression, $version)) throw new versionException('This is not a simple, singular version! No comparators nor ranges allowed!');
		parent::__construct($version);
	}
	function satisfies(versionExpression $versions) {
		return $versions->satisfiedBy($this->getString());
	}
}
class versionException extends Exception {}

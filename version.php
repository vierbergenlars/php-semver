<?php
class versionExpression {
	const version='0.4.0';
	static protected $global_single_version='(([0-9]+)(\\.([0-9]+)(\\.([0-9]+)(-([0-9]+))?)?)?)';
	static protected $global_single_xrange='(([0-9]+|[xX*])(\\.([0-9]+|[xX*])(\\.([0-9]+|[xX*])(-([0-9]+|[xX*]))?)?)?)';
	static protected $global_single_comparator='([<>]=?)?\\s*';
	static protected $global_single_spermy='(~?)>?\\s*';
	static protected $range_mask='%1$s\\s+-\\s+%1$s';
	static protected $regexp_mask='/[v=]*%s/';
	static protected $wildcards=array('x','X','*');
	private $chunks=array();
	/**
	 * standarizes the comparator/range/whatever-string to chunks
	 * Enter description here ...
	 * @param unknown_type $versions
	 */
	function __construct($versions) {
		$versions=preg_replace('/'.self::$global_single_comparator.'(\\s+-\\s+)?'.self::$global_single_xrange.'/','$1$2$3',$versions); //Paste comparator and version together
		$versions=preg_replace('/\\s+/', ' ', $versions); //Condense multiple spaces to one
		if(strstr($versions, ' - ')) $versions=self::rangesToComparators($versions); //Replace all ranges with comparators
		if(strstr($versions,'~')) $versions=self::spermiesToComparators($versions); //Replace all spermies with comparators
		if(strstr($versions, 'x')||strstr($versions,'X')||strstr($versions,'*')) $versions=self::xRangesToComparators($versions); //Replace all x-ranges with comparators
		$or=explode('||', $versions);
		foreach($or as &$orchunk) {
			$orchunk=trim($orchunk); //Remove spaces
			$and=explode(' ', $orchunk);
			foreach($and as $order=>&$achunk) {
				$achunk=self::standarizeSingleComparator($achunk);
				if(strstr($achunk,' ')) {
					$pieces=explode(' ', $achunk);
					unset($and[$order]);
					$and=array_merge($and, $pieces);
				}
			}
			$orchunk=$and;
		}
		$this->chunks=$or;
	}
	function satisfiedBy(version $version) {
		$version1=$version->getVersion();
		$expression=sprintf(self::$regexp_mask,self::$global_single_comparator.self::$global_single_version);
		$ok=false;
		foreach($this->chunks as $orblocks) { //Or loop
			foreach($orblocks as $ablocks) { //And loop
				$matches=array();
				preg_match($expression, $ablocks, $matches);
				$comparators=$matches[1];
				$version2=$matches[2];
				if($comparators==='') $comparators='=='; //Use equal if no comparator is set
				if(!version::cmp($version1, $comparators, $version2)) { //If one chunk of the and-loop does not match...
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
	function getChunk($x,$y) {
		return $this->chunks[$x][$y];
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
			self::matchesToVersionParts($matches, $major, $minor, $patch, $build);
			if($build!=='') $build='-'.$build;
			return $major.'.'.$minor.'.'.$patch.$build;
		}
		else { //If it is just a number, convert to a range
			self::matchesToVersionParts($matches, $major, $minor, $patch, $build, 'x');
			if($build!=='') $build='-'.$build;
			$version=$major.'.'.$minor.'.'.$patch.$build;
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
		if($comparators==='') $hasComparators=false;
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
		$versions=preg_replace($expression, '>=$1 <=$9', $range);
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
	 * @return string
	 */
	static private function xRangesToComparatorsCallback($matches) {
		self::matchesToVersionParts($matches, $major, $minor, $patch, $build, 'x');
		if($build!=='') $build='-'.$build;
		if($major==='x') return '>=0.0.0';
		if($minor==='x') return '>='.$major.'.0.0 <'.($major+1).'.0.0';
		if($patch==='x') return '>='.$major.'.'.$minor.'.0 <'.$major.'.'.($minor+1).'.0';
		if($build==='x') return '>='.$major.'.'.$minor.'.'.$patch.' <'.$major.'.'.$minor.'.'.($patch+1);
		return $major.'.'.$minor.'.'.$patch.$build;
	}
	/**
	 * standarizes a bunch of ~-ranges to comparators
	 * @param string $spermies
	 * @return string
	 */
	static protected function spermiesToComparators($spermies) {
		$expression=sprintf(self::$regexp_mask,self::$global_single_spermy.self::$global_single_xrange);
		return preg_replace_callback($expression, array('self','spermiesToComparatorsCallback'), $spermies);
	}
	/**
	 * Callback for spermiesToComparators()
	 * @internal
	 * @param unknown_type $matches
	 * @return string
	 */
	static private function spermiesToComparatorsCallback($matches) {
		self::matchesToVersionParts($matches, $major, $minor, $patch, $build,'x',3);
		if($build!=='') $build='-'.$build;
		if($major==='x') return '>=0.0.0';
		if($minor==='x') return '>='.$major.'.0.0 <'.($major+1).'.0.0';
		if($patch==='x') return '>='.$major.'.'.$minor.'.0 <'.$major.'.'.($minor+1).'.0';
		if($build==='x') return '>='.$major.'.'.$minor.'.'.$patch.' <'.$major.'.'.($minor+1).'.0';
		return '>='.$major.'.'.$minor.'.'.$patch.$build.' <'.$major.'.'.($minor+1).'.0';
	}
	/**
	 * Converts matches to named version parts, replaces all wildcards by lowercase x
	 * @param array $matches Matches array from preg_match
	 * @param int|string $major Reference to major version
	 * @param int|string $minor Reference to minor version
	 * @param int|string $patch Reference to patch version
	 * @param int|string $default Default value for a version if not found in matches array
	 * @param int $offset The position of the raw occurence of the major version number
	 */
	static private function matchesToVersionParts($matches, &$major, &$minor, &$patch, &$build, $default=0, $offset=2) {
		$major=$minor=$patch=$default;
		$build='';
		switch(count($matches)) {
			case $offset+7: $build=$matches[$offset+6];
			case $offset+5: $patch=$matches[$offset+4];
			case $offset+3: $minor=$matches[$offset+2];
			case $offset+1: $major=$matches[$offset];
		}
		if(is_numeric($build)) $build=intval($build);
		if(is_numeric($patch)) $patch=intval($patch);
		if(is_numeric($minor)) $minor=intval($minor);
		if(is_numeric($major)) $major=intval($major);
		if(in_array($major, self::$wildcards,true)) $major='x';
		if(in_array($minor, self::$wildcards,true)) $minor='x';
		if(in_array($patch, self::$wildcards,true)) $patch='x';
		if(in_array($build, self::$wildcards,true)) $patch='x';
	}
}
class version extends versionExpression {
	private $version='0.0.0';
	private $major='0';
	private $minor='0';
	private $patch='0';
	private $build='0';
	function __construct($version) {
		$expression=sprintf(parent::$regexp_mask,parent::$global_single_version);
		if(!preg_match($expression, $version)) throw new versionException('This is not a simple, singular version! No comparators nor ranges allowed!');
		parent::__construct($version);
		$this->version=$this->getChunk(0, 0);
		@list($this->major,$this->minor,$this->patch,$this->build)=preg_split('/[.-]/', $this->version, 4);
	}
	function getVersion() {
		return $this->version;
	}
	function getMajor() {
		return (int)$this->major;
	}
	function getMinor() {
		return (int)$this->minor;
	}
	function getPatch() {
		return (int)$this->patch;
	}
	function getBuild() {
		return (int)$this->build;
	}
	function satisfies(versionExpression $versions) {
		return $versions->satisfiedBy($this);
	}
	static function cmp($v1,$cmp,$v2) {
		switch($cmp) {
			case '==': return self::eq($v1, $v2);
			case '!=': return self::neq($v1, $v2);
			case '>':  return self::gt($v1, $v2);
			case '>=': return self::gte($v1, $v2);
			case '<': return self::lt($v1, $v2);
			case '<=': return self::lte($v1, $v2);
			case '===': return $v1===$v2;
			case '!==': return $v1!==$v2;
			default: throw new UnexpectedValueException('Invalid comparator');
		}
	}
	static function gt($v1,$v2) {
		$v1=new version($v1);
		$v2=new version($v2);
		if($v1->getMajor() > $v2->getMajor()) return true;
		if($v1->getMajor() < $v2->getMajor()) return false;
		if($v1->getMinor() > $v2->getMinor()) return true;
		if($v1->getMinor() < $v2->getMinor()) return false;
		if($v1->getPatch() > $v2->getPatch()) return true;
		if($v1->getPatch() < $v2->getPatch()) return false;
		if($v1->getBuild() > $v2->getBuild()) return true;
		if($v1->getBuild() < $v2->getBuild()) return false;
	}
	static function gte($v1,$v2) {
		return !self::lt($v1, $v2);
	}
	static function lt($v1,$v2) {
		return self::gt($v2, $v1);
	}
	static function lte($v1,$v2) {
		return !self::gt($v1, $v2);
	}
	static function eq($v1,$v2) {
		$v1=new version($v1);
		$v2=new version($v2);
		return $v1->getVersion()==$v2->getVersion();
	}
	static function neq($v1,$v2) {
		return !self::eq($v1, $v2);
	}
}
class versionException extends Exception {}

<?php
namespace vierbergenlars\SemVer;
class version extends expression {
	private $version='0.0.0';
	private $major='0';
	private $minor='0';
	private $patch='0';
	private $build='';
	private $prtag='';
	function __construct($version) {
		$expression=sprintf(parent::$dirty_regexp_mask,parent::$global_single_version);
		if(!preg_match($expression, $version)) throw new SemVerException('['.$version.']This is not a simple, singular version! No comparators nor ranges allowed!');
		parent::__construct($version);
		$this->version=$this->getChunk(0, 0);
		preg_match($expression, $this->version, $matches);
		parent::matchesToVersionParts($matches, $this->major, $this->minor, $this->patch, $this->build, $this->prtag, NULL);
		if($this->major===null) $this->major=-1;
		if($this->minor===null) $this->minor=-1;
		if($this->patch===null) $this->patch=-1;
		if($this->build==='') $this->build=-1;
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
	function getTag() {
		return (string)$this->prtag;
	}
	function valid() {
		return $this->getVersion();
	}
	function inc($what) {
		if($what=='major') return new version(($this->major+1).'.0.0');
		if($what=='minor') return new version($this->major.'.'.($this->minor+1).'.0');
		if($what=='patch') return new version($this->major.'.'.$this->minor.'.'.($this->patch+1));
		if($what=='build')  {
			if($this->build==-1) return new version($this->major.'.'.$this->minor.'.'.$this->patch.'-1');
			return new version($this->major.'.'.$this->minor.'.'.$this->patch.'-'.($this->build+1));
		}
		throw new SemVerException('Invalid increment value given');
	}
	function satisfies(expression $versions) {
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
		$t=array(''=>true,'-'=>true,'--'=>true);
		if(isset($t[$v1->getTag()])&&!isset($t[$v2->getTag()])) return true; //v1 has no tag, v2 has tag
		if(!isset($t[$v1->getTag()])&&isset($t[$v2->getTag()])) return false; //v1 has tag, v2 has no tag
		if($v1->getMajor() > $v2->getMajor()) return true;
		if($v1->getMajor() < $v2->getMajor()) return false;
		if($v1->getMinor() > $v2->getMinor()) return true;
		if($v1->getMinor() < $v2->getMinor()) return false;
		if($v1->getPatch() > $v2->getPatch()) return true;
		if($v1->getPatch() < $v2->getPatch()) return false;
		if($v1->getBuild() > $v2->getBuild()) return true;
		if($v1->getBuild() < $v2->getBuild()) return false;
		if($v1->getTag() > $v2->getTag()) return true;
		if($v1->getTag() < $v2->getTag()) return false;
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
	static function compare($v1,$v2) {
		$g=self::gt($v1, $v2);
		if($g===null) return 0;
		if($g) return 1;
		return -1;
	}
	static function rcompare($v1,$v2) {
		return self::compare($v2, $v1);
	}
}

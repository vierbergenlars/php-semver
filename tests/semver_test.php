<?php
namespace vierbergenlars\SemVer\Tests;
use vierbergenlars\SemVer;
require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../vendor/vierbergenlars/simpletest/autorun.php';
class SemVerTest extends \UnitTestCase {
	function testComparison() {
		$compare=array(
		  array("0.0.0","0.0.0foo")
		, array("0.0.1","0.0.0")
		, array("1.0.0","0.9.9")
		, array("0.10.0","0.9.0")
		, array("0.99.0","0.10.0")
		, array("2.0.0","1.2.3")
		, array("v0.0.0","0.0.0foo")
		, array("v0.0.1","0.0.0")
		, array("v1.0.0","0.9.9")
		, array("v0.10.0","0.9.0")
		, array("v0.99.0","0.10.0")
		, array("v2.0.0","1.2.3")
		, array("0.0.0","v0.0.0foo")
		, array("0.0.1","v0.0.0")
		, array("1.0.0","v0.9.9")
		, array("0.10.0","v0.9.0")
		, array("0.99.0","v0.10.0")
		, array("2.0.0","v1.2.3")
		, array("1.2.3","1.2.3-asdf")
		, array("1.2.3-4","1.2.3")
		, array("1.2.3-4-foo","1.2.3")
		, array("1.2.3-5","1.2.3-5-foo")
		, array("1.2.3-5","1.2.3-4")
		, array("1.2.3-5-foo","1.2.3-5-Foo")
		, array('1.2.3-0','1.2.3')
		, array('3.0.0', '2.7.2+')
		);
		foreach($compare as $set) {
			$a=$set[0];
			$b=$set[1];
			$this->assertTrue(SemVer\version::gt($a, $b), "%s > gt('".$a."', '".$b."')");
			$this->assertTrue(SemVer\version::lt($b, $a), "%s > lt('".$b."', '".$a."')");
			$this->assertFalse(SemVer\version::gt($b, $a), "%s > !gt('".$b."', '".$a."')");
			$this->assertFalse(SemVer\version::lt($a, $b), "%s > !lt('".$a."', '".$b."')");
			$this->assertTrue(SemVer\version::eq($a, $a), "%s > eq('".$a."', '".$a."')");
			$this->assertTrue(SemVer\version::eq($b, $b), "%s > eq('".$b."', '".$b."')");
			$this->assertTrue(SemVer\version::neq($a, $b), "%s > neq('".$a."', '".$b."')");
			$this->assertTrue(SemVer\version::cmp($b, "==", $b), "%s > cmp('".$b."' == '".$b."')");
			$this->assertTrue(SemVer\version::cmp($a, ">=", $b), "%s > cmp('".$a."' >= '".$b."')");
			$this->assertTrue(SemVer\version::cmp($b, "<=", $a), "%s > cmp('".$b."' <= '".$a."')");
			$this->assertTrue(SemVer\version::cmp($a, "!=", $b), "%s > cmp('".$a."' != '".$b."')");
		}
	}
	function testEquality() {
		$compare=array(
		  array("1.2.3","v1.2.3")
		, array("1.2.3","=1.2.3")
		, array("1.2.3","v 1.2.3")
		, array("1.2.3","= 1.2.3")
		, array("1.2.3"," v1.2.3")
		, array("1.2.3"," =1.2.3")
		, array("1.2.3"," v 1.2.3")
		, array("1.2.3"," = 1.2.3")
		, array("1.2.3-0","v1.2.3-0")
		, array("1.2.3-0","=1.2.3-0")
		, array("1.2.3-0","v 1.2.3-0")
		, array("1.2.3-0","= 1.2.3-0")
		, array("1.2.3-0"," v1.2.3-0")
		, array("1.2.3-0"," =1.2.3-0")
		, array("1.2.3-0"," v 1.2.3-0")
		, array("1.2.3-0"," = 1.2.3-0")
		, array("1.2.3-01","v1.2.3-1")
		, array("1.2.3-01","=1.2.3-1")
		, array("1.2.3-01","v 1.2.3-1")
		, array("1.2.3-01","= 1.2.3-1")
		, array("1.2.3-01"," v1.2.3-1")
		, array("1.2.3-01"," =1.2.3-1")
		, array("1.2.3-01"," v 1.2.3-1")
		, array("1.2.3-01"," = 1.2.3-1")
		, array("1.2.3beta","v1.2.3beta")
		, array("1.2.3beta","=1.2.3beta")
		, array("1.2.3beta","v 1.2.3beta")
		, array("1.2.3beta","= 1.2.3beta")
		, array("1.2.3beta"," v1.2.3beta")
		, array("1.2.3beta"," =1.2.3beta")
		, array("1.2.3beta"," v 1.2.3beta")
		, array("1.2.3beta"," = 1.2.3beta")
		);
		foreach($compare as $set) {
			$a=$set[0];
			$b=$set[1];
			$this->assertTrue(SemVer\version::eq($a, $b), "%s > eq('".$a."', '".$b."')");
			$this->assertFalse(SemVer\version::neq($a, $b), "%s > !neq('".$a."', '".$b."')");
			$this->assertTrue(SemVer\version::cmp($a, "==", $b), "%s > cmp(".$a."==".$b.")");
			$this->assertFalse(SemVer\version::cmp($a, "!=", $b), "%s > !cmp(".$a."!=".$b.")");
			$this->assertFalse(SemVer\version::cmp($a, "===", $b), "%s > !cmp(".$a."===".$b.")");
			$this->assertTrue(SemVer\version::cmp($a, "!==", $b), "%s > cmp(".$a."!==".$b.")");
			$this->assertFalse(SemVer\version::gt($a, $b), "%s > !gt('".$a."', '".$b."')");
			$this->assertTrue(SemVer\version::gte($a, $b), "%s > gte('".$a."', '".$b."')");
			$this->assertFalse(SemVer\version::lt($a, $b), "%s > !lt('".$a."', '".$b."')");
			$this->assertTrue(SemVer\version::lte($a, $b), "%s > lte('".$a."', '".$b."')");
		}
	}
	function testRange() {
	$compare=array(
		 array("1.0.0 - 2.0.0","1.2.3")
		, array("1.0.0","1.0.0")
		, array(">=*","0.2.4")
	//	, array("", "1.0.0")
		, array("*","1.2.3")
		, array(">=1.0.0","1.0.0")
		, array(">=1.0.0","1.0.1")
		, array(">=1.0.0","1.1.0")
		, array(">1.0.0","1.0.1")
		, array(">1.0.0","1.1.0")
		, array("<=2.0.0","2.0.0")
		, array("<=2.0.0","1.9999.9999")
		, array("<=2.0.0","0.2.9")
		, array("<2.0.0","1.9999.9999")
		, array("<2.0.0","0.2.9")
		, array(">= 1.0.0","1.0.0")
		, array(">=  1.0.0","1.0.1")
		, array(">=   1.0.0","1.1.0")
		, array("> 1.0.0","1.0.1")
		, array(">  1.0.0","1.1.0")
		, array("<=   2.0.0","2.0.0")
		, array("<= 2.0.0","1.9999.9999")
		, array("<=  2.0.0","0.2.9")
		, array("<    2.0.0","1.9999.9999")
		, array("<\t2.0.0","0.2.9")
		, array(">=0.1.97","v0.1.97")
		, array(">=0.1.97","0.1.97")
		, array("0.1.20 || 1.2.4","1.2.4")
		, array(">=0.2.3 || <0.0.1","0.0.0")
		, array(">=0.2.3 || <0.0.1","0.2.3")
		, array(">=0.2.3 || <0.0.1","0.2.4")
	//	, array("||","1.3.4")
		, array("2.x.x","2.1.3")
		, array("1.2.x","1.2.3")
		, array("1.2.x || 2.x","2.1.3")
		, array("1.2.x || 2.x","1.2.3")
		, array("x","1.2.3")
		, array("2.*.*","2.1.3")
		, array("1.2.*","1.2.3")
		, array("1.2.* || 2.*","2.1.3")
		, array("1.2.* || 2.*","1.2.3")
		, array("*","1.2.3")
		, array("2","2.1.2")
		, array("2.3","2.3.1")
		, array("~2.4","2.4.0") // >=2.4.0 <2.5.0
		, array("~2.4","2.4.5")
		, array("~>3.2.1","3.2.2") // >=3.2.1 <3.3.0
		, array("~1","1.2.3") // >=1.0.0 <2.0.0
		, array("~>1","1.2.3")
		, array("~> 1","1.2.3")
		, array("~1.0","1.0.2") // >=1.0.0 <1.1.0
		, array("~ 1.0","1.0.2")
        , array("~1.0.3", "1.0.12")
		, array(">=1","1.0.0")
		, array(">= 1","1.0.0")
		, array("<1.2","1.1.1")
		, array("< 1.2","1.1.1")
		, array("1","1.0.0beta")
		, array("~v0.5.4-pre","0.5.5")
		, array("~v0.5.4-pre","0.5.4")
		, array('=0.7.x', '0.7.2')
		, array('>=0.7.x', '0.7.2')
		, array('=0.7.x', '0.7.0-asdf')
		, array('>=0.7.x', '0.7.0-asdf')
		, array('<=0.7.x', '0.6.2')
        , array('~1.2.1 >=1.2.3', '1.2.3')
        , array('~1.2.1 =1.2.3', '1.2.3')
        , array('~1.2.1 1.2.3', '1.2.3')
        , array('~1.2.1 >=1.2.3 1.2.3', '1.2.3')
        , array('~1.2.1 1.2.3 >=1.2.3', '1.2.3')
        , array('~1.2.1 1.2.3', '1.2.3')
        , array('>=1.2.1 1.2.3', '1.2.3')
        , array('1.2.3 >=1.2.1', '1.2.3')
        , array('>=1.2.3 >=1.2.1', '1.2.3')
        , array('>=1.2.1 >=1.2.3', '1.2.3')
		);
		foreach($compare as $set) {
			$v=new SemVer\version($set[1]);
			$this->assertTrue($v->satisfies(new SemVer\expression($set[0])), "%s > $set[0] should be satisfied by $set[1]");
		}

	}
	function testNegativeRange() {
		$compare=array(
		  array("1.0.0 - 2.0.0","2.2.3")
		, array("1.0.0","1.0.1")
		, array(">=1.0.0","0.0.0")
		, array(">=1.0.0","0.0.1")
		, array(">=1.0.0","0.1.0")
		, array(">1.0.0","0.0.1")
		, array(">1.0.0","0.1.0")
		, array("<=2.0.0","3.0.0")
		, array("<=2.0.0","2.9999.9999")
		, array("<=2.0.0","2.2.9")
		, array("<2.0.0","2.9999.9999")
		, array("<2.0.0","2.2.9")
		, array(">=0.1.97","v0.1.93")
		, array(">=0.1.97","0.1.93")
		, array("0.1.20 || 1.2.4","1.2.3")
		, array(">=0.2.3 || <0.0.1","0.0.3")
		, array(">=0.2.3 || <0.0.1","0.2.2")
		, array("2.x.x","1.1.3")
		, array("2.x.x","3.1.3")
		, array("1.2.x","1.3.3")
		, array("1.2.x || 2.x","3.1.3")
		, array("1.2.x || 2.x","1.1.3")
		, array("2.*.*","1.1.3")
		, array("2.*.*","3.1.3")
		, array("1.2.*","1.3.3")
		, array("1.2.* || 2.*","3.1.3")
		, array("1.2.* || 2.*","1.1.3")
		, array("2","1.1.2")
		, array("2.3","2.4.1")
		, array("~2.4","2.5.0") // >=2.4.0 <2.5.0
		, array("~2.4","2.3.9")
		, array("~>3.2.1","3.3.2") // >=3.2.1 <3.3.0
		, array("~>3.2.1","3.2.0") // >=3.2.1 <3.3.0
		, array("~1","0.2.3") // >=1.0.0 <2.0.0
		, array("~>1","2.2.3")
		, array("~1.0","1.1.0") // >=1.0.0 <1.1.0
		, array("<1","1.0.0")
		, array(">=1.2","1.1.1")
		, array("<1","1.0.0beta")
		, array("< 1","1.0.0beta")
		, array("1","2.0.0beta")
		, array(">1.0.0", "1.0.0beta")
		, array("~v0.5.4-beta","0.5.4-alpha")
		, array('=0.7.x', '0.8.2')
		, array('>=0.7.x', '0.6.2')
		, array('<=0.7.x', '0.7.2')
		);
		foreach($compare as $set) {
			$v=new SemVer\version($set[1]);
			$this->assertFalse($v->satisfies(new SemVer\expression($set[0])), "%s > $set[0] should not be satisfied by $set[1]");
		}
	}
	function testIncrementVersions() {
		$compare=array(
		  array("1.2.3","major","2.0.0")
		, array("1.2.3","minor","1.3.0")
		, array("1.2.3","patch","1.2.4")
		, array("1.2.3","build","1.2.3-1")
		, array("1.2.3-4","build","1.2.3-5")
		, array("1.2.3tag","major","2.0.0")
		, array("1.2.3-tag","major","2.0.0")
		, array("1.2.3tag","build","1.2.3-1")
		, array("1.2.3-tag","build","1.2.3-1")
		, array("1.2.3-4-tag","build","1.2.3-5")
		, array("1.2.3-4tag","build","1.2.3-5")
		, array("1.2.3","fake",null)
		, array("fake","major",null)
		);
		foreach($compare as $set) {
			$s=$set[0];
			if($set[2]===null) $this->expectException();
			$v=new SemVer\version($s);
			$this->assertEqual($v->inc($set[1])->getVersion(), $set[2], "%s > inc($set[0], $set[1]) === $set[2]");
		}
	}
	function testValidRange() {
		$compare=array(
		  array("1.0.0 - 2.0.0",">=1.0.0 <=2.0.0")
		, array("1.0.0","1.0.0")
		, array(">=*",">=0.0.0-")
	//	, array("","")
		, array("*",">=0")
		, array(">=1.0.0",">=1.0.0")
		, array(">1.0.0",">1.0.0")
		, array("<=2.0.0","<=2.0.0")
		, array("1",">=1 <2.0.0-")
		, array("<=2.0.0","<=2.0.0")
		, array("<2.0.0","<2.0.0")
		, array(">= 1.0.0",">=1.0.0")
		, array(">=  1.0.0",">=1.0.0")
		, array(">=   1.0.0",">=1.0.0")
		, array("> 1.0.0",">1.0.0")
		, array(">  1.0.0",">1.0.0")
		, array("<=   2.0.0","<=2.0.0")
		, array("<= 2.0.0","<=2.0.0")
		, array("<=  2.0.0","<=2.0.0")
		, array("<    2.0.0","<2.0.0")
		, array("<	2.0.0","<2.0.0")
		, array(">=0.1.97",">=0.1.97")
		, array(">=0.1.97",">=0.1.97")
		, array("0.1.20 || 1.2.4","0.1.20||1.2.4")
		, array(">=0.2.3 || <0.0.1",">=0.2.3||<0.0.1")
		, array(">=0.2.3 || <0.0.1",">=0.2.3||<0.0.1")
		, array(">=0.2.3 || <0.0.1",">=0.2.3||<0.0.1")
	//	, array("||","||")
		, array("2.x.x",">=2 <3.0.0-")
		, array("1.2.x",">=1.2 <1.3.0-")
		, array("1.2.x || 2.x",">=1.2 <1.3.0-||>=2 <3.0.0-")
		, array("x",">=0")
		, array("2.*.*",'>=2 <3.0.0-')
		, array("1.2.*",'>=1.2 <1.3.0-')
		, array("1.2.* || 2.*",'>=1.2 <1.3.0-||>=2 <3.0.0-')
		, array("*",">=0")
		, array("2",">=2 <3.0.0-")
		, array("2.3",">=2.3 <2.4.0-")
		, array("~2.4",">=2.4 <2.5.0-")
		, array("~>3.2.1",">=3.2.1 <3.3.0-")
		, array("~1",">=1 <2.0.0-")
		, array("~>1",">=1 <2.0.0-")
		, array("~> 1",">=1 <2.0.0-")
		, array("~1.0",">=1.0 <1.1.0-")
		, array("~ 1.0",">=1.0 <1.1.0-")
		, array("<1","<1")
		, array("< 1","<1")
		, array(">=1",">=1")
		, array(">= 1",">=1")
		, array("<1.2","<1.2")
		, array("< 1.2","<1.2")
		);
		foreach($compare as $set) {
			$v=new SemVer\expression($set[0]);
			$this->assertEqual($v->getString(), $set[1], "%s > validRange($set[0]) === $set[1]");
		}
	}
}

<?php
require 'simpletest/simpletest.phar';
require 'version.php';
class versioningTest extends UnitTestCase {
	function testKeepSimpleversion() {
		$t=array(
				'1.0.0',
				'2.0.0',
				'1.0.1',
				'1.3.2',
				'1.02.0'=>'1.2.0',
				'0.2.5',
				'01.2.6'=>'1.2.6',
				'2.0.03'=>'2.0.3',
				'0.0.0'
		);
		foreach($t as $original=>$result) {
			if(!is_string($original)) $original=$result;
			$v=new versionExpression($original);
			$this->assertEqual($v->getString(),$result,'['.$original.'] %s');
		}
	}
	function testKeepSimpleversionComparator() {
		$t=array(
				'>1.0.0',
				'>1.1.0',
				'>1.2.3',
				'>0.5.6',
				'>0.025.6'=>'>0.25.6',
				'>=1.0.0',
				'>=1.2.0',
				'>=1.4.5',
				'>=0.9.3',
				'>=05.3.6'=>'>=5.3.6',
				'<2.0.0',
				'<5.6.0',
				'<2.3.5',
				'<0.2.3',
				'<0.2.05'=>'<0.2.5',
				'<=7.0.0',
				'<=1.3.0',
				'<=1.4.3',
				'<=0.2.6',
				'<=00.05.6'=>'<=0.5.6'
		);
		foreach($t as $original=>$result) {
			if(!is_string($original)) $original=$result;
			$v=new versionExpression($original);
			$this->assertEqual($v->getString(),$result,'['.$original.'] %s');
		}
	}
	function testShortSimpleversion() {
		$t=array(
				'1'=>'>=1.0.0 <2.0.0',
				'1.2'=>'>=1.2.0 <1.3.0',
				'1.0'=>'>=1.0.0 <1.1.0',
				'501'=>'>=501.0.0 <502.0.0'
		);
		foreach($t as $original=>$result) {
			$v=new versionExpression($original);
			$this->assertEqual($v->getString(),$result,'['.$original.'] %s');
		}
	}
	function testShortSimpleversionComparator() {
		$t=array(
				'>1'=>'>1.0.0',
				'<2.0'=>'<2.0.0',
				'<=5.2'=>'<=5.2.0',
				'>=3'=>'>=3.0.0'
		);
		foreach($t as $original=>$result) {
			$v=new versionExpression($original);
			$this->assertEqual($v->getString(),$result,'['.$original.'] %s');
		}
	}
	function testSimpleversionWildcard() {
		$t=array(
				'1.x.x'=>'>=1.0.0 <2.0.0',
				'1.x'=>'>=1.0.0 <2.0.0',
				'1.x.5'=>'>=1.0.0 <2.0.0',
				'3.x2'=>'>=3.0.0 <4.0.0',
				'1.X.X'=>'>=1.0.0 <2.0.0',
				'1.*.*'=>'>=1.0.0 <2.0.0',
				'2.X.x'=>'>=2.0.0 <3.0.0',
				'5.*.x'=>'>=5.0.0 <6.0.0',
				'x'=>'>=0.0.0'
		);
		foreach($t as $original=>$result) {
			$v=new versionExpression($original);
			$this->assertEqual($v->getString(),$result,'['.$original.'] %s');
		}
	}
	function testSimpleversionRange() {
		$t=array(
				'1.0.0 - 2.0.0'=>'>=1.0.0 <2.0.0',
				'1.2.3 - 1.3.0'=>'>=1.2.3 <1.3.0',
				'4.3.0 - 4.3.1'=> '>=4.3.0 <4.3.1'
		);
		foreach($t as $original=>$result) {
			$v=new versionExpression($original);
			$this->assertEqual($v->getString(),$result,'['.$original.'] %s');
		}
	}
	function testShortversionRange() {
		$t=array(
				'1 - 2'=>'>=1.0.0 <2.0.0',
				'1.2 - 2.1'=>'>=1.2.0 <2.1.0'
		);
		foreach($t as $original=>$result) {
			$v=new versionExpression($original);
			$this->assertEqual($v->getString(),$result,'['.$original.'] %s');
		}
	}
	function testAndOperator() {
		$t=array(
				'<1.2.0   >=1.3.2'=>array(array('<1.2.0','>=1.3.2')),
				'>2.3.4 <5.0'=>array(array('>2.3.4','<5.0.0')),
				'>1.0.0 <=1.2.0 '=>array(array('>1.0.0','<=1.2.0')),
				'>=1.2.4'=>array(array('>=1.2.4'))
		);
		foreach($t as $original=>$result) {
			$v=new versionExpression($original);
			$this->assertEqual($v->getChunks(),$result,'['.$original.'] %s');
		}
	}
	function testOrOperator() {
		$t=array(
				'<1.2.0 || >2.1'=>array(array('<1.2.0'),array('>2.1.0')),
				'<1.3 || >3.0 <3.5 || >4'=>array(array('<1.3.0'),array('>3.0.0','<3.5.0'),array('>4.0.0'))
		);
		foreach($t as $original=>$result) {
			$v=new versionExpression($original);
			$this->assertEqual($v->getChunks(),$result,'['.$original.'] %s');
		}
	}
	function testComplexExpessions() {
		$t=array(
				'1.x || 2.0 - 2.3 || >4.x.x'=>'>=1.0.0 <2.0.0||>=2.0.0 <2.3.0||>4.0.0',
				'2.0.x || 2.1 - 4 || 4 - 4.5' => '>=2.0.0 <2.1.0||>=2.1.0 <4.0.0||>=4.0.0 <4.5.0'
		);
		foreach($t as $original=>$result) {
			$v=new versionExpression($original);
			$this->assertEqual($v->getString(),$result,'['.$original.'] %s');
		}
	}
	function testSatisfiedBy() {
		$t=array(
			'1.0.0'=>'1.0.0',
			'1.2.3'=>'1.2.3'
		);
		foreach($t as $range=>$version) {
			$e=new versionExpression($range);
			$v=new version($version);
			$this->assertTrue($e->satisfiedBy($v), '['.$range.' :: '.$version.'] %s');
		}
	}
}
<?php
require_once 'simpletest/simpletest.php';
class RemoteCPVersioningTests extends TestSuite {
	function __construct() {
		parent::__construct('RemoteCP Versioning System Tests');
		$this->addFile('base_test.php');
		$this->addFile('semver_test.php');
	}
}
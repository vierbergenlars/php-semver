<?php
require_once 'simpletest/simpletest.phar';
class RemoteCPVersioningTests extends TestSuite {
	function __construct() {
		parent::__construct('RemoteCP Versioning System Tests');
		$this->addFile('base_test.php');
		$this->addFile('semver_test.php');
	}
}
<?php

namespace vierbergenlars\SemVer\Tests;

use PHPUnit\Framework\TestCase;
use vierbergenlars\LibJs\JSArray;
use vierbergenlars\LibJs\JString;

/**
 * @copyright ResearchGate GmbH
 */
class JSArrayTest extends TestCase
{

    public function testIterableJSArray()
    {
        $jsArray = new JSArray(array('1.0.0', '2.0.0'));
        $versions = array();
        foreach ($jsArray as $version) {
            $versions[] = $version;
        }

        $this->assertEquals(array(new JString('1.0.0'), new JString('2.0.0')), $versions);
    }
}

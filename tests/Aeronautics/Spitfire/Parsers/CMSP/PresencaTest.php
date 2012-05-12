<?php

namespace Aeronautics\Spitfire\Parsers\CMSP;

class PresencaTest extends \PHPUnit_Framework_TestCase
{
    protected $file;
    protected $object;

    public function setUp()
    {
        $className    = 'Aeronautics\Spitfire\Parsers\CMSP\Presenca';
        $methods      = array('getContent', 'parseXmlToArray');
        $this->file  = TEST_DATA.'/CMSP/Presencas_2012_04_11_[0].xml';
        /*
        $this->object = $this->getMock($className, $methods);
        $this->object->expects($this->any())
                     ->method('getContent')
                     ->will($this->returnValue($content));
                     */
    }

    public function tearDown()
    {
        unset($this->object);
    }

    public function testParsePresences()
    {
        $object = new Presenca();
        $array  = $object->parsePresencesToArray(file_get_contents($this->file));
        $this->assertEquals(55, count($array));
    }

}
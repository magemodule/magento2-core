<?php

namespace MageModule\Core\Test\Unit\Model\Data\Sanitizer;

class DateTimeTest extends \Magento\Framework\TestFramework\Unit\BaseTestCase
{
    /**
     * @var \MageModule\Core\Model\Data\Sanitizer\DateTime
     */
    private $sanitizer;

    public function setUp()
    {
        parent::setUp();

        $this->sanitizer = $this->objectManager->getObject(
            \MageModule\Core\Model\Data\Sanitizer\DateTime::class,
            ['dateTime' => $this->objectManager->getObject(\Magento\Framework\Stdlib\DateTime::class)]
        );
    }

    public function testSanitizeV1()
    {
        $datetime = '2/24/18 17:10';
        $expected = '2018-02-24 17:10:00';
        $result   = $this->sanitizer->sanitize($datetime);
        $this->assertEquals($expected, $result);
    }

    public function testSanitizeV2()
    {
        $datetime = '2/24/18 17:10:05';
        $expected = '2018-02-24 17:10:05';
        $result   = $this->sanitizer->sanitize($datetime);
        $this->assertEquals($expected, $result);
    }

    public function testSanitizeV3()
    {
        $datetime = '2/24/18 5:10PM';
        $expected = '2018-02-24 17:10:00';
        $result   = $this->sanitizer->sanitize($datetime);
        $this->assertEquals($expected, $result);
    }

    public function testSanitizeV4()
    {
        $datetime = '2/24/18 5:10AM';
        $expected = '2018-02-24 17:10:00';
        $result   = $this->sanitizer->sanitize($datetime);
        $this->assertNotEquals($expected, $result);
    }

    public function testSanitizeV5()
    {
        $datetime = '2-24-18 5:10PM';
        $expected = '2018-02-24 17:10:00';
        $result   = $this->sanitizer->sanitize($datetime);
        $this->assertEquals($expected, $result);
    }

    public function testSanitizeV6()
    {
        $datetime = '02/24/2018 17:10';
        $expected = '2018-02-24 17:10:00';
        $result   = $this->sanitizer->sanitize($datetime);
        $this->assertEquals($expected, $result);
    }

    public function testSanitizeV7()
    {
        $datetime = '2018/02/24 17:10';
        $expected = '2018-02-24 17:10:00';
        $result   = $this->sanitizer->sanitize($datetime);
        $this->assertEquals($expected, $result);
    }

    public function testSanitizeV8()
    {
        $datetime = '2018/24/02 17:10';
        $result   = $this->sanitizer->sanitize($datetime);
        $this->assertNull($result);
    }

    public function testSanitizeV9()
    {
        $datetime = '02/24/2018';
        $expected = '2018-02-24 00:00:00';
        $result   = $this->sanitizer->sanitize($datetime);
        $this->assertEquals($expected, $result);
    }

    public function testSanitizeV10()
    {
        $datetime = '2018-12-11';
        $expected = '2018-12-11 00:00:00';
        $result   = $this->sanitizer->sanitize($datetime);
        $this->assertEquals($expected, $result);
    }
}

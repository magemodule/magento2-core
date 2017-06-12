<?php

namespace MageModule\Core\Test\Unit\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \MageModule\Core\Helper\Data
     */
    private $helper;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->helper  = $objectManager->getObject(\MageModule\Core\Helper\Data::class);
    }

    /**
     * @return array
     */
    public function providerTestArrayFunctions()
    {
        return [
            [
                [
                    0 => '',
                    1 => '',
                    2 => 'some string',
                    3 => [
                        0 => 'some string'
                    ],
                    4 => new \Magento\Framework\DataObject()
                ]
            ]
        ];
    }

    /**
     * @dataProvider providerTestArrayFunctions
     *
     * @param array $array
     */
    public function testNullifyEmpty(array $array)
    {
        $this->helper->nullifyEmpty($array);
        $this->assertNull($array[0]);
        $this->assertNull($array[1]);
        $this->assertNotNull($array[2]);
        $this->assertNotNull($array[3]);
        $this->assertNotNull($array[4]);
    }

    /**
     * @dataProvider providerTestArrayFunctions
     *
     * @param array $array
     */
    public function testRemoveObjects(array $array)
    {
        $this->assertArrayHasKey(4, $array);
        $this->helper->removeObjects($array);
        $this->assertArrayNotHasKey(4, $array);
    }

    /**
     * @dataProvider providerTestArrayFunctions
     *
     * @param array $array
     */
    public function testRemoveArrays(array $array)
    {
        $this->assertArrayHasKey(0, $array[3]);
        $this->helper->removeArrays($array);
        $this->assertArrayNotHasKey(3, $array);
    }

    /**
     * @return array
     */
    public function getCsvDataSampleArray()
    {
        return [
            0 => [
                'entity_id'    => '1',
                'state'        => 'complete',
                'status'       => 'complete',
                'coupon_code'  => null,
                'protect_code' => 'a658ab',
                1              => 'hi there',
                2              => [
                    'some' => 1,
                    'keys' => '2'
                ]
            ],
            1 => [
                'coupon_code'          => null,
                'protect_code'         => '6bee66',
                'shipping_description' => 'Flat Rate - Fixed',
                'is_virtual'           => '0',
                'store_id'             => '1',
                'customer_id'          => '1',
            ],
            2 => [
                0 => 'some value',
                2 => 'some other value'
            ]
        ];
    }

    public function testEqualizeArrayKeys()
    {
        $data = $this->getCsvDataSampleArray();

        $expectedKeyCount = count($data[0] + $data[1] + $data[2]);

        $this->helper->equalizeArrayKeys($data);
        $keys1 = implode('', array_keys($data[0]));
        $keys2 = implode('', array_keys($data[1]));
        $keys3 = implode('', array_keys($data[2]));

        $this->assertEquals($keys1, $keys2);
        $this->assertEquals($keys1, $keys3);
        $this->assertEquals($expectedKeyCount, count($data[0]));
        $this->assertEquals($expectedKeyCount, count($data[1]));
        $this->assertEquals($expectedKeyCount, count($data[2]));
    }

    public function testAddHeadersRowToArray()
    {
        $data = $this->getCsvDataSampleArray();
        $this->helper->equalizeArrayKeys($data);
        $this->helper->addHeadersRowToArray($data);

        $headers = implode('', $data[0]);
        $keys1   = implode('', array_keys($data[1]));
        $keys2   = implode('', array_keys($data[2]));
        $keys3   = implode('', array_keys($data[3]));

        $this->assertCount(4, $data);
        $this->assertEquals($headers, $keys1);
        $this->assertEquals($headers, $keys2);
        $this->assertEquals($headers, $keys3);
    }

    public function testAddPrefix()
    {
        $data   = $this->getCsvDataSampleArray();
        $prefix = 'testing_';
        foreach ($data as &$subarray) {
            $this->helper->addPrefix($prefix, $subarray);
            foreach ($subarray as $key => $value) {
                $this->assertStringStartsWith($prefix, $key);
            }
        }
    }

    public function testStringifyPaths()
    {
        $data   = $this->getCsvDataSampleArray();
        $result = $this->helper->stringifyPaths($data);

        $this->assertEquals('0', $result[0]);
        $this->assertEquals('0/entity_id', $result[1]);
        $this->assertEquals('0/2/some', $result[8]);
    }
}

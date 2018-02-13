<?php
/**
 * Copyright (c) 2018 MageModule: All rights reserved
 *
 * LICENSE: This source file is subject to our standard End User License 
 * Agreeement (EULA) that is available through the world-wide-web at the 
 * following URI: http://www.magemodule.com/magento2-ext-license.html.  
 *
 * If you did not receive a copy of the EULA and are unable to obtain it through 
 * the web, please send a note to admin@magemodule.com so that we can mail 
 * you a copy immediately.
 *
 * @author       MageModule admin@magemodule.com 
 * @copyright   2018 MageModule
 * @license       http://www.magemodule.com/magento2-ext-license.html
 *
 */

namespace MageModule\Core\Test\Unit\Model\Data;

class MapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \MageModule\Core\Model\Data\Mapper
     */
    private $model;

    public function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $helper = $this->objectManager->getObject(\MageModule\Core\Helper\Data::class);

        $this->model = $this->objectManager->getObject(
            \MageModule\Core\Model\Data\Mapper::class,
            ['helper' => $helper]
        );
    }

    /**
     * @return array
     */
    public function getValidMapping()
    {
        return [
            'entity_id'   => 'test_entity_id',
            'name'        => 'customer_name',
            'postal_code' => 'zip_code'
        ];
    }

    /**
     * @return array
     */
    public function getNonUniqueMapping()
    {
        return [
            'entity_id'   => 'test_entity_id',
            'name'        => 'customer_name',
            'postal_code' => 'zip_code',
            'postcode'    => 'zip_code'
        ];
    }

    /**
     * @return array
     */
    public function getMappingWhereNewFieldEqualsPreviousOldField()
    {
        return [
            'entity_id'   => 'test_entity_id',
            'name'        => 'customer_name',
            'postal_code' => 'zip_code',
            'some_id'     => 'entity_id'
        ];
    }

    /**
     * @return array
     */
    public function getMappingWhereToFieldsAreMappedToSameField()
    {
        return [
            'entity_id' => 'some_field',
            'name'      => 'some_field'
        ];
    }

    /**
     * @return array
     */
    public function getArrayToMap()
    {
        return [
            'entity_id'   => '1',
            'name'        => 'MageModule',
            'postal_code' => '90210',
            'street1'     => '123 Rodeo Drive',
            'city'        => 'Beverly Hills'
        ];
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getObjectToMap()
    {
        /** @var \Magento\Framework\DataObject $object */
        $object = $this->objectManager->getObject(\Magento\Framework\DataObject::class);
        $object->setData($this->getArrayToMap());

        return $object;
    }

    public function testValidateValidMapping()
    {
        $this->assertTrue($this->model->validateMapping($this->getValidMapping()));
    }

    public function testMapArrayKeepOrigFields()
    {
        $array = $this->getArrayToMap();

        $this->model->setMapping($this->getValidMapping());
        $this->model->map($array, true);

        $this->assertArrayHasKey('entity_id', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('postal_code', $array);
        $this->assertArrayHasKey('street1', $array);
        $this->assertArrayHasKey('city', $array);
        $this->assertArrayHasKey('test_entity_id', $array);
        $this->assertArrayHasKey('customer_name', $array);
        $this->assertArrayHasKey('zip_code', $array);

        $this->assertEquals(1, $array['entity_id']);
        $this->assertEquals(1, $array['test_entity_id']);
        $this->assertEquals('MageModule', $array['name']);
        $this->assertEquals('MageModule', $array['customer_name']);
        $this->assertEquals('90210', $array['postal_code']);
        $this->assertEquals('90210', $array['zip_code']);
        $this->assertEquals('123 Rodeo Drive', $array['street1']);
        $this->assertEquals('Beverly Hills', $array['city']);
    }

    public function testMapArrayOmitOrigFields()
    {
        $array = $this->getArrayToMap();

        $this->model->setMapping($this->getValidMapping());
        $this->model->map($array, false);

        $this->assertArrayNotHasKey('entity_id', $array);
        $this->assertArrayNotHasKey('name', $array);
        $this->assertArrayNotHasKey('postal_code', $array);
        $this->assertArrayHasKey('street1', $array);
        $this->assertArrayHasKey('city', $array);
        $this->assertArrayHasKey('test_entity_id', $array);
        $this->assertArrayHasKey('customer_name', $array);
        $this->assertArrayHasKey('zip_code', $array);

        $this->assertEquals(1, $array['test_entity_id']);
        $this->assertEquals('MageModule', $array['customer_name']);
        $this->assertEquals('90210', $array['zip_code']);
        $this->assertEquals('123 Rodeo Drive', $array['street1']);
        $this->assertEquals('Beverly Hills', $array['city']);
    }

    public function testMapObjectKeepOrigFields()
    {
        $object = $this->getObjectToMap();

        $this->model->setMapping($this->getValidMapping());
        $this->model->map($object, true);

        $this->assertTrue($object->hasData('entity_id'));
        $this->assertTrue($object->hasData('name'));
        $this->assertTrue($object->hasData('postal_code'));
        $this->assertTrue($object->hasData('street1'));
        $this->assertTrue($object->hasData('city'));
        $this->assertTrue($object->hasData('test_entity_id'));
        $this->assertTrue($object->hasData('customer_name'));
        $this->assertTrue($object->hasData('zip_code'));

        $this->assertEquals(1, $object->getData('entity_id'));
        $this->assertEquals(1, $object->getData('test_entity_id'));
        $this->assertEquals('MageModule', $object->getData('name'));
        $this->assertEquals('MageModule', $object->getData('customer_name'));
        $this->assertEquals('90210', $object->getData('postal_code'));
        $this->assertEquals('90210', $object->getData('zip_code'));
        $this->assertEquals('123 Rodeo Drive', $object->getData('street1'));
        $this->assertEquals('Beverly Hills', $object->getData('city'));
    }

    public function testMapObjectOmitOrigFields()
    {
        $object = $this->getObjectToMap();

        $this->model->setMapping($this->getValidMapping());
        $this->model->map($object, false);

        $this->assertTrue(!$object->hasData('entity_id'));
        $this->assertTrue(!$object->hasData('name'));
        $this->assertTrue(!$object->hasData('postal_code'));
        $this->assertTrue($object->hasData('street1'));
        $this->assertTrue($object->hasData('city'));
        $this->assertTrue($object->hasData('test_entity_id'));
        $this->assertTrue($object->hasData('customer_name'));
        $this->assertTrue($object->hasData('zip_code'));

        $this->assertEquals(1, $object->getData('test_entity_id'));
        $this->assertEquals('MageModule', $object->getData('customer_name'));
        $this->assertEquals('90210', $object->getData('zip_code'));
        $this->assertEquals('123 Rodeo Drive', $object->getData('street1'));
        $this->assertEquals('Beverly Hills', $object->getData('city'));
    }

    public function testMapTwoFieldsMappedToSameField()
    {
        $array = $this->getArrayToMap();

        $this->model->setMapping($this->getMappingWhereToFieldsAreMappedToSameField());
        $this->model->map($array, false);

        $this->assertArrayNotHasKey('entity_id', $array);
        $this->assertArrayNotHasKey('name', $array);
        $this->assertArrayHasKey('some_field', $array);
        $this->assertArrayHasKey('postal_code', $array);
        $this->assertArrayHasKey('street1', $array);
        $this->assertArrayHasKey('city', $array);
        $this->assertCount(4, $array);

        $this->assertEquals('MageModule', $array['some_field']);
        $this->assertEquals('90210', $array['postal_code']);
        $this->assertEquals('123 Rodeo Drive', $array['street1']);
        $this->assertEquals('Beverly Hills', $array['city']);
    }

    public function testGetExistingMappedField()
    {
        $this->model->setMapping($this->getValidMapping());
        $result = $this->model->getMappedField('name');
        $this->assertEquals('customer_name', $result);
    }

    public function testGetNonExistentMappedField()
    {
        $this->model->setMapping($this->getValidMapping());
        $result = $this->model->getMappedField('some_non_field');
        $this->assertNull($result);
    }
}

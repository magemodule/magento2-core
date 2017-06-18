<?php

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

    public function testValidateNonUniqueNewFieldsMapping()
    {
        $this->setExpectedException(\Magento\Framework\Exception\LocalizedException::class);
        $this->model->validateMapping($this->getNonUniqueMapping());
    }

    public function testMapArray()
    {
        $array = $this->getArrayToMap();

        $this->model->setMapping($this->getValidMapping());
        $this->model->map($array);

        $this->assertArrayNotHasKey('entity_id', $array);
        $this->assertArrayNotHasKey('name', $array);
        $this->assertArrayNotHasKey('postal_code', $array);

        $this->assertEquals(1, $array['test_entity_id']);
        $this->assertEquals('MageModule', $array['customer_name']);
        $this->assertEquals(90210, $array['zip_code']);
        $this->assertEquals('123 Rodeo Drive', $array['street1']);
        $this->assertEquals('Beverly Hills', $array['city']);
    }

    public function testMapObject()
    {
        $object = $this->getObjectToMap();

        $this->model->setMapping($this->getValidMapping());
        $this->model->map($object);

        $this->assertTrue(!$object->hasData('entity_id'));
        $this->assertTrue(!$object->hasData('name'));
        $this->assertTrue(!$object->hasData('postal_code'));

        $this->assertEquals(1, $object->getData('test_entity_id'));
        $this->assertEquals('MageModule', $object->getData('customer_name'));
        $this->assertEquals(90210, $object->getData('zip_code'));
        $this->assertEquals('123 Rodeo Drive', $object->getData('street1'));
        $this->assertEquals('Beverly Hills', $object->getData('city'));
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

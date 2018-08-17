<?php /** @noinspection MessDetectorValidationInspection */

namespace MageModule\Core\Model\ResourceModel\Entity\Attribute;

use Magento\Framework\Exception\LocalizedException;
use MageModule\Core\Model\Entity\Attribute;

abstract class AbstractCollection extends \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
{
    /**
     * @var string
     */
    protected $entityTypeCode;

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_itemObjectClass = Attribute::class;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    protected function _initSelect()
    {
        $select = $this->getSelect()->from(
            ['main_table' => $this->getResource()->getMainTable()]
        );

        $select->join(
            ['entity_type_table' => $this->getResource()->getTable('eav_entity_type')],
            'main_table.entity_type_id = entity_type_table.entity_type_id',
            ''
        );

        $select->where('entity_type_table.entity_type_code = ?', $this->entityTypeCode);

        return $this;
    }
}

<?php

namespace MageModule\Core\Model\Data;

class Formatter implements FormatterInterface
{
    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $objectFactory;

    /**
     * @var \MageModule\Core\Helper\Data
     */
    private $helper;

    /**
     * This mapper is only for internal use. Should not have customized fields from users
     *
     * @var \MageModule\Core\Model\Data\Mapper
     */
    private $systemFieldMapper;

    /**
     * Field mapper that contains field mappings created by end user
     *
     * @var \MageModule\Core\Model\Data\Mapper
     */
    private $customFieldMapper;

    /**
     * @var \MageModule\Core\Model\Data\Formatter\Iterator[]
     */
    private $iterators = [];

    /**
     * @var string
     */
    private $format;

    /**
     * @var string
     */
    private $glue;

    /**
     * @var string|null
     */
    private $prepend;

    /**
     * @var string|null
     */
    private $append;

    /**
     * @var string
     */
    private $valueWrapPattern;

    /**
     * @var array
     */
    private $includedFields;

    /**
     * @var array
     */
    private $excludedFields;

    /**
     * @var bool
     */
    private $allowNewlineChar;

    /**
     * @var bool
     */
    private $allowReturnChar;

    /**
     * @var bool
     */
    private $allowTabChar;

    /**
     * Formatter constructor.
     *
     * @param \Magento\Framework\DataObjectFactory    $objectFactory
     * @param \MageModule\Core\Helper\Data            $helper
     * @param \MageModule\Core\Model\Data\Mapper|null $systemFieldMapper
     * @param \MageModule\Core\Model\Data\Mapper|null $customFieldMapper
     * @param array                                   $iterators
     * @param string                                  $format
     * @param string|array|null                       $glue
     * @param string|array|null                       $prepend
     * @param string|array|null                       $append
     * @param string|null                             $valueWrapPattern
     * @param array                                   $includedFields
     * @param array                                   $excludedFields
     * @param bool                                    $allowNewlineChar
     * @param bool                                    $allowReturnChar
     * @param bool                                    $allowTabChar
     */
    public function __construct(
        \Magento\Framework\DataObjectFactory $objectFactory,
        \MageModule\Core\Helper\Data $helper,
        \MageModule\Core\Model\Data\Mapper $systemFieldMapper = null,
        \MageModule\Core\Model\Data\Mapper $customFieldMapper = null,
        array $iterators = [],
        $format = 'string',
        $glue = null,
        $prepend = null,
        $append = null,
        $valueWrapPattern = null,
        array $includedFields = [],
        array $excludedFields = [],
        $allowNewlineChar = false,
        $allowReturnChar = false,
        $allowTabChar = false
    ) {
        $this->objectFactory     = $objectFactory;
        $this->helper            = $helper;
        $this->systemFieldMapper = $systemFieldMapper;
        $this->setCustomFieldMapper($customFieldMapper);
        $this->setIterators($iterators);
        $this->setFormat($format);
        $this->setGlue($glue);
        $this->setPrepend($prepend);
        $this->setAppend($append);
        $this->setValueWrapPattern($valueWrapPattern);
        $this->setIncludedFields($includedFields);
        $this->setExcludedFields($excludedFields);
        $this->allowNewlineChar = $allowNewlineChar;
        $this->allowReturnChar  = $allowReturnChar;
        $this->allowTabChar     = $allowTabChar;
    }

    /**
     * @return \MageModule\Core\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * @return null|\MageModule\Core\Model\Data\Mapper
     */
    public function getSystemFieldMapper()
    {
        return $this->systemFieldMapper;
    }

    /**
     * @param \MageModule\Core\Model\Data\Mapper $mapper
     *
     * @return $this
     */
    public function setCustomFieldMapper(\MageModule\Core\Model\Data\Mapper $mapper = null)
    {
        $this->customFieldMapper = $mapper;

        return $this;
    }

    /**
     * @return null|\MageModule\Core\Model\Data\Mapper
     */
    public function getCustomFieldMapper()
    {
        return $this->customFieldMapper;
    }

    /**
     * @param \MageModule\Core\Model\Data\Formatter\Iterator[] $iterators
     *
     * @return $this
     */
    public function setIterators(array $iterators)
    {
        foreach ($iterators as $field => $iterator) {
            $this->addIterator($field, $iterator);
        }

        return $this;
    }

    /**
     * @param string                                         $field
     * @param \MageModule\Core\Model\Data\Formatter\Iterator $iterator
     *
     * @return $this
     */
    public function addIterator($field, \MageModule\Core\Model\Data\Formatter\Iterator $iterator)
    {
        $this->iterators[$field] = $iterator;

        return $this;
    }

    /**
     * @return \MageModule\Core\Model\Data\Formatter\Iterator[]
     */
    public function getIterators()
    {
        return $this->iterators;
    }

    /**
     * @param string $field
     *
     * @return \MageModule\Core\Model\Data\Formatter\Iterator|null
     */
    public function getIterator($field)
    {
        if (isset($this->iterators[$field])) {
            return $this->iterators[$field];
        }

        return null;
    }

    /**
     * @param array|\Magento\Framework\DataObject $item
     */
    private function executeIterators(&$item)
    {
        foreach ($this->getIterators() as $field => $iterator) {
            if (is_array($item) && isset($item[$field])) {
                $item[$field] = $iterator->iterate($item[$field]);
            } elseif ($item instanceof \Magento\Framework\DataObject) {
                $item->setData($field, $iterator->iterate($item->getData($field)));
            }
        }
    }

    /**
     * Acceptable values are string, array, object
     *
     * @param string $format
     *
     * @return $this
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setGlue($value)
    {
        $this->glue = $this->prepareGlue($value);

        return $this;
    }

    /**
     * @return string
     */
    public function getGlue()
    {
        return $this->glue;
    }

    /**
     * @param string|array $glue
     *
     * @return string
     */
    public function prepareGlue($glue)
    {
        if (is_array($glue)) {
            foreach ($glue as &$subglue) {
                $subglue = $this->prepareGlue($subglue);
            }
            $glue = implode('', $glue);
        }

        if ($glue === '\n' || $glue === 'newline') {
            $glue = $this->allowNewlineChar ? PHP_EOL : null;
        }

        if ($glue === '\r' || $glue === 'return') {
            $glue = $this->allowReturnChar ? "\r" : null;
        }

        if ($glue === '\t' || $glue === 'tab') {
            $glue = $this->allowTabChar ? "\t" : null;
        }

        return $glue;
    }

    /**
     * Set any value or character that should be place at start of formatted string
     *
     * @param null|string $value
     *
     * @return $this
     */
    public function setPrepend($value)
    {
        $this->prepend = $this->prepareGlue($value);

        return $this;
    }

    /**
     * Get value or character that should be place at start of formatted string
     *
     * @return null|string
     */
    public function getPrepend()
    {
        return $this->prepend;
    }

    /**
     * @param string $string
     * @param string $value
     *
     * @return string
     */
    public function prepend($string, $value)
    {
        return $string . $value;
    }

    /**
     * Set any value or character that should be place at end of formatted string
     *
     * @param null|string $value
     *
     * @return $this
     */
    public function setAppend($value)
    {
        $this->append = $this->prepareGlue($value);

        return $this;
    }

    /**
     * Get value or character that should be place at end of formatted string
     *
     * @return null|string
     */
    public function getAppend()
    {
        return $this->append;
    }

    /**
     * @param string $string
     * @param string $value
     *
     * @return string
     */
    public function append($string, $value)
    {
        return $value . $string;
    }

    /**
     * @param string $pattern
     *
     * @return $this
     */
    public function setValueWrapPattern($pattern)
    {
        $this->valueWrapPattern = $pattern;

        return $this;
    }

    /**
     * @return string
     */
    public function getValueWrapPattern()
    {
        return $this->valueWrapPattern;
    }

    /**
     * @param string|null $field
     * @param string|null $value
     * @param string      $pattern
     *
     * @return string
     */
    public function wrapValue($field, $value, $pattern)
    {
        $pairs['{{FIELD}}']   = strtoupper($field);
        $pairs['{{field}}']   = strtolower($field);
        $pairs['{{value}}']   = $value;
        $pairs['{{newline}}'] = $this->allowNewlineChar ? PHP_EOL : null;
        $pairs['{{return}}']  = $this->allowReturnChar ? "\r" : null;
        $pairs['{{tab}}']     = $this->allowTabChar ? "\t" : null;

        return str_replace(array_keys($pairs), $pairs, $pattern);
    }

    /**
     * If included fields empty, all fields will be included
     *
     * @param array $fields
     *
     * @return $this
     */
    public function setIncludedFields(array $fields)
    {
        $this->includedFields = $fields;

        return $this;
    }

    /**
     * If included fields empty, all fields will be included
     *
     * @return array
     */
    public function getIncludedFields()
    {
        return $this->includedFields;
    }

    /**
     * @param array $array
     */
    public function filterArrayByIncludedFields(array &$array)
    {
        if ($this->getIncludedFields()) {
            $newArray = [];
            foreach ($this->getIncludedFields() as $field => $null) {
                $newArray[$field] = isset($array[$field]) ? $array[$field] : null;
            }
            $array = $newArray;
        }
    }

    /**
     * @param \Magento\Framework\DataObject $object
     */
    public function filterObjectByIncludedFields(\Magento\Framework\DataObject &$object)
    {
        $data = $object->getData();
        $this->filterArrayByIncludedFields($data);
        $object->setData($data);
    }

    /**
     * @param array|\Magento\Framework\DataObject $item
     *
     * @return array|\Magento\Framework\DataObject
     */
    public function filterByIncludedFields(&$item)
    {
        if ($this->getIncludedFields()) {
            is_array($item) ?
                $this->filterArrayByIncludedFields($item) :
                $this->filterObjectByIncludedFields($item);
        }

        return $item;
    }

    /**
     * If included fields empty, all fields will be included
     *
     * @param array $fields
     *
     * @return $this
     */
    public function setExcludedFields(array $fields)
    {
        $this->excludedFields = $fields;

        return $this;
    }

    /**
     * If included fields empty, all fields will be included
     *
     * @return array
     */
    public function getExcludedFields()
    {
        return $this->excludedFields;
    }

    /**
     * @param array $array
     */
    public function filterArrayByExcludedFields(array &$array)
    {
        foreach ($this->getExcludedFields() as $field => $null) {
            if (isset($array[$field])) {
                unset($array[$field]);
            }
        }
    }

    /**
     * @param \Magento\Framework\DataObject $object
     */
    public function filterObjectByExcludedFields(\Magento\Framework\DataObject &$object)
    {
        $data = $object->getData();
        $this->filterArrayByExcludedFields($data);
        $object->setData($data);
    }

    /**
     * @param array|\Magento\Framework\DataObject $item
     *
     * @return array|\Magento\Framework\DataObject
     */
    public function filterByExcludedFields(&$item)
    {
        if ($this->getExcludedFields()) {
            is_array($item) ?
                $this->filterArrayByExcludedFields($item) :
                $this->filterObjectByExcludedFields($item);
        }

        return $item;
    }

    /**
     * @param array|\Magento\Framework\DataObject $item
     *
     * @return string|array|\Magento\Framework\DataObject
     */
    public function format($item)
    {
        if ($item === null || $item === false) {
            return null;
        }

        $mapper = $this->getSystemFieldMapper();
        if ($mapper) {
            $item = $this->getSystemFieldMapper()->map($item);
        }

        $this->filterByIncludedFields($item);
        $this->filterByExcludedFields($item);

        if (is_array($item)) {
            $item = $this->objectFactory->create(['data' => $item]);
        }

        $this->executeIterators($item);

        $array = $item->getData();

        $this->helper->removeObjects($array);

        /** remove subarrays and subobjects */
        foreach ($array as $field => &$value) {
            if (is_array($value)) {
                $this->helper->removeObjects($value);
                $this->helper->removeArrays($value);
                $value = is_array($value) ? implode($this->getGlue(), $value) : $value;
            }

            if ($this->getValueWrapPattern()) {
                $value = $this->wrapValue($field, $value, $this->getValueWrapPattern());
            }
        }

        $mapper = $this->getCustomFieldMapper();
        if ($mapper) {
            $array = $this->getCustomFieldMapper()->map($array);
        }

        switch ($this->getFormat()) {
            case 'array':
                $result = $array;
                break;
            case 'object':
                $result = $this->objectFactory->create(['data' => $array]);
                break;
            default:
                $result = $this->append(
                    $this->getAppend(),
                    $this->prepend(
                        $this->getPrepend(),
                        implode($this->getGlue(), $array)
                    )
                );
        }

        return $result;
    }
}

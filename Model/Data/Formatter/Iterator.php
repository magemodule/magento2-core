<?php

namespace MageModule\Core\Model\Data\Formatter;

class Iterator extends \MageModule\Core\Model\Data\Formatter
{
    /**
     * @var \MageModule\Core\Model\Data\FormatterInterface
     */
    private $formatter;

    /**
     * Formatter constructor.
     *
     * @param \Magento\Framework\DataObjectFactory           $objectFactory
     * @param \MageModule\Core\Helper\Data                   $helper
     * @param \MageModule\Core\Model\Data\FormatterInterface $formatter
     * @param \MageModule\Core\Model\Data\Mapper|null        $systemFieldMapper
     * @param \MageModule\Core\Model\Data\Mapper|null        $customFieldMapper
     * @param array                                          $iterators
     * @param string                                         $format
     * @param string|array|null                              $glue
     * @param string|array|null                              $prepend
     * @param string|array|null                              $append
     * @param string|null                                    $valueWrapPattern
     * @param array                                          $includedFields
     * @param array                                          $excludedFields
     * @param bool                                           $allowNewlineChar
     * @param bool                                           $allowReturnChar
     * @param bool                                           $allowTabChar
     */
    public function __construct(
        \Magento\Framework\DataObjectFactory $objectFactory,
        \MageModule\Core\Helper\Data $helper,
        \MageModule\Core\Model\Data\FormatterInterface $formatter,
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
        parent::__construct(
            $objectFactory,
            $helper,
            $systemFieldMapper,
            $customFieldMapper,
            $iterators,
            $format,
            $glue,
            $prepend,
            $append,
            $valueWrapPattern,
            $includedFields,
            $excludedFields,
            $allowNewlineChar,
            $allowReturnChar,
            $allowTabChar
        );
        $this->formatter = $formatter;
    }

    /**
     * @param array|\Magento\Framework\DataObject $items
     *
     * @return array|\Magento\Framework\DataObject
     */
    public function iterate($items)
    {
        if (is_array($items)) {
            foreach ($items as &$item) {
                $item = $this->formatter->format($item);
                if ($this->getValueWrapPattern()) {
                    $item = $this->wrapValue(null, $item, $this->getValueWrapPattern());
                }
            }
            $items = $this->append(
                $this->prepend(
                    implode($this->getGlue(), $items),
                    $this->getPrepend()
                ),
                $this->getAppend()
            );
        }

        if ($items instanceof \Magento\Framework\DataObject) {
            $data = $items->getData();
            foreach ($data as &$item) {
                $item = $this->formatter->format($item);
                if ($this->getValueWrapPattern()) {
                    $item = $this->wrapValue(null, $item, $this->getValueWrapPattern());
                }
            }
            $items = $this->append(
                $this->prepend(
                    implode($this->getGlue(), $data),
                    $this->getPrepend()
                ),
                $this->getAppend()
            );
        }

        return $items;
    }
}

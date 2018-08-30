<?php

namespace MageModule\Core\Ui\Component\Listing\Column\Options;

class Yesno implements \Magento\Framework\Data\OptionSourceInterface
{
    const OPTION_VALUE_YES = '1';
    const OPTION_VALUE_NO = '0';

    /**
     * @var array
     */
    protected $options;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options[self::OPTION_VALUE_YES]['label'] = 'Yes';
            $this->options[self::OPTION_VALUE_YES]['value'] = self::OPTION_VALUE_YES;

            $this->options[self::OPTION_VALUE_NO]['label'] = 'No';
            $this->options[self::OPTION_VALUE_NO]['value'] = self::OPTION_VALUE_NO;
        }

        return $this->options;
    }
}

<?php

namespace MageModule\Core\Model\Data\Sanitizer;

class DateTime implements \MageModule\Core\Model\Data\SanitizerInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    private $dateTime;

    /**
     * Date constructor.
     *
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     */
    public function __construct(\Magento\Framework\Stdlib\DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function sanitize($value)
    {
        try {
            $value = $this->dateTime->formatDate(
                str_replace(['//', '/', '--'], '-', $value),
                true
            );
        } catch (\Exception $e){
            $value = null;
        }

        return $value;
    }
}

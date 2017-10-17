<?php

namespace MageModule\Core\Model\Data\Sanitizer;

class Date implements \MageModule\Core\Model\Data\SanitizerInterface
{
    /**
     * @param string $value
     *
     * @return string
     */
    public function sanitize($value)
    {
        if($value !== null){
            $value = str_replace(['//','/','--'], '-', $value);
        }

        return $value;
    }
}

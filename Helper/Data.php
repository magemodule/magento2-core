<?php

namespace MageModule\Core\Helper;

class Data
{
    /**
     * Nullifies any string with a length of 0
     *
     * @param array $array
     */
    public function nullifyEmpty(array &$array)
    {
        $array = array_map(function ($value) {
            if (!is_array($value) && !is_object($value) && $value === '') {
                $value = null;
            }

            return $value;
        },
            $array);
    }

    /**
     * @param array $array
     */
    public function removeObjects(array &$array)
    {
        $array = array_filter(
            $array,
            function ($value) {
                return !is_object($value);
            }
        );
    }

    /**
     * @param array $array
     */
    public function removeArrays(array &$array)
    {
        $array = array_filter(
            $array,
            function ($value) {
                return !is_object($value) && !is_array($value);
            }
        );
    }

    /**
     * Takes a multidimensional array and makes sure that all subarrays have the exact same keys
     *
     * @param array $array
     */
    public function equalizeArrayKeys(array &$array)
    {
        /** note to self: using nested for each to ensure that numeric array keys are preserved */

        $fields = [];
        foreach ($array as &$subarray) {
            foreach ($subarray as $key => $value) {
                $fields[$key] = null;
            }
        }

        foreach ($array as &$subarray) {
            $newData = $fields;
            foreach ($fields as $field => $null) {
                if (isset($subarray[$field])) {
                    $newData[$field] = $subarray[$field];
                }
            }
            $subarray = $newData;
            $newData  = null;
        }
    }

    /**
     * Takes the array keys from the first element in array and adds them as the first
     * subarray to create csv headers row. $this->equalizeArrayKeys() should be run first
     *
     * @param array $array
     */
    public function addHeadersRowToArray(array &$array)
    {
        reset($array);
        $row = current($array);
        if ($row) {
            $fields = array_keys($row);
            array_unshift($array, $fields);
        }
    }

    /**
     * Adds prefix to all array keys or \Magento\Framework\DataObject keys
     *
     * @param string                              $prefix
     * @param array|\Magento\Framework\DataObject $item
     */
    public function addPrefix($prefix, &$item)
    {
        $isObject = $item instanceof \Magento\Framework\DataObject;
        $array    = $isObject ? $item->getData() : $item;

        $newArray = [];
        foreach ($array as $key => &$value) {
            $newArray[$prefix . $key] = $value;
        }

        $array = $newArray;

        if ($isObject) {
            $item->setData($array);
        } else {
            $item = $array;
        }
    }

    /**
     * Stringifys subarray paths into first_level_key/second_level_key/third_level_key, etc
     *
     * @param array $array
     * @param array $path
     *
     * @return array
     */
    public function stringifyPaths(array $array, array $path = [])
    {
        $result = [];
        foreach ($array as $key => $val) {
            $currentPath = array_merge($path, [$key]);
            if (is_array($val)) {
                $result[] = join('/', $currentPath);
                $result   = array_merge($result, $this->stringifyPaths($val, $currentPath));
            } else {
                $result[] = join('/', $currentPath);
            }
        }

        return $result;
    }
}

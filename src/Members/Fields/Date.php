<?php

namespace EeObjects\Members\Fields;

use EeObjects\Members\AbstractField;

class Date extends AbstractField
{
    /**
     * @param $value
     * @return \DateTime|mixed
     */
    public function read($value)
    {
        $date = new \DateTime();

        return $date->setTimestamp($value);
    }

    /**
     * @param $value
     * @return mixed|string
     */
    public function prepValueForStorage($value)
    {
        if ($value instanceof \DateTime) {
            return $value->format('U');
        }
    }
}

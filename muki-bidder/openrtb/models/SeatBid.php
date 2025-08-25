<?php

namespace openrtb\models;

class SeatBid extends \openrtb\abstractions\BaseModel
{

    protected $attributes = array(
        'bid' => array(
            'type' => self::ATTR_ARRAY,
            'sub_type' => 'openrtb\models\Bid',
            'required' => true
        ),
        'seat' => array(
            'type' => self::ATTR_STRING
        ),
        'group' => array(
            'type' => self::ATTR_INTEGER,
            'default_value' => 0
        ),
        'ext' => array(
            'type' => 'openrtb\models\Extension'
        )
    );

}
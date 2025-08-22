<?php

namespace openrtb\models;

class Format extends \openrtb\abstractions\BaseModel
{
    protected $attributes = array(
        'w' => array(
            'type' => self::ATTR_INTEGER,
        ),
        'h' => array(
            'type' => self::ATTR_INTEGER,
        ),
        'ext' => array(
            'type' => 'openrtb\\models\\Extension',
            'optional' => true,
        ),
    );
}


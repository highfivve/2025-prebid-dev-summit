<?php

namespace openrtb\models;

class Impression extends \openrtb\abstractions\BaseModel
{

    protected $attributes = array(
        'id' => array(
            'required' => true,
            'type' => self::ATTR_ID,
        ),
        'banner' => array(
            'type' => 'openrtb\models\Banner',
        ),
        'video' => array(
            'type' => 'openrtb\models\Video',
        ),
//        'audio' => array(
//            'type' => 'openrtb\models\Audio',
//        ),
        'native' => array(
            'type' => 'openrtb\models\Native',
        ),
        'pmp' => array(
            'type' => 'openrtb\models\PMP',
        ),
        'displaymanager' => array(
            'type' => self::ATTR_STRING,
        ),
        'displaymanagerver' => array(
            'type' => self::ATTR_STRING,
        ),
        'instl' => array(
            'type' => self::ATTR_INTEGER,
            'default_value' => 0,
        ),
        'tagid' => array(
            'type' => self::ATTR_STRING,
        ),
        'bidfloor' => array(
            'type' => self::ATTR_FLOAT,
            'default_value' => 0.0,
        ),
        'bidfloorcur' => array(
            'type' => self::ATTR_STRING,
            'default_value' => 'USD',
        ),
        'clickbrower' => array(
            'type' => self::ATTR_INTEGER,
        ),
        'secure' => array(
            'type' => self::ATTR_INTEGER,
        ),
        'iframebuster' => array(
            'type' => self::ATTR_ARRAY,
            'sub_type' => self::ATTR_STRING,
        ),
        'exp' => array(
            'type' => self::ATTR_INTEGER,
        ),
        'ext' => array(
            'type' => 'openrtb\models\Extension',
        ),
    );

}

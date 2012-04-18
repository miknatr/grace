<?php

namespace Grace\Test\ORM;

use Grace\ORM\Mapper;

class OrderMapper extends Mapper {
    protected $fields = array(
        'id',
        'name',
        'phone',
    );
}
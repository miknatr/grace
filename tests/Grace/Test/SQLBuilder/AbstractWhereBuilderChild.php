<?php

namespace Grace\Test\SQLBuilder;

use Grace\SQLBuilder\AbstractWhereBuilder;

class AbstractWhereBuilderChild extends AbstractWhereBuilder
{
    public function getQueryString()
    {
        //It's plug
        ;
    }
    public function getWhereSql()
    {
        return parent::getWhereSql();
    }
    public function getQueryArguments()
    {
        return parent::getQueryArguments();
    }
}
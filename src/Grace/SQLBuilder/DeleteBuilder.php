<?php

namespace Grace\SQLBuilder;

class DeleteBuilder extends AbstractWhereBuilder
{
    protected function getQueryString()
    {
        return 'DELETE FROM `' . $this->from . '`' . $this->getWhereSql();
    }
}
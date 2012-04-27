<?php

namespace Grace\SQLBuilder;

class SelectBuilder extends AbstractWhereBuilder
{
    protected $fields = '*';
    protected $joinSql = '';
    protected $groupSql = '';
    protected $havingSql = '';
    protected $orderSql = '';
    protected $limitSql;

    public function count()
    {
        //TODO id - magic field
        $this->fields = 'COUNT(id)';
        return $this;
    }
    public function fields($sql)
    {
        $this->fields = $sql;
        return $this;
    }
    public function join($table, $fromTableField, $joinTableField)
    {
        $this->joinSql .=
            ' JOIN `' . $table . '` ON `' . $this->from . '`.`' . $fromTableField . '`=`' . $table . '`.`' .
                $joinTableField . '`';
        return $this;
    }
    public function group($sql)
    {
        $this->groupSql = ' GROUP BY ' . $sql;
        return $this;
    }
    public function having($sql)
    {
        $this->havingSql = ' HAVING ' . $sql;
        return $this;
    }
    public function order($sql)
    {
        $this->orderSql = ' ORDER BY ' . $sql;
        return $this;
    }
    public function limit($from, $limit)
    {
        $this->limitSql = ' LIMIT ' . $from . ',' . $limit;
        return $this;
    }
    protected function getQueryString()
    {
        return 'SELECT ' . $this->fields . ' FROM `' . $this->from . '`' . $this->joinSql . $this->getWhereSql() .
            $this->groupSql . $this->havingSql . $this->orderSql . $this->limitSql;
    }
}


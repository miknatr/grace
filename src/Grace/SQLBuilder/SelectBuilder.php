<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\SQLBuilder;

/**
 * Select sql builder
 */
class SelectBuilder extends AbstractWhereBuilder
{
    protected $fields = '*';
    protected $joinSql = '';
    protected $groupSql = '';
    protected $havingSql = '';
    protected $orderSql = '';
    protected $limitSql;

    /**
     * Sets count syntax
     * @return SelectBuilder
     */
    public function count()
    {
        //TODO id - magic field
        $this->fields = 'COUNT(id)';
        return $this;
    }
    /**
     * Sets fields statement
     * @param $sql
     * @return SelectBuilder
     */
    public function fields($sql)
    {
        $this->fields = $sql;
        return $this;
    }
    /**
     * Sets join statement
     * @param $table
     * @param $fromTableField
     * @param $joinTableField
     * @return SelectBuilder
     */
    public function join($table, $fromTableField, $joinTableField)
    {
        $this->joinSql .=
            ' JOIN ' . $this->sqlEscapeSymbol . $table . $this->sqlEscapeSymbol . ' ON ' . $this->sqlEscapeSymbol . $this->from . $this->sqlEscapeSymbol . '.' . $this->sqlEscapeSymbol . $fromTableField . '`=`' . $table . '`.`' .
                $joinTableField . '`';
        return $this;
    }
    /**
     * Sets group by statement
     * @param $sql
     * @return SelectBuilder
     */
    public function group($sql)
    {
        $this->groupSql = ' GROUP BY ' . $sql;
        return $this;
    }
    /**
     * Sets having statement
     * @param $sql
     * @return SelectBuilder
     */
    public function having($sql)
    {
        $this->havingSql = ' HAVING ' . $sql;
        return $this;
    }
    /**
     * Sets order by statement
     * @param $sql
     * @return SelectBuilder
     */
    public function order($sql)
    {
        $this->orderSql = ' ORDER BY ' . $sql;
        return $this;
    }
    /**
     * Sets limit statements
     * @param $from
     * @param $limit
     * @return SelectBuilder
     */
    public function limit($from, $limit)
    {
        $this->limitSql = ' LIMIT ' . $from . ',' . $limit;
        return $this;
    }
    /**
     * @inheritdoc
     */
    protected function getQueryString()
    {
        return 'SELECT ' . $this->fields . ' FROM `' . $this->from . '`' . $this->joinSql . $this->getWhereSql() .
            $this->groupSql . $this->havingSql . $this->orderSql . $this->limitSql;
    }
}


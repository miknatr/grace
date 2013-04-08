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
    protected $fieldsArguments = array();
    protected $joinSql = '';
    protected $groupSql = '';
    protected $groupArguments = array();
    protected $havingSql = '';
    protected $havingArguments = array();
    protected $orderSql = '';
    protected $orderArguments = array();
    protected $limitSql;

    /**
     * Sets count syntax
     * @return $this
     */
    public function count()
    {
        $this->fields = 'COUNT(?f) AS ?f';
        //TODO id - magic field
        $this->fieldsArguments[] = 'id';
        $this->fieldsArguments[] = 'counter';
        return $this;
    }
    /**
     * Sets fields statement
     * @param $fields array('id', array('AsText(?f) AS ?f', array('coords', 'coords')))
     * @return $this
     */
    public function fields(array $fields)
    {
        $newFields = array();
        $this->fields = '';
        $this->fieldsArguments = array();

        foreach ($fields as $field) {
            if (is_scalar($field)) {
                $newFields[] = '?f';
                $this->fieldsArguments[] = $field;
            } else {
                if (!isset($field[0]) or !isset($field[1]) or !is_array($field[1])) {
                    throw new \BadMethodCallException('Must be exist 0 and 1 index in array and second one must be an array');
                }
                $newFields[] = $field[0];
                $this->fieldsArguments = array_merge($this->fieldsArguments, $field[1]);
            }
        }

        $this->fields = implode(', ', $newFields);

        return $this;
    }
    /**
     * Sets one field in fields statement
     * @param $field
     * @return $this
     */
    public function fieldSql($field, array $arguments = array())
    {
        $this->fields(array(array($field, $arguments)));
        return $this;
    }
    /**
     * Sets one field in fields statement
     * @param $field
     * @return $this
     */
    public function field($field)
    {
        $this->fields(array($field));
        return $this;
    }
    /**
     * Sets group by statement
     * @param $sql
     * @param $arguments
     * @return $this
     */
    public function having($sql, array $arguments)
    {
        $this->havingSql       = ' HAVING ' . $sql;
        $this->havingArguments = $arguments;
        return $this;
    }
    /**
     * Sets group by statement
     * @param $field
     * @return $this
     */
    public function group($field)
    {
        if ($this->groupSql == '') {
            $this->groupSql = ' GROUP BY ?f';
        } else {
            $this->groupSql .= ', ?f';
        }
        $this->groupArguments[] = $field;
        return $this;
    }
    /**
     * Sets asc order by statement
     * @param $field
     * @return $this
     */
    public function orderAsc($field)
    {
        $this->orderByDirection($field, 'ASC');
        return $this;
    }
    /**
     * Sets desc order by statement
     * @param $field
     * @return $this
     */
    public function orderDesc($field)
    {
        $this->orderByDirection($field, 'DESC');
        return $this;
    }
    /**
     * Sets order by statement
     * @param $field
     * @param $direction
     * @return $this
     */
    protected function orderByDirection($field, $direction)
    {
        if ($this->orderSql == '') {
            $this->orderSql = ' ORDER BY ?f ' . $direction;
        } else {
            $this->orderSql .= ', ?f ' . $direction;
        }
        $this->orderArguments[] = $field;
    }
    /**
     * Sets limit statements
     * @param $from
     * @param $limit
     * @return $this
     */
    public function limit($from, $limit)
    {
        $this->limitSql = ' LIMIT ' . $limit . ' OFFSET ' . $from;
        return $this;
    }
    /**
     * @inheritdoc
     */
    protected function getQueryString()
    {
        $aliasSql = ($this->alias != '' ? ' AS ?f' : '');

        return 'SELECT ' . $this->fields . ' FROM ?f' . $aliasSql . $this->joinSql . $this->getWhereSql() .
            $this->groupSql . $this->havingSql . $this->orderSql . $this->limitSql;
    }
    /**
     * @inheritdoc
     */
    protected function getQueryArguments()
    {
        $aliasPlaceholders = ($this->alias != '' ? array($this->alias) : array());

        $arguments = parent::getQueryArguments();

        return array_merge($this->fieldsArguments, array($this->from), $aliasPlaceholders, $arguments, $this->groupArguments, $this->havingArguments, $this->orderArguments);
    }
}

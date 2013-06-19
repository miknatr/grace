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
class SelectBuilder extends WhereBuilderAbstract
{
    protected $fields = '*';
    protected $fieldsArguments = array();
    protected $joins = array();
    protected $lastJoinAlias = '';
    protected $joinArguments = array();
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
                $this->fieldsArguments[] = $this->alias . '.' . $field;
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
     * @param array $arguments
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
     * Sets one field in fields statement
     * @param $tableName
     * @param $onSql
     * @param array $arguments
     * @return $this
     */
    public function join($tableName, $alias = null)
    {
        $this->joins[] = ' LEFT JOIN ?f as ?f';
        $this->lastJoinAlias = $alias;
        $this->joinArguments[] = $tableName;
        $this->joinArguments[] = $alias;
        return $this;
    }

    public function onEq($localField, $foreignField)
    {
        if (empty($this->joins)) {
            throw new \LogicException('Select builder error: onEq() called before join()');
        }

        $lastJoinIndex = count($this->joins) - 1;
        if ($this->joins[$lastJoinIndex] == ' LEFT JOIN ?f as ?f') {
            $this->joins[$lastJoinIndex] .= ' ON';
        } else {
            $this->joins[$lastJoinIndex] .= ' AND';
        }

        $this->joins[$lastJoinIndex] .= ' ?f = ?f';
        $this->joinArguments[] = "{$this->alias}.{$localField}";
        $this->joinArguments[] = "{$this->lastJoinAlias}.{$foreignField}";

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
        $this->groupArguments[] = $this->alias . '.' . $field;
        return $this;
    }
    /**
     * Sets asc order by statement
     * @param $field
     * @return $this
     */
    public function orderAsc($field)
    {
        $this->orderByDirection($this->alias . '.' . $field, 'ASC');
        return $this;
    }
    /**
     * Sets desc order by statement
     * @param $field
     * @return $this
     */
    public function orderDesc($field)
    {
        $this->orderByDirection($this->alias . '.' . $field, 'DESC');
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
        return 'SELECT ' . $this->fields . ' FROM ?f AS ?f' . join('', $this->joins) . $this->getWhereSql() .
            $this->groupSql . $this->havingSql . $this->orderSql . $this->limitSql;
    }
    /**
     * @inheritdoc
     */
    protected function getQueryArguments()
    {
        $arguments = parent::getQueryArguments();
        return array_merge($this->fieldsArguments, array($this->from, $this->alias), $this->joinArguments, $arguments, $this->groupArguments, $this->havingArguments, $this->orderArguments);
    }


    //
    // OVERRIDE WHERE BUILDER METHODS FOR ADDING ALIAS
    //

    /**
     * Adds '=' statement into where statement
     * @param $field
     * @param $value
     * @return $this
     */
    public function eq($field, $value)
    {
        return parent::eq($this->alias . '.' . $field, $value);
    }

    /**
     * Adds '!=' statement into where statement
     * @param $field
     * @param $value
     * @return $this
     */
    public function notEq($field, $value)
    {
        return parent::notEq($this->alias . '.' . $field, $value);
    }

    /**
     * Adds '>' statement into where statement
     * @param $field
     * @param $value
     * @return $this
     */
    public function gt($field, $value)
    {
        return parent::gt($this->alias . '.' . $field, $value);
    }

    /**
     * Adds '>=' statement into where statement
     * @param $field
     * @param $value
     * @return $this
     */
    public function gtEq($field, $value)
    {
        return parent::gtEq($this->alias . '.' . $field, $value);
    }

    /**
     * Adds '<' statement into where statement
     * @param $field
     * @param $value
     * @return $this
     */
    public function lt($field, $value)
    {
        return parent::lt($this->alias . '.' . $field, $value);
    }

    /**
     * Adds '<=' statement into where statement
     * @param $field
     * @param $value
     * @return $this
     */
    public function ltEq($field, $value)
    {
        return parent::ltEq($this->alias . '.' . $field, $value);
    }

    /**
     * Adds LIKE statement into where statement
     * @param $field
     * @param $value
     * @return $this
     */
    public function like($field, $value)
    {
        return parent::like($this->alias . '.' . $field, $value);
    }

    /**
     * Adds NOT LIKE statement into where statement
     * @param $field
     * @param $value
     * @return $this
     */
    public function notLike($field, $value)
    {
        return parent::notLike($this->alias . '.' . $field, $value);
    }

    /**
     * Adds LIKE '%value%' statement into where statement
     * @param $field
     * @param $value
     * @return $this
     */
    public function likeInPart($field, $value)
    {
        return parent::likeInPart($this->alias . '.' . $field, $value);
    }

    /**
     * Adds NOT LIKE '%value%' statement into where statement
     * @param $field
     * @param $value
     * @return $this
     */
    public function notLikeInPart($field, $value)
    {
        return parent::notLikeInPart($this->alias . '.' . $field, $value);
    }

    /**
     * Adds IN statement into where statement
     * @param       $field
     * @param array $values
     * @return $this
     */
    public function in($field, array $values)
    {
        return parent::in($this->alias . '.' . $field, $values);
    }

    /**
     * Adds NOT IN statement into where statement
     * @param       $field
     * @param array $values
     * @return $this
     */
    public function notIn($field, array $values)
    {
        return parent::notIn($this->alias . '.' . $field, $values);
    }

    /**
     * Adds BETWEEN statement into where statement
     * @param $field
     * @param $value1
     * @param $value2
     * @return $this
     */
    public function between($field, $value1, $value2)
    {
        return parent::between($this->alias . '.' . $field, $value1, $value2);
    }

    /**
     * Adds NOT BETWEEN statement into where statement
     * @param $field
     * @param $value1
     * @param $value2
     * @return $this
     */
    public function notBetween($field, $value1, $value2)
    {
        return parent::notBetween($this->alias . '.' . $field, $value1, $value2);
    }
}

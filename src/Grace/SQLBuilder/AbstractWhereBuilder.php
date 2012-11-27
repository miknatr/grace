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
 * Provides some base functions for builders with where statements
 */
abstract class AbstractWhereBuilder extends AbstractBuilder
{
    private $arguments = array();
    private $whereSqlConditions = array();

    /**
     * Adds sql statement into where statement
     * @param       $sql
     * @param array $values
     * @return AbstractWhereBuilder
     */
    public function sql($sql, array $values = array())
    {
        $this->whereSqlConditions[] = $sql;
        $this->arguments            = array_merge($this->arguments, $values);
        return $this;
    }
    /**
     * @param $field
     * @param $value
     * @param $operator
     * @return AbstractWhereBuilder
     */
    protected function setTwoArgsOperator($field, $value, $operator)
    {
        $this->whereSqlConditions[] = $field . '' . $operator . '?q';
        $this->arguments[]          = $value;
        return $this;
    }
    /**
     * Adds '=' statement into where statement
     * @param $field
     * @param $value
     * @return AbstractWhereBuilder
     */
    public function eq($field, $value)
    {
        return $this->setTwoArgsOperator($field, $value, '=');
    }
    /**
     * Adds '!=' statement into where statement
     * @param $field
     * @param $value
     * @return AbstractWhereBuilder
     */
    public function notEq($field, $value)
    {
        return $this->setTwoArgsOperator($field, $value, '!=');
    }
    /**
     * Adds '>' statement into where statement
     * @param $field
     * @param $value
     * @return AbstractWhereBuilder
     */
    public function gt($field, $value)
    {
        return $this->setTwoArgsOperator($field, $value, '>');
    }
    /**
     * Adds '>=' statement into where statement
     * @param $field
     * @param $value
     * @return AbstractWhereBuilder
     */
    public function gtEq($field, $value)
    {
        return $this->setTwoArgsOperator($field, $value, '>=');
    }
    /**
     * Adds '<' statement into where statement
     * @param $field
     * @param $value
     * @return AbstractWhereBuilder
     */
    public function lt($field, $value)
    {
        return $this->setTwoArgsOperator($field, $value, '<');
    }
    /**
     * Adds '<=' statement into where statement
     * @param $field
     * @param $value
     * @return AbstractWhereBuilder
     */
    public function ltEq($field, $value)
    {
        return $this->setTwoArgsOperator($field, $value, '<=');
    }
    /**
     * Adds LIKE statement into where statement
     * @param $field
     * @param $value
     * @return AbstractWhereBuilder
     */
    public function like($field, $value)
    {
        return $this->setTwoArgsOperator($field, $value, ' LIKE ');
    }
    /**
     * Adds NOT LIKE statement into where statement
     * @param $field
     * @param $value
     * @return AbstractWhereBuilder
     */
    public function notLike($field, $value)
    {
        return $this->setTwoArgsOperator($field, $value, ' NOT LIKE ');
    }
    /**
     * Adds LIKE '%value%' statement into where statement
     * @param $field
     * @param $value
     * @return AbstractWhereBuilder
     */
    public function likeInPart($field, $value)
    {
        return $this->setTwoArgsOperator($field, '%' . $value . '%', ' LIKE ');
    }
    /**
     * Adds NOT LIKE '%value%' statement into where statement
     * @param $field
     * @param $value
     * @return AbstractWhereBuilder
     */
    public function notLikeInPart($field, $value)
    {
        return $this->setTwoArgsOperator($field, '%' . $value . '%', ' NOT LIKE ');
    }
    /**
     * @param       $field
     * @param array $values
     * @param       $operator
     * @return AbstractWhereBuilder
     */
    protected function setInOperator($field, array $values, $operator)
    {
        $this->whereSqlConditions[] =
            $field . ' ' . $operator . ' (' . substr(str_repeat('?q,', count($values)), 0, -1) . ')';
        $this->arguments            = array_merge($this->arguments, $values);
        return $this;
    }
    /**
     * Adds IN statement into where statement
     * @param       $field
     * @param array $values
     * @return AbstractWhereBuilder
     */
    public function in($field, array $values)
    {
        return $this->setInOperator($field, $values, 'IN');
    }
    /**
     * Adds NOT IN statement into where statement
     * @param       $field
     * @param array $values
     * @return AbstractWhereBuilder
     */
    public function notIn($field, array $values)
    {
        return $this->setInOperator($field, $values, 'NOT IN');
    }
    /**
     * @param $field
     * @param $value1
     * @param $value2
     * @param $operator
     * @return AbstractWhereBuilder
     */
    protected function setBetweenOperator($field, $value1, $value2, $operator)
    {
        $this->whereSqlConditions[] = $field . ' ' . $operator . ' ?q AND ?q';
        $this->arguments[]          = $value1;
        $this->arguments[]          = $value2;
        return $this;
    }
    /**
     * Adds BETWEEN statement into where statement
     * @param $field
     * @param $value1
     * @param $value2
     * @return AbstractWhereBuilder
     */
    public function between($field, $value1, $value2)
    {
        return $this->setBetweenOperator($field, $value1, $value2, 'BETWEEN');
    }
    /**
     * Adds NOT BETWEEN statement into where statement
     * @param $field
     * @param $value1
     * @param $value2
     * @return AbstractWhereBuilder
     */
    public function notBetween($field, $value1, $value2)
    {
        return $this->setBetweenOperator($field, $value1, $value2, 'NOT BETWEEN');
    }
    /**
     * @inheritdoc
     */
    protected function getQueryArguments()
    {
        return $this->arguments;
    }
    /**
     * @return string
     */
    protected function getWhereSql()
    {
        if (count($this->whereSqlConditions) == 0) {
            return '';
        }
        return ' WHERE ' . implode(' AND ', $this->whereSqlConditions);
    }
}

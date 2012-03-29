<?php

namespace Grace\SQLBuilder;

abstract class AbstractWhereBuilder extends AbstractBuilder {
    protected $arguments = array();
    protected $whereSql = '';

    protected function setTwoArgsOperator($field, $value, $operator) {
        $this->whereSql .= ' ' . $field . '' . $operator . '\'?e\'';
        $this->arguments[] = $value;
        return $this;
    }
    public function eq($field, $value) {
        return $this->setTwoArgsOperator($field, $value, '=');
    }
    public function notEq($field, $value) {
        return $this->setTwoArgsOperator($field, $value, '=');
    }
    public function gt($field, $value) {
        return $this->setTwoArgsOperator($field, $value, '>');
    }
    public function gtEq($field, $value) {
        return $this->setTwoArgsOperator($field, $value, '>=');
    }
    public function lt($field, $value) {
        return $this->setTwoArgsOperator($field, $value, '<');
    }
    public function ltEq($field, $value) {
        return $this->setTwoArgsOperator($field, $value, '<=');
    }
    public function like($field, $value) {
        return $this->setTwoArgsOperator($field, $value, ' LIKE ');
    }
    public function notLike($field, $value) {
        return $this->setTwoArgsOperator($field, $value, ' NOT LIKE ');
    }
    public function likeInPart($field, $value) {
        return $this->setTwoArgsOperator($field, '%' . $value . '%', ' LIKE ');
    }
    public function notLikeInPart($field, $value) {
        return $this->setTwoArgsOperator($field, '%' . $value . '%', ' NOT LIKE ');
    }
    protected function setInOperator($field, array $values, $operator) {
        $this->whereSql .= $field . ' ' . $operator
            . ' (' . substr(str_repeat('?q,', count($values)), 0, -1) . ')';
        $this->arguments = array_merge($this->arguments, $values);
        return $this;
    }
    public function in($field, array $values) {
        return $this->setInOperator($field, $values, 'IN');
    }
    public function notIn($field, array $values) {
        return $this->setInOperator($field, $values, 'NOT IN');
    }
    protected function setBetweenOperator($field, $value1, $value2, $operator) {
        $this->whereSql .= ' ' . $field . ' ' . $operator . ' \'?e\' AND \'?e\'';
        $this->arguments[] = $value1;
        $this->arguments[] = $value2;
        return $this;
    }
    public function between($field, $value1, $value2) {
        return $this->setBetweenOperator($field, $value1, $value2, 'BETWEEN');
    }
    public function notBetween($field, $value1, $value2) {
        return $this->setBetweenOperator($field, $value1, $value2, 'NOT BETWEEN');
    }
    
    protected function getQueryArguments() {
        return $this->arguments;
    }
}
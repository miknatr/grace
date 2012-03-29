<?php

class Model_ConditionElement
{

    public $field = null;
    public $value = '';
    public $operator = '';
    public $valueForEscaping = '';

    public function __construct($field)
    {
        $this->field = $field;
    }
}

class Model_ConditionSql
{

    public $sql = '';
    public $values = array();

    public function __construct(array $sqlAndArgs)
    {
        $this->sql = $sqlAndArgs[0];
        unset($sqlAndArgs[0]);
        $this->values = array_values($sqlAndArgs);
    }
}

class Model_Condition
{

    protected $_conditions = array();
    protected $_order = '';
    protected $_limit = '';
    protected $_mapper = '';
    protected $_fieldToOperate = null;
    public function __construct(Model_Mapper $mapper)
    {
        $this->_mapper = $mapper;
    }

    public function all()
    {
        return $this->_mapper->all($this);
    }
    public function one()
    {
        return $this->_mapper->one($this);
    }
    public function count()
    {
        return $this->_mapper->count($this);
    }
    public function column($column)
    {
        return $this->_mapper->column($column, $this);
    }
    public function update($row)
    {
        return $this->_mapper->update($row, $this);
    }
    public function delete()
    {
        return $this->_mapper->delete($this);
    }

    public function sql()
    {
        $args = func_get_args();
        $this->_conditions[] = new Model_ConditionSql($args);
        return $this;
    }
    public function cond($field, $value, $operator = '=', $valueForEscaping = '')
    {
        $cond = new Model_ConditionElement($field);
        $cond->value = $value;
        $cond->operator = $operator;
        $cond->valueForEscaping = $valueForEscaping;
        $this->_conditions[] = $cond;
        return $this;
    }
    public function __get($name)
    {
        $this->_fieldToOperate = $name;
        return $this;
    }
    public function __call($name, array $args)
    {
        if ($this->_fieldToOperate == null) {
             return;
        }

        $cond = new Model_ConditionElement($this->_fieldToOperate);
        $this->_fieldToOperate = null;

        switch ($name) {
            case 'eq':
            case 'equals':
                $cond->value = $args[0];
                $cond->operator = '=';
                break;
            case 'notEq':
            case 'notEquals':
                $cond->value = $args[0];
                $cond->operator = '!=';
                break;
            case 'gt':
            case 'greaterThan':
                $cond->value = $args[0];
                $cond->operator = '>';
                break;
            case 'gtEq':
            case 'greaterThanOrEquals':
                $cond->value = $args[0];
                $cond->operator = '>=';
                break;
            case 'lt':
            case 'lowerThan':
                $cond->value = $args[0];
                $cond->operator = '<';
                break;
            case 'ltEq':
            case 'lowerThanOrEquals':
                $cond->value = $args[0];
                $cond->operator = '<=';
                break;
            case 'in':
                if (count($args) > 1) {
                    $cond->value = $args;
                } elseif (is_array($args[0])) {
                    $cond->value = $args[0];
                } else {
                    $cond->value = explode(',', $args[0]);
                }
                $cond->valueForEscaping = '(' . substr(str_repeat('?,', count($cond->value)), 0, -1) . ')';
                $cond->operator = 'IN';
                break;
            case 'notIn':
                if (count($args) > 1) {
                    $cond->value = $args;
                } elseif (is_array($args[0])) {
                    $cond->value = $args[0];
                } else {
                    $cond->value = explode(',', $args[0]);
                }
                $cond->valueForEscaping = '(' . substr(str_repeat('?,', count($cond->value)), 0, -1) . ')';
                $cond->operator = 'IN';
                break;
            case 'between':
                $cond->value = array($args[0], $args[1]);
                $cond->valueForEscaping = '? AND ?';
                $cond->operator = 'BETWEEN';
                break;
            case 'notBetween':
                $cond->value = array($args[0], $args[1]);
                $cond->valueForEscaping = '? AND ?';
                $cond->operator = 'NOT BETWEEN';
                break;
            case 'like':
                $cond->value = $args[0];
                $cond->operator = 'LIKE';
                break;
            case 'notLike':
                $cond->value = $args[0];
                $cond->operator = 'NOT LIKE';
                break;
            case 'likePartly':
                $cond->value = '%' . $args[0] . '%';
                $cond->operator = 'LIKE';
                break;
            case 'notLikePartly':
                $cond->value = '%' . $args[0] . '%';
                $cond->operator = 'NOT LIKE';
                break;
        }

        $this->_conditions[] = $cond;
        return $this;
    }

    public function limit($start, $number = null)
    {
        $this->_limit = ($number === null ? '0,' . $start : $start . ',' . $number);
        return $this;
    }
    public function order($sql)
    {
        $this->_order = $sql;
        return $this;
    }

    public function getLimitSql()
    {
        if ($this->_limit != '') {
            return  ' LIMIT ' . $this->_limit;
        } else {
            return '';
        }
    }
    public function getOrderSql()
    {
        if ($this->_order != '') {
            return  ' ORDER BY ' . $this->_order;
        } else {
            return '';
        }
    }
    public function getWhereSql()
    {
        if (count($this->_conditions) == 0) {
            return  '';
        } else {
            $r = array();
            foreach ($this->_conditions as $cr) {
                if ($cr instanceof Model_ConditionElement) {
                    $value = ($cr->valueForEscaping != '' ? $cr->valueForEscaping : '?');
                    $r[] = $cr->field . ' ' . $cr->operator . ' ' . $value;
                } elseif ($cr instanceof Model_ConditionSql) {
                    $r[] = $cr->sql;
                } else {
                    trigger_error('Model_Condition->getWhereSql(): Bad condition');
                }
            }
            return  ' WHERE ' . implode(' AND ', $r);
        }
    }
    public function getArgs()
    {
        $r = array();
        foreach ($this->_conditions as $cr) {
            if ($cr instanceof Model_ConditionElement) {
                if (is_array($cr->value)) {
                    foreach ($cr->value as $v) {
                        $r[] = $v;
                    }
                } else {
                    $r[] = $cr->value;
                }
            } elseif ($cr instanceof Model_ConditionSql) {
                foreach ($cr->values as $v) {
                    $r[] = $v;
                }
            } else {
                trigger_error('Model_Condition->getArgs(): Bad condition');
            }
        }
        return $r;
    }
}

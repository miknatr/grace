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
 * Insert sql builder
 */
class InsertBuilder extends AbstractBuilder
{
    private $fieldsSql = '';
    private $valuesSql = '';
    private $fieldValues = array();

    /**
     * Prepares values for inserting into db
     * @param array $values
     * @return $this
     */
    public function values(array $values)
    {
        $this->fieldsSql   = '`' . implode('`, `', array_keys($values)) . '`';

        $this->valuesSql   = array();
        $this->fieldValues = array();

        foreach ($values as $k => $v) {
            if (is_object($v) and $v instanceof SqlValueInterface) {
                $this->valuesSql[] = $v->getSql();
                $this->fieldValues = array_merge($this->fieldValues, $v->getValues());
            } else {
                $this->valuesSql[] = '?q';
                $this->fieldValues[] = $v;
            }
        }

        $this->valuesSql = implode(', ', $this->valuesSql);

        return $this;
    }
    /**
     * @inheritdoc
     */
    protected function getQueryString()
    {
        if (count($this->fieldValues) == 0) {
            throw new ExceptionCallOrder('Set values for insert before execute');
        }
        return 'INSERT INTO `' . $this->from . '` (' . $this->fieldsSql . ')' . ' VALUES (' . $this->valuesSql . ')';
    }
    /**
     * @inheritdoc
     */
    protected function getQueryArguments()
    {
        return $this->fieldValues;
    }
}
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
     * @return InsertBuilder
     */
    public function values(array $values)
    {
        $this->fieldsSql   = '`' . implode('`, `', array_keys($values)) . '`';
        $this->valuesSql   = substr(str_repeat('?q, ', count($values)), 0, -2);
        $this->fieldValues = array_values($values);
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
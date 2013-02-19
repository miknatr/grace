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

class UpdateBuilder extends AbstractWhereBuilder
{
    private $fieldsSql = '';
    private $fieldValues = array();

    /**
     * @param array $values
     * @return $this
     */
    public function values(array $values)
    {
        $fieldQueryParts = array();
        foreach ($values as $k => $v) {
            $fieldQueryParts[] = '`' . $k . '`=?q';
        }
        $this->fieldsSql   = implode(', ', $fieldQueryParts);
        $this->fieldValues = array_values($values);
        return $this;
    }
    protected function getQueryString()
    {
        if (count($this->fieldValues) == 0) {
            throw new ExceptionCallOrder('Set values for update before execute');
        }
        return 'UPDATE `' . $this->from . '` SET ' . $this->fieldsSql . $this->getWhereSql();
    }
    protected function getQueryArguments()
    {
        return array_merge($this->fieldValues, parent::getQueryArguments());
    }
}
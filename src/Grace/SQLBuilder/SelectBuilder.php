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
    protected $additionalTables = array();


    public function setAdditionalTables(array $tables)
    {
        $this->additionalTables = $tables;
        return $this;
    }

    //TODO хак для запросов с юнионом (иначе они вернут набор резалтов, что неподходит)
    protected $isCountQuery = false;
    /**
     * Sets count syntax
     * @return $this
     */
    public function count()
    {
        $this->isCountQuery = true;
        //TODO id - magic field
        $this->fields = 'COUNT(id) AS counter';
        return $this;
    }
    /**
     * Sets fields statement
     * @param $sql
     * @return $this
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
     * @return $this
     */
    public function join($table, $fromTableField, $joinTableField)
    {
        $this->joinSql .=
            ' JOIN `' . $table . '` ON `' . $this->from . '`.`' . $fromTableField . '`=`' . $table . '`.`' .
                $joinTableField . '`';
        return $this;
    }
    /**
     * Joins SELECT statement as table
     * @param $table
     * @param $fromTableField
     * @param $joinTableField
     * @return $this
     */
    public function joinSelect($selectSQL, $alias, $fromTableField, $joinTableField, $operator = '=')
    {
        $this->joinSql .=
            " JOIN ({$selectSQL}) AS `{$alias}` ON `{$this->from}`.`{$fromTableField}`{$operator}`{$alias}`.`{$joinTableField}`";
        return $this;
    }
    /**
     * Sets group by statement
     * @param $sql
     * @return $this
     */
    public function group($sql)
    {
        $this->groupSql = ' GROUP BY ' . $sql;
        return $this;
    }
    /**
     * Sets having statement
     * @param $sql
     * @return $this
     */
    public function having($sql)
    {
        $this->havingSql = ' HAVING ' . $sql;
        return $this;
    }
    /**
     * Sets order by statement
     * @param $sql
     * @return $this
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
     * @return $this
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
        $aliasSql = ($this->alias != '' ? ' AS ?f' : '');

        if (count($this->additionalTables) > 0) {
            $tables = $this->additionalTables;
            array_unshift($tables, $this->from);

            $queries = array();
            foreach ($tables as $table) {
                $queries[] = 'SELECT ' . $this->fields . ' FROM ?f' . $aliasSql . $this->joinSql . $this->getWhereSql() .
                    $this->groupSql . $this->havingSql;
            }

            return ($this->isCountQuery ? 'SELECT SUM(counter) FROM ( ' : '')
                . '( ' . implode(' ) UNION ALL ( ', $queries) . ' )'
                . ($this->isCountQuery ? ' ) AS UnionTableAlias' : '')
                . $this->orderSql . $this->limitSql;
        } else {
            return 'SELECT ' . $this->fields . ' FROM ?f' . $aliasSql . $this->joinSql . $this->getWhereSql() .
                $this->groupSql . $this->havingSql . $this->orderSql . $this->limitSql;
        }
    }
    /**
     * @inheritdoc
     */
    protected function getQueryArguments()
    {
        $aliasPlaceholders = ($this->alias != '' ? array($this->alias) : array());

        $arguments = parent::getQueryArguments();

        if (count($this->additionalTables) > 0) {
            $tables = $this->additionalTables;
            array_unshift($tables, $this->from);

            $newArguments = array();
            foreach ($tables as $table) {
                $newArguments = array_merge($newArguments, array($table), $aliasPlaceholders, $arguments);
            }

            return $newArguments;
        } else {
            $arguments = array_merge(array($this->from), $aliasPlaceholders, $arguments);
            return $arguments;
        }
    }

}


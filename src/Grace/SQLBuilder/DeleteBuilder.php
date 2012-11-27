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
 * Delete sql builder
 */
class DeleteBuilder extends AbstractWhereBuilder
{
    /**
     * @inheritdoc
     */
    protected function getQueryString()
    {
        return "DELETE FROM {$this->sqlEscapeSymbol}{$this->from}{$this->sqlEscapeSymbol}" . $this->getWhereSql();
    }
}

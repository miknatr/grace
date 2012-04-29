<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\DBAL;

/**
 * Provides some base functions for concrete result classes
 */
abstract class AbstractResult implements InterfaceResult
{
    /**
     * @inheritdoc
     */
    public function fetchAll()
    {
        $r = array();
        while ($row = $this->fetchOne()) {
            $r[] = $row;
        }
        return $r;
    }
    /**
     * @inheritdoc
     */
    public function fetchColumn()
    {
        $r = array();
        while ($row = $this->fetchOne()) {
            $result = array_shift($row);
            $r[]    = $result;
        }
        return $r;
    }
    /**
     * @inheritdoc
     */
    public function fetchResult()
    {
        $row = $this->fetchOne();
        return array_shift($row);
    }
}
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
    public function fetchResult()
    {
        $row = $this->fetchOne();
        if (is_array($row)) {
            return array_shift($row);
        } else {
            return false;
        }
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
    public function fetchHash()
    {
        $r = array();
        while ($row = $this->fetchOne()) {
            if (count($row) >= 2) {
                $key     = array_shift($row);
                $value   = array_shift($row);
                $r[$key] = $value;
            }
        }
        return $r;
    }
}
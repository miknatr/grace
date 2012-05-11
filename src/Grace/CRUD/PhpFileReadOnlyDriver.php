<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\CRUD;

use Grace\DBAL\InterfaceConnection;

/**
 * Driver for php file which contains array with data
 */
class PhpFileReadOnlyDriver implements CRUDInterface
{
    private $data;

    /**
     * @param $filename
     */
    public function __construct($filename)
    {
        $this->data = include $filename;
    }
    /**
     * @inheritdoc
     */
    public function selectById($table, $id)
    {
        return $this->data[$table][$id];
    }
    /**
     * @inheritdoc
     */
    public function insertById($table, $id, array $values)
    {
        throw new \LogicException('Read only driver');
    }
    /**
     * @inheritdoc
     */
    public function updateById($table, $id, array $values)
    {
        throw new \LogicException('Read only driver');
    }
    /**
     * @inheritdoc
     */
    public function deleteById($table, $id)
    {
        throw new \LogicException('Read only driver');
    }
}


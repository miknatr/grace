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

//TODO Do and test all methods below
class MemcacheProxy implements CRUDInterface
{
    private $memcache;
    private $subject;
    public function __construct(array $config, CRUDInterface $subject)
    {
        //TODO DODODO
        $this->memcache = '';
        $this->subject  = $subject;
    }
    /**
     * @inheritdoc
     */
    public function selectById($table, $id)
    {
    }
    /**
     * @inheritdoc
     */
    public function insertById($table, $id, array $values)
    {
        $values['id'] = $id;
    }
    /**
     * @inheritdoc
     */
    public function updateById($table, $id, array $values)
    {
    }
    /**
     * @inheritdoc
     */
    public function deleteById($table, $id)
    {
    }
}


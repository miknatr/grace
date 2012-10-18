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

use Grace\Cache\CacheInterface;

class CacheProxy implements CRUDInterface
{
    /**
     * @var Cache
     */
    private $cache;
    private $subject;
    public function __construct(CRUDInterface $subject, CacheInterface $cache)
    {
        $this->subject  = $subject;
        $this->cache  = $cache;
    }
    /**
     * @inheritdoc
     */
    public function selectById($table, $id)
    {
        $r = $this->cache->get($table . $id);
        if (!$r) {
            $r = $this->subject->selectById($table, $id);
            $this->cache->set($table . $id, $r);
        }
        return $r;
    }
    /**
     * @inheritdoc
     */
    public function insertById($table, $id, array $values)
    {
        //лучше здесь не кэшировать, чтобы данные прошли через бд, т.к. там возможна некая фильтрация
        return $this->subject->insertById($table, $id, $values);
    }
    /**
     * @inheritdoc
     */
    public function updateById($table, $id, array $values)
    {
        //лучше здесь не кэшировать, чтобы данные прошли через бд, т.к. там возможна некая фильтрация
        $this->cache->remove($table . $id);
        return $this->subject->updateById($table, $id, $values);
    }
    /**
     * @inheritdoc
     */
    public function deleteById($table, $id)
    {
        $this->cache->remove($table . $id);
        return $this->subject->deleteById($table, $id);
    }
}


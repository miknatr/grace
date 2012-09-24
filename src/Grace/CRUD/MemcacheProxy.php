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
    private $ttl = 600;
    public function __construct(CRUDInterface $subject)
    {
        $this->subject  = $subject;
    }
    private function getMemcache()
    {
        if (empty($this->memcache)) {
            $this->memcache = new \Memcache;
            //TODO расхардкодить и вынести в параметры конструктора
            $this->memcache->connect('localhost', 11211);
        }
        return $this->memcache;
    }
    /**
     * @inheritdoc
     */
    public function selectById($table, $id)
    {
        $memcache = $this->getMemcache();
        $r = $memcache->get($table . $id);
        if (!$r) {
            $r = $this->subject->selectById($table, $id);
            $memcache->add($table . $id, $r, 0, $this->ttl);
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
        $this->getMemcache()->delete($table . $id);
        return $this->subject->updateById($table, $id, $values);
    }
    /**
     * @inheritdoc
     */
    public function deleteById($table, $id)
    {
        $this->getMemcache()->delete($table . $id);
        return $this->subject->deleteById($table, $id);
    }
}


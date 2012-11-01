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

use Grace\SQLBuilder\Factory;
use Grace\Cache\CacheInterface;

/**
 * Provides some base functions for concrete connection classes
 */
abstract class AbstractConnection implements InterfaceConnection
{

    /**
     * @var CacheInterface
     */
    private $cache;
    /**
     * @inheritdoc
     */
    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;
        return $this;
    }
    /**
     * @inheritdoc
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @var QueryLogger
     */
    private $logger;
    /**
     * @inheritdoc
     */
    public function setLogger(QueryLogger $logger)
    {
        $this->logger = $logger;
        return $this;
    }
    /**
     * @inheritdoc
     */
    public function getLogger()
    {
        if (!is_object($this->logger)) {
            $this->setLogger(new QueryLogger());
        }
        return $this->logger;
    }
    /**
     * @inheritdoc
     */
    public function getSQLBuilder()
    {
        return new Factory($this);
    }
    /**
     * @inheritdoc
     */
    public function replacePlaceholders($query, array $arguments)
    {
        $queryParts = explode('?', $query);
        $queryPartsLen = count($queryParts);
        $r = '';
        $i = 0;

        while ($i < $queryPartsLen) {
            $queryPartCurrent = $queryParts[$i];
            $r .= $queryPartCurrent;

            if (isset($queryParts[$i + 1])) {
                $queryPartNext = $queryParts[$i + 1];

                $type = $queryPartNext[0];
                $queryParts[$i + 1] = substr($queryParts[$i + 1], 1); //подрезаем первый символ (где указан символ типа)

                $r .= $this->escapeValueByType($arguments[$i], $type);
            }

            $i++;
        }

        return $r;
    }
    /**
     * Escapes value in compliance with type
     * @param mixed $value
     * @param char  $type
     * @return string
     */
    private function escapeValueByType($value, $type)
    {
        if (is_object($value)) {
            $value = (string) $value;
        }

        switch ($type) {
            case 'p':
                $r = $value;
                break;
            case 'e':
                $r = $this->escape($value);
                break;
            case 'q':
                $r = "'" . $this->escape($value) . "'";
                break;
            default:
                throw new ExceptionQuery('Placeholder has incorrect type: ' . $type);
        }
        return $r;
    }


    protected $idCounter = null;
    /**
     * Generate new id for insert
     * @return mixed
     */
    public function generateNewId($table)
    {
        //TODO будет логично для постгреса юзать последовательности
        if ($this->idCounter === null) {
            $this->idCounter = $this->getSQLBuilder()->select($table)->fields('id')->order('id DESC')->limit(0, 1)->fetchResult();
        }

        for ($i = 0; $i < 50; $i++) {
            $this->idCounter++;
            $key    = 'grace_id_gen_' . strval($this->idCounter);

            $isBusy = $this->getCache()->get($key);
            if ($isBusy === false) {
                $this->getCache()->set($key, '1', 60);
                return $this->idCounter;
            }
        }

        throw new \OutOfBoundsException('Maximum number of cycles to generate new id has reached');
    }
}
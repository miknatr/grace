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
        //firstly, we replace named placeholders like ?i:name: where "i" is escaping type and "name" is parameter name
        $onMatch = function($matches) use ($arguments) {
            if (!isset($arguments[$matches[2]])) {
                throw new ExceptionQuery("Placeholder named '$matches[2]' is not presented in \$arguments");
            }
            return $this->escapeValueByType($arguments[$matches[2]], $matches[1]);
        };
        $query = preg_replace_callback("(\?([a-z]{1}):([a-zA-Z0-9_]{0,100}):)", $onMatch, $query);

        //secondly, we replace ordered placeholders like ?i where "i" is escaping type
        $counter = -1;
        $onMatch = function($matches) use ($arguments, &$counter) {
            $counter++;
            if (!isset($arguments[$counter])) {
                throw new ExceptionQuery("Placeholder number '$counter' is not presented in \$arguments");
            }
            return $this->escapeValueByType($arguments[$counter], $matches[1]);
        };
        $query = preg_replace_callback("(\?([a-z]{1}))", $onMatch, $query);

        return $query;
    }
    /**
     * Escapes value in compliance with type
     *
     * Possible values of $type:
     * "p" - plain value, no escaping
     * "e" - escaping by "db-escape" function, but not quoting
     * "q" - escaping by "db-escape" function and quoting
     *
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


    protected $idCounterByTable = array();
    /**
     * Generate new id for insert
     * @return mixed
     */
    public function generateNewId($table)
    {
        //TODO будет логично для постгреса юзать последовательности
        if (!isset($this->idCounterByTable[$table])) {
            $this->idCounterByTable[$table] = $this->getSQLBuilder()->select($table)->fields('id')->order('id DESC')->limit(0, 1)->fetchResult();
        }

        for ($i = 0; $i < 50; $i++) {
            $this->idCounterByTable[$table]++;

            if ($this->getCache()) {
                $key    = 'grace_id_gen_' . $table . '_' . strval($this->idCounterByTable[$table]);

                $isBusy = $this->getCache()->get($key);
                if ($isBusy === false) {
                    $this->getCache()->set($key, '1', 60);
                    return $this->idCounterByTable[$table];
                }
            } else {
                return $this->idCounterByTable[$table];
            }
        }

        throw new \OutOfBoundsException('Maximum number of cycles to generate new id has reached');
    }
}
<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\Bundle\CommonBundle\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Grace\DBAL\QueryLogger;

class QueryCollector extends DataCollector
{
    private $logger;

    public function __construct(QueryLogger $logger)
    {
        $this->logger = $logger;
    }
    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = array(
            'connections' => $this->logger->getConnections(),
            'queries' => $this->logger->getQueries(),
        );
    }
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'grace';
    }

    public function getConnections()
    {
        return $this->data['connections'];
    }

    public function getQueries()
    {
        return $this->data['queries'];
    }

    public function isConnected()
    {
        return (count($this->data['queries']) > 0);
    }

    public function getQueryCount()
    {
        return count($this->data['queries']); //minus connection query and setting charset
        //return count($this->data['queries']) > 0 ? count($this->data['queries']) - 2 : 0; //minus connection query and setting charset
    }

    public function getTime()
    {
        $time = 0;
        foreach ($this->data['queries'] as $query) {
            $time += $query['time'];
        }

        return $time;
    }
}

<?php

namespace Grace\Bundle\ApiBundle\Finder;

interface PaginationFinderInterface
{
    /**
     * @abstract
     * @return array array('phone' => array('NotBlank'), ...)
     */
    public function getParamsConfig();
    /**
     * @return int
     */
    public function count(array $params = array());
    /**
     * @param $start
     * @param $number
     * @return CollectionAbstract
     */
    public function get($start, $number, array $params = array());
}

<?php

namespace Grace\Bundle\ApiBundle\Finder;

interface PagelessFinderInterface
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
     * @return ResourceAbstract[]
     */
    public function get(array $params = array());
}

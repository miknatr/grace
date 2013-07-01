<?php

namespace Grace\Tests\ORM\Plug;

use Grace\ORM\Service\Config\Config;
use Grace\ORM\Service\Config\Loader;

class GraceConfigHelper
{
    /** @return Config */
    public static function create()
    {
        $loader = new Loader(__DIR__ . '/../Resources/models');
        return $loader->getConfig();
    }
}

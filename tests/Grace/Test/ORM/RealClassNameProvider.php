<?php

namespace Grace\Test\ORM;

use Grace\ORM\Service\ClassNameProvider;

class RealClassNameProvider extends \Grace\ORM\Service\ClassNameProvider
{
    protected $modelNamespace = 'Grace\\Test\\ORM';
    protected $modelPrefix = '';
    protected $modelPostfix = '';
    protected $finderNamespace = 'Grace\\Test\\ORM';
    protected $finderPrefix = '';
    protected $finderPostfix = 'Finder';
    protected $mapperNamespace = 'Grace\\Test\\ORM';
    protected $mapperPrefix = '';
    protected $mapperPostfix = 'Mapper';
}

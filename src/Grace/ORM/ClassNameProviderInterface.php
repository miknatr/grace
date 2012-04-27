<?php

namespace Grace\ORM;

interface ClassNameProviderInterface
{
    public function getBaseClass($modelClass);
    public function getModelClass($baseClass);
    public function getFinderClass($baseClass);
    public function getMapperClass($baseClass);
    public function getCollectionClass($baseClass);
}
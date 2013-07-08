<?php

namespace Grace\Tests\ORM\Service\Config;

use Grace\ORM\Service\ClassNameProvider;
use Grace\ORM\Service\Config\Element\ModelElement;
use Grace\ORM\Service\Config\Element\PropertyElement;
use Grace\ORM\Service\Config\Element\ProxyElement;
use Grace\ORM\Service\Config\Loader;
use Grace\ORM\Service\TypeConverter;

class LoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testConfig()
    {
        $config = (new Loader(__DIR__ . '/../../Resources/modelsLoaderTest', new TypeConverter()))->getConfig();

        $modelNames = array_keys($config->models);
        sort($modelNames);
        $this->assertEquals(array('City', 'TaxiPassenger', 'TaxiStation'), $modelNames);

        $this->checkCity($config->models['City']);
        $this->checkTaxiStation($config->models['TaxiStation']);
        $this->checkTaxiPassenger($config->models['TaxiPassenger']);
    }

    protected function checkCity(ModelElement $model)
    {
        $this->checkLocalProperty($model->properties['id'], 'int');
        $this->checkLocalProperty($model->properties['name'], 'string');
    }

    protected function checkTaxiStation(ModelElement $model)
    {
        $this->checkLocalProperty($model->properties['id'], 'int');
        $this->checkLocalProperty($model->properties['name'], 'string');
        $this->checkLocalProperty($model->properties['phone'], 'string');
        $this->checkForeignKeyProperty($model->properties['cityId'], 'int', 'City', array('cityName>cityId>City>name'));
        $this->checkProxyProperty($model->properties['cityName'], 'string', 'cityName>cityId>City>name');
    }

    protected function checkTaxiPassenger(ModelElement $model)
    {
        $this->checkLocalProperty($model->properties['id'], 'int');
        $this->checkLocalProperty($model->properties['name'], 'string');
        $this->checkLocalProperty($model->properties['phone'], 'string');
        $this->checkForeignKeyProperty($model->properties['cityId'], 'int', 'City', array('cityName>cityId>City>name'));
        $this->checkProxyProperty($model->properties['cityName'], 'string', 'cityName>cityId>City>name');
        $this->checkForeignKeyProperty($model->properties['taxiStationId'], 'int', 'TaxiStation', array('taxiStationName>taxiStationId>TaxiStation>name', 'taxiStationPhone>taxiStationId>TaxiStation>phone'));
        $this->checkProxyProperty($model->properties['taxiStationName'], 'string', 'taxiStationName>taxiStationId>TaxiStation>name');
        $this->checkProxyProperty($model->properties['taxiStationPhone'], 'string', 'taxiStationPhone>taxiStationId>TaxiStation>phone');
    }


    //
    // HELPERS
    //

    protected function checkLocalProperty(PropertyElement $prop, $typeAlias)
    {
        $this->assertEquals(true, $prop->isLocalInDb);
        $this->assertEquals(true, $prop->isSettable);
        $this->assertEquals(false, $prop->isNullable);
        $this->assertEquals($typeAlias, $prop->type);
        $this->assertEquals(null, $prop->resolvesToModelName);
        $this->assertEquals(array(), $prop->dependentProxies);
        $this->assertEquals(null, $prop->proxy);
    }

    protected function checkForeignKeyProperty(PropertyElement $prop, $typeAlias, $resolvesToModelName, array $dependentProxies)
    {
        $dependentProxies = $this->resolveProxies($dependentProxies);

        $this->assertEquals(true, $prop->isLocalInDb);
        $this->assertEquals(true, $prop->isSettable);
        $this->assertEquals(true, $prop->isNullable);
        $this->assertEquals($typeAlias, $prop->type);
        $this->assertEquals($resolvesToModelName, $prop->resolvesToModelName);
        $this->assertEquals($dependentProxies, $prop->dependentProxies);
        $this->assertEquals(null, $prop->proxy);
    }

    protected function checkProxyProperty(PropertyElement $prop, $typeAlias, $proxy)
    {
        $proxy = $this->resolveProxy($proxy)[1];

        $this->assertEquals(false, $prop->isLocalInDb);
        $this->assertEquals(false, $prop->isSettable);
        $this->assertEquals(true, $prop->isNullable);
        $this->assertEquals($typeAlias, $prop->type);
        $this->assertEquals(null, $prop->resolvesToModelName);
        $this->assertEquals(array(), $prop->dependentProxies);
        $this->assertEquals($proxy, $prop->proxy);
    }

    protected function resolveProxies(array $proxiesString)
    {
        $proxies = array();

        foreach ($proxiesString as $proxyString) {
            list($localProxyProperty, $proxy) = $this->resolveProxy($proxyString);
            $proxies[$localProxyProperty] = $proxy;
        }

        return $proxies;
    }

    protected function resolveProxy($proxyString)
    {
        list($localProxyProperty, $localForeignKeyProperty, $foreignTable, $foreignField) = explode('>', $proxyString);

        $proxy = new ProxyElement();
        //STOPPER переименовать field в property, table в model
        $proxy->foreignField = $foreignField;
        $proxy->foreignTable = $foreignTable;
        $proxy->localField   = $localForeignKeyProperty;

        return array($localProxyProperty, $proxy);
    }
}

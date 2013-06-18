<?php

namespace Grace\Tests\ORM;

use Grace\Cache\CacheInterface;
use Grace\ORM\Grace;
use Grace\ORM\Service\ClassNameProvider;
use Grace\ORM\Service\ModelObserver;
use Grace\DBAL\Mysqli\Connection;
use Grace\ORM\Service\TypeConverter;
use Grace\Tests\ORM\Plug\Finder\TaxiPassengerFinder;
use Grace\Tests\ORM\Plug\TaxiModelsConfig;

class GraceTest extends \PHPUnit_Framework_TestCase
{
    /** @var Grace */
    protected $orm;
    /** @var TaxiPassengerFinder */
    protected $finder;
    /** @var Connection */
    protected $connection;

    protected function setUp()
    {
        /** @var $cache CacheInterface */
        $cache = $this->getMock('\\Grace\\Cache\\CacheInterface');

        $this->connection = new Connection(TEST_MYSQLI_HOST, TEST_MYSQLI_PORT, TEST_MYSQLI_NAME, TEST_MYSQLI_PASSWORD, TEST_MYSQLI_DATABASE);

        $this->orm = new Grace(
            $this->connection,
            new ClassNameProvider('Grace\\Tests\\ORM\\Plug'),
            new ModelObserver(),
            new TypeConverter(),
            new TaxiModelsConfig(),
            $cache
        );

        $this->finder = $this->orm->getFinder('TaxiPassenger');

        $this->connection->execute('DROP TABLE IF EXISTS "TaxiPassenger"');
        $this->connection->execute('CREATE TABLE "TaxiPassenger" ("id" INT(10) PRIMARY KEY AUTO_INCREMENT, "name" VARCHAR(255), "phone" VARCHAR(255))');
        $this->connection->execute('INSERT INTO "TaxiPassenger" VALUES (1, \'Mike Smit\', \'1234567\')');
        $this->connection->execute('INSERT INTO "TaxiPassenger" VALUES (2, \'Jack Smit\', \'1234567\')');
        $this->connection->execute('INSERT INTO "TaxiPassenger" VALUES (3, \'Arnold Schwarzenegger\', \'1234567\')');
    }
    public function testFinder()
    {
        $finder = $this->orm->getFinder('TaxiPassenger');
        $this->assertTrue($finder instanceof TaxiPassengerFinder);

        $finder2 = $this->orm->getFinder('TaxiPassenger');
        $this->assertEquals(spl_object_hash($finder), spl_object_hash($finder2));
    }
    public function testCommit()
    {
        //test insert
        $this->finder->create()->setName('Sylvester Stallone')->setPhone('+1-123-123');

        //test delete
        $this->finder->getByIdOrFalse(3)->delete();

        //test update
        $this->finder->getByIdOrFalse(2)->setName('Mr. Jack Smit');

        $this->orm->commit();


        //clean objects and see changes
        $this->assertEquals('Mike Smit', $this->finder->getByIdOrFalse(1)->getName());
        $this->assertEquals('Mr. Jack Smit', $this->finder->getByIdOrFalse(2)->getName());
        $this->assertEquals('Sylvester Stallone', $this->finder->getByIdOrFalse(4)->getName());

        $this->assertFalse($this->finder->getByIdOrFalse(3));
    }
}

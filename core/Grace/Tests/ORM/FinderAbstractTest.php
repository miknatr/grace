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
use Grace\Tests\ORM\Plug\Model\TaxiPassenger;

class FinderAbstractTest extends \PHPUnit_Framework_TestCase
{
    /** @var Grace */
    protected $orm;
    /** @var TaxiPassengerFinder */
    protected $finder;

    protected function setUp()
    {
        $db  = new Connection(TEST_MYSQLI_HOST, TEST_MYSQLI_PORT, TEST_MYSQLI_NAME, TEST_MYSQLI_PASSWORD, TEST_MYSQLI_DATABASE);

        /** @var $cache CacheInterface */
        $cache = $this->getMock('\\Grace\\Cache\\CacheInterface');

        $this->orm = new Grace(
            $db,
            new ClassNameProvider('Grace\\Tests\\ORM\\Plug'),
            new ModelObserver(),
            new TypeConverter(),
            new TaxiModelsConfig(),
            $cache
        );

        $this->finder = new TaxiPassengerFinder('TaxiPassenger', $this->orm);

        $db->execute('DROP TABLE IF EXISTS "TaxiPassenger"');
        $db->execute('CREATE TABLE "TaxiPassenger" ("id" INT(10) PRIMARY KEY AUTO_INCREMENT, "name" VARCHAR(255), "phone" VARCHAR(255))');
        $db->execute('INSERT INTO "TaxiPassenger" VALUES (1, \'Mike Smit\', \'1234567\')');
        $db->execute('INSERT INTO "TaxiPassenger" VALUES (2, \'John Smit\', \'1234567\')');
        $db->execute('INSERT INTO "TaxiPassenger" VALUES (3, \'Bill Murray\', \'1234567\')');
    }
    public function testCreate()
    {
        /** @var $r TaxiPassenger */
        $r = $this->finder->create();
        $this->assertTrue($r instanceof TaxiPassenger);
        $this->assertEquals(4, $r->getId());

        /** @var $r TaxiPassenger */
        $r = $this->finder->create();
        $this->assertTrue($r instanceof TaxiPassenger);
        $this->assertEquals(5, $r->getId());
    }
    public function testGetById()
    {
        /** @var $r TaxiPassenger */
        $r = $this->finder->getByIdOrFalse(3);
        $this->assertTrue($r instanceof TaxiPassenger);
        $this->assertEquals(3, $r->getId());
        $this->assertEquals('Bill Murray', $r->getName());

        //Testing Identity Map work
        /** @var $r TaxiPassenger */
        $r2 = $this->finder->getByIdOrFalse(3);
        $this->assertEquals(spl_object_hash($r), spl_object_hash($r2));
    }
    public function testGetByIdWithBadId()
    {
        $this->assertFalse($this->finder->getByIdOrFalse(123123));
    }
    public function testFetchAll()
    {
        /** @var $r TaxiPassenger[] */
        $r = $this->finder->getSelectBuilder()->likeInPart('name', 'smit')->fetchAll();
        $this->assertEquals(2, count($r));
        $this->assertEquals('Mike Smit', $r[0]->getName());
    }
}

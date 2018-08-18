<?php

// use Exception;
use FcPhp\Repository\Repository;
use PHPUnit\Framework\TestCase;
use FcPhp\Datasource\Datasource;
use FcPhp\Query\Interfaces\IQuery;
use FcPhp\Cache\Facades\CacheFacade;
use FcPhp\Repository\Interfaces\IRepository;
use FcPhp\Di\Facades\DiFacade;
use FcPhp\Query\Query;
use FcPhp\Repository\Factories\Factory;

class RepositoryIntegrationTest extends TestCase
{
    public function setUp()
    {
        $this->di = DiFacade::getInstance();
        $this->factory = new Factory($this->di);
        $this->datasource = new DatasourceTest();
        $this->cache = CacheFacade::getInstance('tests/var/cache');
        
        $this->callbackConnectError = function(IQuery $query, Exception $e) {
            $this->assertInstanceOf(IQuery::class, $query);
            $this->assertInstanceOf(Exception::class, $e);
        };

        $this->callbackQueryError = function(IQuery $query, Exception $e) {
            $this->assertInstanceOf(IQuery::class, $query);
            $this->assertInstanceOf(Exception::class, $e);
        };

        $this->instance = new Repository($this->datasource, $this->cache, $this->factory, $this->callbackConnectError, $this->callbackQueryError);
    }

    public function testInstance()
    {
        $this->assertInstanceOf(IRepository::class, $this->instance);
    }

    public function testSingleQueryCache()
    {
        $query = $this->instance->getQuery();
        $data = $this->instance->execute($query);
        $expected = [];
        $expected[] = ['id' => 123, 'name' => 'Test'];
        $this->assertEquals($expected, $data);
    }

    public function testSingleQueryNonCache()
    {
        $query = $this->instance->getQuery();
        $datasource = new DatasourceTestRand();
        $instance = new Repository($datasource, $this->cache, $this->factory, $this->callbackConnectError, $this->callbackQueryError);
        $data = $instance->execute($query);
        d($data, true);
        $this->assertTrue(is_array($data) && count($data) == 1);
    }
}

class DatasourceTest extends Datasource
{
    public function execute(IQuery $query) :array
    {
        return [
            'id' => 123,
            'name' => 'Test'
        ];
    }
}

class DatasourceTestRand extends Datasource
{
    public function execute(IQuery $query) :array
    {
        d('okok', true);
        return [
            'id' => time(),
            'name' => microtime()
        ];
    }
}

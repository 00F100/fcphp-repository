<?php

// use Exception;
use FcPhp\Datasource\Strategy;
use FcPhp\Repository\Repository;
use PHPUnit\Framework\TestCase;
use FcPhp\Datasource\Datasource;
// use FcPhp\Datasource\MySQL\MySQL;
use FcPhp\Datasource\Interfaces\IQuery;
use FcPhp\Cache\Facades\CacheFacade;
use FcPhp\Repository\Interfaces\IRepository;
use FcPhp\Di\Facades\DiFacade;
// use FcPhp\Datasource\Query;
use FcPhp\Datasource\Factories\Factory;

class RepositoryIntegrationTest extends TestCase
{
    public function setUp()
    {
        $this->stratagies = [
            'mysql' => '\MySQLStrategy',
        ];
        $this->di = DiFacade::getInstance();
        $this->factory = new Factory('mysql', $this->stratagies, $this->di);
        $this->datasource = new MySQLMockTest();
        $this->cache = CacheFacade::getInstance('tests/var/cache');
        
        $this->callbackError = function(IQuery $query, Exception $e) {
            $this->assertInstanceOf(IQuery::class, $query);
            $this->assertInstanceOf(Exception::class, $e);
            $this->assertEquals('Error Processing Request', $e->getMessage());
        };

        $this->instance = new LocalRepository($this->datasource, $this->cache, $this->factory, $this->callbackError);
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

    public function testSingleQueryCacheSaved()
    {
        $query = $this->instance->getQuery();
        $data = $this->instance->execute($query);
        $expected = [];
        $expected[] = ['id' => 123, 'name' => 'Test'];
        $this->assertEquals($expected, $data);
    }

    public function testMultieQueryCache()
    {
        $query = $this->instance->getQuery();
        $query->promp('column');
        $query2 = $this->instance->getQuery();
        $query2->promp('field');
        $data = $this->instance->execute([$query, $query2]);
        $expected = [];
        $expected[] = ['id' => 123, 'name' => 'Test'];
        $this->assertEquals([$expected, $expected], $data);
    }

    public function testMultieQueryCacheSaved()
    {
        $query = $this->instance->getQuery();
        $query->promp('column');
        $query2 = $this->instance->getQuery();
        $query2->promp('field');
        $data = $this->instance->execute([$query, $query2]);
        $expected = [];
        $expected[] = ['id' => 123, 'name' => 'Test'];
        $this->assertEquals([$expected, $expected], $data);
    }

    public function testSingleQueryNoCache()
    {
        $instance = new LocalRepository($this->datasource, null, $this->factory, $this->callbackError);
        $query = $instance->getQuery();
        $query->promp('test');
        $data = $instance->execute($query);
        $expected = [];
        $expected[] = ['id' => 123, 'name' => 'Test'];
        $this->assertEquals($expected, $data);
    }

    public function testMultiQueryNoCache()
    {
        $instance = new LocalRepository($this->datasource, null, $this->factory, $this->callbackError);
        $query = $instance->getQuery();
        $query->promp('column');
        $query2 = $instance->getQuery();
        $query2->promp('field');
        $data = $instance->execute([$query, $query2]);
        $expected = [];
        $expected[] = ['id' => 123, 'name' => 'Test'];
        $this->assertEquals([$expected, $expected], $data);
    }

    /**
     * @expectedException Exception
     */
    public function testQueryException()
    {
        $datasource = new MySQLMockTestException();
        $instance = new LocalRepository($datasource, null, $this->factory, $this->callbackError);
        $query = $instance->getQuery();
        $query->promp('test-exception');
        $data = $instance->execute($query);
    }

    /**
     * @expectedException Exception
     */
    public function testMultiQueryException()
    {
        $datasource = new MySQLMockTestException();
        $instance = new LocalRepository($datasource, null, $this->factory, $this->callbackError);
        $query = $instance->getQuery();
        $query->promp('test-exception2');
        $query2 = $instance->getQuery();
        $query2->promp('test-exception2');
        $data = $instance->execute([$query, $query2]);
    }
}

class LocalRepository extends Repository
{
    
}

class MySQLMockTest extends Datasource
{
    public function execute(IQuery $query) :array
    {
        return [
            'id' => 123,
            'name' => 'Test'
        ];
    }
}

class MySQLMockTestException extends Datasource
{
    public function execute(IQuery $query) :array
    {
        throw new Exception("Error Processing Request", 1);
    }
}

class MySQLStrategy extends Strategy
{
    private $promp;

    public function promp($promp)
    {
        $this->promp = $promp;
    }

    public function getPromp()
    {
        return $this->promp;
    }
}

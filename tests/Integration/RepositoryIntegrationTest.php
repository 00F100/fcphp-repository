<?php

// use Exception;
use FcPhp\Repository\Repository;
use PHPUnit\Framework\TestCase;
use FcPhp\Datasource\Datasource;
use FcPhp\Datasource\Interfaces\IQuery;
use FcPhp\Cache\Facades\CacheFacade;
use FcPhp\Repository\Interfaces\IRepository;
use FcPhp\Di\Facades\DiFacade;
use FcPhp\Datasource\Query;
use FcPhp\Datasource\Factories\Factory;

class RepositoryIntegrationTest extends TestCase
{
    public function setUp()
    {
        $this->di = DiFacade::getInstance();
        $this->factory = new Factory($this->di);
        $this->datasource = new DatasourceTest();
        $this->cache = CacheFacade::getInstance('tests/var/cache');
        
        $this->callbackError = function(IQuery $query, Exception $e) {
            $this->assertInstanceOf(IQuery::class, $query);
            $this->assertInstanceOf(Exception::class, $e);
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

    public function testSingleQueryCreateCache()
    {
        $query = $this->instance->getQuery();
        $query->select('more_some_field');
        $data = $this->instance->execute($query);
        $expected = [];
        $expected[] = ['id' => 123, 'name' => 'Test'];
        $this->assertEquals($expected, $data);
    }

    public function testSingleQueryNonCache()
    {
        $query = $this->instance->getQuery();
        $query->select('*')->from('table');
        $datasource = new DatasourceTestRand();
        $instance = new LocalRepository($datasource, null, $this->factory, $this->callbackError);
        $data = $instance->execute($query);
        $this->assertTrue(is_array($data) && count($data) == 1);
    }

    public function testMultiQueryCreateCache()
    {
        $query = $this->instance->getQuery();
        $query->select('field')->from('table');
        $secondQuery = $this->instance->getQuery();
        $secondQuery->select('some_field');
        $data = $this->instance->execute(['first' => $query, 'second' => $secondQuery]);
        $expected = ['first' => [['id' => 123, 'name' => 'Test']], 'second' => [['id' => 123, 'name' => 'Test']]];
        $this->assertEquals($expected, $data);
    }

    public function testMultiQueryCache()
    {
        $query = $this->instance->getQuery();
        $query->select('more_field')->from('one_table');
        $secondQuery = $this->instance->getQuery();
        $secondQuery->select('more_information')->from('some');
        $data = $this->instance->execute(['one' => $query, 'two' => $secondQuery]);
        $expected = ['one' => [['id' => 123, 'name' => 'Test']], 'two' => [['id' => 123, 'name' => 'Test']]];
        $this->assertEquals($expected, $data);
    }

    public function testMultiQueryNonCache()
    {
        $query = $this->instance->getQuery();
        $query->select('first_column')->from('table');
        $datasource = new DatasourceTestRand();
        $instance = new LocalRepository($datasource, null, $this->factory, $this->callbackError);
        $data = $instance->execute([$query]);
        $this->assertTrue(is_array($data) && count($data) == 1);
    }

    /**
     * @expectedException Exception
     */
    public function testQueryException()
    {
        $query = $this->instance->getQuery();
        $query->select('first_column')->from('table');
        $datasource = new DatasourceException();
        $instance = new LocalRepository($datasource, null, $this->factory, $this->callbackError);
        $data = $instance->execute([$query]);
    }
}

class LocalRepository extends Repository
{
    
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
        return [
            'id' => time(),
            'name' => microtime()
        ];
    }
}


class DatasourceException extends Datasource
{
    public function execute(IQuery $query) :array
    {
        throw new Exception();
        
    }
}

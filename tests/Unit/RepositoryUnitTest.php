<?php

// use Exception;
use PHPUnit\Framework\TestCase;
use FcPhp\Repository\Repository;
use FcPhp\Repository\Interfaces\IRepository;
use FcPhp\Datasource\Interfaces\IQuery;

class RepositoryUnitTest extends TestCase
{
    public function testCacheHas()
    {
        $datasource = $this->createMock('FcPhp\Datasource\Interfaces\IDatasource');
        $cache = $this->createMock('FcPhp\Cache\Interfaces\ICache');
        $cache
            ->expects($this->any())
            ->method('has')
            ->will($this->returnValue(true));
        $cache
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue(['content' => 'value']));
        $factory = $this->createMock('FcPhp\Datasource\Interfaces\IFactory');
        $query = $this->createMock('FcPhp\Datasource\Interfaces\IQuery');
        $callbackError = function(IQuery $query, Exception $e) {
            $this->assertInstanceOf(IQuery::class, $query);
            $this->assertInstanceOf(Exception::class, $e);
            $this->assertEquals('Error Processing Request', $e->getMessage());
        };
        $instance = new LocalRepositoryUnit($datasource, $cache, $factory, $callbackError);
        $data = $instance->execute($query);
        $this->assertEquals([['content' => 'value']], $data);
    }

    public function testNotCache()
    {
        $datasource = $this->createMock('FcPhp\Datasource\Interfaces\IDatasource');
        $datasource
            ->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(['content2' => 'value2']));
        $factory = $this->createMock('FcPhp\Datasource\Interfaces\IFactory');
        $query = $this->createMock('FcPhp\Datasource\Interfaces\IQuery');
        $callbackError = function(IQuery $query, Exception $e) {
            $this->assertInstanceOf(IQuery::class, $query);
            $this->assertInstanceOf(Exception::class, $e);
            $this->assertEquals('Error Processing Request', $e->getMessage());
        };
        $instance = new LocalRepositoryUnit($datasource, null, $factory, $callbackError);
        $data = $instance->execute($query);
        $this->assertEquals([['content2' => 'value2']], $data);
    }

    public function testCacheNoHas()
    {
        $datasource = $this->createMock('FcPhp\Datasource\Interfaces\IDatasource');
        $datasource
            ->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(['content3' => 'value3']));
        $cache = $this->createMock('FcPhp\Cache\Interfaces\ICache');
        $cache
            ->expects($this->any())
            ->method('has')
            ->will($this->returnValue(false));
        $factory = $this->createMock('FcPhp\Datasource\Interfaces\IFactory');
        $query = $this->createMock('FcPhp\Datasource\Interfaces\IQuery');
        $callbackError = function(IQuery $query, Exception $e) {
            $this->assertInstanceOf(IQuery::class, $query);
            $this->assertInstanceOf(Exception::class, $e);
            $this->assertEquals('Error Processing Request', $e->getMessage());
        };
        $instance = new LocalRepositoryUnit($datasource, $cache, $factory, $callbackError);
        $data = $instance->execute($query);
        $this->assertEquals([['content3' => 'value3']], $data);
    }

    public function testMultiQueryHasCache()
    {
        $datasource = $this->createMock('FcPhp\Datasource\Interfaces\IDatasource');
        $cache = $this->createMock('FcPhp\Cache\Interfaces\ICache');
        $cache
            ->expects($this->any())
            ->method('has')
            ->will($this->returnValue(true));
        $cache
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue(['content5' => 'value5']));
        $factory = $this->createMock('FcPhp\Datasource\Interfaces\IFactory');
        $query = $this->createMock('FcPhp\Datasource\Interfaces\IQuery');
        $query2 = $this->createMock('FcPhp\Datasource\Interfaces\IQuery');
        $query2->setContent('value5');
        $callbackError = function(IQuery $query, Exception $e) {
            $this->assertInstanceOf(IQuery::class, $query);
            $this->assertInstanceOf(Exception::class, $e);
            $this->assertEquals('Error Processing Request', $e->getMessage());
        };
        $instance = new LocalRepositoryUnit($datasource, $cache, $factory, $callbackError);
        $data = $instance->execute([$query, $query2]);
        $this->assertEquals([[['content5' => 'value5']], [['content5' => 'value5']]], $data);
    }

    public function testMultiQueryNoCache()
    {
        $datasource = $this->createMock('FcPhp\Datasource\Interfaces\IDatasource');
        $datasource
            ->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(['content6' => 'value6']));
        $factory = $this->createMock('FcPhp\Datasource\Interfaces\IFactory');
        $query = $this->createMock('FcPhp\Datasource\Interfaces\IQuery');
        $query2 = $this->createMock('FcPhp\Datasource\Interfaces\IQuery');
        $query2->setContent('value6');
        $callbackError = function(IQuery $query, Exception $e) {
            $this->assertInstanceOf(IQuery::class, $query);
            $this->assertInstanceOf(Exception::class, $e);
            $this->assertEquals('Error Processing Request', $e->getMessage());
        };
        $instance = new LocalRepositoryUnit($datasource, null, $factory, $callbackError);
        $data = $instance->execute([$query, $query2]);
        $this->assertEquals([[['content6' => 'value6']], [['content6' => 'value6']]], $data);
    }

    public function testMultiQueryHasNoCache()
    {
        $datasource = $this->createMock('FcPhp\Datasource\Interfaces\IDatasource');
        $datasource
            ->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(['content7' => 'value7']));
        $cache = $this->createMock('FcPhp\Cache\Interfaces\ICache');
        $cache
            ->expects($this->any())
            ->method('has')
            ->will($this->returnValue(false));
        $factory = $this->createMock('FcPhp\Datasource\Interfaces\IFactory');
        $query = $this->createMock('FcPhp\Datasource\Interfaces\IQuery');
        $query2 = $this->createMock('FcPhp\Datasource\Interfaces\IQuery');
        $query2->setContent('value7');
        $callbackError = function(IQuery $query, Exception $e) {
            $this->assertInstanceOf(IQuery::class, $query);
            $this->assertInstanceOf(Exception::class, $e);
            $this->assertEquals('Error Processing Request', $e->getMessage());
        };
        $instance = new LocalRepositoryUnit($datasource, $cache, $factory, $callbackError);
        $data = $instance->execute([$query, $query2]);
        $this->assertEquals([[['content7' => 'value7']], [['content7' => 'value7']]], $data);
    }

    /**
     * @expectedException \Exception 
     */
    public function testException()
    {
        $datasource = $this->createMock('FcPhp\Datasource\Interfaces\IDatasource');
        $cache = $this->createMock('FcPhp\Cache\Interfaces\ICache');
        
        $exception = $this->createMock('Exception');
        
        $cache
            ->expects($this->any())
            ->method('has')
            ->will($this->returnCallback(function() {
                throw new Exception("Error Processing Request", 1);
            }));
        $factory = $this->createMock('FcPhp\Datasource\Interfaces\IFactory');
        $callbackError = function(IQuery $query, Exception $e) {
            $this->assertInstanceOf(IQuery::class, $query);
            $this->assertInstanceOf(Exception::class, $e);
            $this->assertEquals('Error Processing Request', $e->getMessage());
        };
        $instance = new LocalRepositoryUnit($datasource, $cache, $factory, $callbackError);
        $query = $instance->getQuery();
        $data = $instance->execute([$query]);
    }

}

class LocalRepositoryUnit extends Repository
{

}

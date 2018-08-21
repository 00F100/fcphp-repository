<?php

namespace FcPhp\Repository
{
    use Exception;
    use FcPhp\Cache\Interfaces\ICache;
    use FcPhp\Datasource\Interfaces\IQuery;
    use FcPhp\Datasource\Interfaces\IFactory;
    use FcPhp\Datasource\Interfaces\IDatasource;
    use FcPhp\Repository\Interfaces\IRepository;

    abstract class Repository implements IRepository
    {
        const TTL_REPOSITORY = 7200;

        /**
         * @var FcPhp\Datasource\Interfaces\IDatasource
         */
        private $datasource;

        /**
         * @var FcPhp\Cache\Interfaces\ICache
         */
        private $cache;

        /**
         * @var FcPhp\Datasource\Interfaces\IFactory
         */
        private $factory;

        /**
         * @var object
         */
        private $callbackError;

        /**
         * Method to construct instance
         *
         * @param FcPhp\Datasource\Interfaces\IDatasource $datasource Instance of Datasource
         * @param null|FcPhp\Cache\Interfaces\ICache $cache Instance of Cache
         * @param FcPhp\Datasource\Interfaces\IFactory $factory Instance of Factory to create Query
         * @param object $callbackError Function to send error callback
         * @return void;
         */
        public function __construct(IDatasource $datasource, ICache $cache = null, IFactory $factory, object $callbackError = null)
        {
            $this->datasource = $datasource;
            $this->cache = $cache;
            $this->factory = $factory;
            $this->callbackError = $callbackError;
        }

        /**
         * Method to execute query(ies)
         *
         * @param array|FcPhp\Datasource\Interfaces\IQuery $query Query(ies) to execute
         * @return array
         */
        public function execute($query) :array
        {
            $data = [];
            try {
                if(is_array($query)) {
                    foreach($query as $index => $itemQuery) {
                        try {
                            $queryData = [];
                            if($this->cache instanceof ICache) {
                                $key = md5(serialize($itemQuery));
                                if($this->cache->has($key)) {
                                    $queryData[] = $this->cache->get($key);
                                }else{
                                    $content = $this->datasource->execute($itemQuery);
                                    $queryData[] = $content;
                                    $this->cache->set($key, $content, self::TTL_REPOSITORY);
                                }
                            }else{
                                $queryData[] = $this->datasource->execute($itemQuery);
                            }
                            $data[$index] = $queryData;
                        } catch (Exception $e) {
                            $query = $itemQuery;
                            throw $e;
                        }
                    }
                }else{
                    if($this->cache instanceof ICache) {
                        $key = md5(serialize($query));
                        if($this->cache->has($key)) {
                            $data[] = $this->cache->get($key);
                        }else{
                            $content = $this->datasource->execute($query);
                            $data[] = $content;
                            $this->cache->set($key, $content, self::TTL_REPOSITORY);
                        }
                    }else{
                        $data[] = $this->datasource->execute($query);
                    }
                }
            } catch (Exception $e) {
                $this->callbackError($query, $e);
                throw $e;
            } finally {
                $this->datasource->disconnect();
            }
            return $data;
        }

        /**
         * Method to return instance of Query
         *
         * @return FcPhp\Datasource\Interfaces\IQuery
         */
        public function getQuery() :IQuery
        {
            return $this->factory->getQuery($this->datasource->getStrategy());
        }

        /**
         * Method to execute error callback
         *
         * @param FcPhp\Datasource\Interfaces\IQuery $query Instance of Query
         * @param Exception $e Exception
         * @return FcPhp\Datasource\Interfaces\IQuery
         */
        private function callbackError(IQuery $query, Exception $e)
        {
            if(is_object($this->callbackError)) {
                $callbackError = $this->callbackError;
                $callbackError($query, $e);
            }
        }
    }
}

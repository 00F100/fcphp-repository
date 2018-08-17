<?php

namespace FcPhp\Repository
{
    use Exception;
    use FcPhp\Repository\Interfaces\IRepository;
    use FcPhp\Repository\Exceptions\ConnectErrorException;
    use FcPhp\Repository\Exceptions\QueryErrorException;
    use FcPhp\Cache\Interfaces\ICache;

    class Repository implements IRepository
    {
        const TTL_REPOSITORY = 7200;
        private $query;
        private $datasource;
        private $cache;
        private $callbackConnectError;
        private $callbackQueryError;

        public function __construct($datasource, ICache $cache = null, object $callbackConnectError = null, object $callbackQueryError = null)
        {
            $this->datasource = $datasource;
            $this->cache = $cache;
            $this->callbackConnectError = $callbackConnectError;
            $this->callbackQueryError = $callbackQueryError;
        }cache

        public function execute($query) :array
        {
            try {
                $hasException = false;
                $data = [];
                $this->datasource->connect();

            } catch (Exception $e) {
                $this->callbackConnectError($query, $e);
                throw new ConnectErrorException();
            }
            try {
                if(is_array($query)) {
                    foreach($query as $itemQuery) {
                        if($this->cache instanceof ICache) {
                            $key = md5(serialize($itemQuery));
                            if($this->cache->has($key)) {
                                $data[] = $this->cache->get($key);
                            }else{
                                $content = $this->datasource->execute($itemQuery);
                                $data[] = $content;
                                $this->cache->set($key, $content, self::TTL_REPOSITORY);
                            }
                        }else{
                            $data[] = $this->datasource->execute($itemQuery);
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
                $hasException = true;
                $this->callbackQueryError($e, $query);
                throw new QueryErrorException();
            } finally {
                $this->datasource->disconnect();
            }
            return $data;
        }

        private function callbackQueryError($query, Exception $e)
        {
            if(is_object($this->callbackQueryError)) {
                $callbackQueryError = $this->callbackQueryError;
                $callbackQueryError($query, $e);
            }
        }

        private function callbackConnectError($query, Exception $e)
        {
            if(is_object($this->callbackConnectError)) {
                $callbackConnectError = $this->callbackConnectError;
                $callbackConnectError($query, $e);
            }
        }
    }
}

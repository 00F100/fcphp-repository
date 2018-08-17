<?php

namespace FcPhp\Repository
{
    use Exception;
    use FcPhp\Repository\Interfaces\IRepository;
    use FcPhp\Repository\Exceptions\ConnectErrorException;
    use FcPhp\Repository\Exceptions\QueryErrorException;

    class Repository implements IRepository
    {
        private $query;
        private $datasource;
        private $callbackConnectError;
        private $callbackQueryError;

        public function __construct($datasource, object $callbackConnectError = null, object $callbackQueryError = null)
        {
            $this->datasource = $datasource;
            $this->callbackConnectError = $callbackConnectError;
            $this->callbackQueryError = $callbackQueryError;
        }

        public function execute($query) :array
        {
            try {
                $hasException = false;
                $data = [];
                $this->datasource->connect();
                try {
                    if(is_array($query)) {
                        foreach($query as $itemQuery) {
                            $data[] = $this->datasource->execute($itemQuery);
                        }
                    }else{
                        $data[] = $this->datasource->execute($query);
                    }
                } catch (Exception $e) {
                    $hasException = true;
                    $this->callbackQueryError($e, $query);
                    throw new QueryErrorException();
                } finally {
                    $this->datasource->disconnect();
                }
            } catch (Exception $e) {
                $this->callbackConnectError($query, $e);
                throw new ConnectErrorException();
                
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

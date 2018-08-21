<?php

namespace FcPhp\Repository\Interfaces
{
    use FcPhp\Cache\Interfaces\ICache;
    use FcPhp\Datasource\Interfaces\IQuery;
    use FcPhp\Datasource\Interfaces\IFactory;
    use FcPhp\Datasource\Interfaces\IDatasource;

    interface IRepository
    {
        /**
         * Method to construct instance
         *
         * @param FcPhp\Datasource\Interfaces\IDatasource $datasource Instance of Datasource
         * @param null|FcPhp\Cache\Interfaces\ICache $cache Instance of Cache
         * @param FcPhp\Datasource\Interfaces\IFactory $factory Instance of Factory to create Query
         * @param object $callbackError Function to send error callback
         * @return void;
         */
        public function __construct(IDatasource $datasource, ICache $cache = null, IFactory $factory, object $callbackError = null);

        /**
         * Method to execute query(ies)
         *
         * @param array|FcPhp\Datasource\Interfaces\IQuery $query Query(ies) to execute
         * @return array
         */
        public function execute($query) :array;

        /**
         * Method to return instance of Query
         *
         * @return FcPhp\Datasource\Interfaces\IQuery
         */
        public function getQuery() :IQuery;
    }
}

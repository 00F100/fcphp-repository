<?php

namespace FcPhp\Repository\Factories
{
    use FcPhp\Di\Interfaces\IDi;
    use FcPhp\Di\Facades\DiFacade;
    use FcPhp\Repository\Interfaces\IFactory;
    use FcPhp\Datasource\Query;
    use FcPhp\Datasource\Interfaces\IQuery;
    use FcPhp\Datasource\Interfaces\IStrategy;

    class Factory implements IFactory
    {
        public function __construct(IDi $di = null)
        {
            $this->di = $di;
        }

        public function getQuery(string $strategy = null) :IQuery
        {
            $factory = $this;
            if($this->di instanceof IDi) {
                if(!$this->di->has('FcPhp/Datasource/Query')) {
                    $this->di->setNonSingleton('FcPhp/Datasource/Query', 'FcPhp\Datasource\Query');
                }
                return $this->di->make('FcPhp/Datasource/Query', compact('factory', 'strategy'));
            }
            return new Query($strategy, $factory);
        }

        public function getStrategy(string $alias) :IStrategy
        {
            $namespace = str_replace('/', '\\', $alias);
            if($this->di instanceof IDi) {
                if(!$this->di->has($alias)) {
                    $this->di->setNonSingleton($alias, $namespace);
                }
                return $this->di->make($alias);
            }
            return new $namespace();
        }
    }
}

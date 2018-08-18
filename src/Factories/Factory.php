<?php

namespace FcPhp\Repository\Factories
{
    use FcPhp\Di\Interfaces\IDi;
    use FcPhp\Di\Facades\DiFacade;
    use FcPhp\Repository\Interfaces\IFactory;

    class Factory implements IFactory
    {
        public function __construct(IDi $di = null)
        {
            $this->di = $di;
        }

        public function getQuery(string $strategy = null)
        {
            if($this->di instanceof IDi) {
                if(!$this->di->has('FcPhp/Query/Query')) {
                    $this->di->setNonSingleton('FcPhp/Query/Query', 'FcPhp\Query\Query');
                }
                return $this->di->make('FcPhp/Query/Query', compact('strategy'));
            }
        }
    }
}

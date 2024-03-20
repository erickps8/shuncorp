<?php

namespace Nfe\Model\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Nfe\Model\NfeTable;

use Nfe\Model;

class NfeTableFactory implements FactoryInterface
{
    
    function __invoke(ContainerInterface $container, $requestedName, array $option = null)
    {
        $tableGateway = $container->get(Model\NfeTableGateway::class);
        return new NfeTable($tableGateway);
    }
}
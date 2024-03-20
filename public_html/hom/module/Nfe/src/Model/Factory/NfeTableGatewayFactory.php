<?php

namespace Nfe\Model\Factory;

use Interop\Container\ContainerInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Nfe\Model\Nfe;

class NfeTableGatewayFactory
{
    function __invoke(ContainerInterface $container)
    {
        $dbAdapter = $container->get(AdapterInterface::class);
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new Nfe());
        return new TableGateway('tb_nfe', $dbAdapter, null, $resultSetPrototype);
    }
}
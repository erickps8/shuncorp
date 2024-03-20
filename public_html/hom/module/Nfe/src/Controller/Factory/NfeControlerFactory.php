<?php

namespace Nfe\Controller\Factory;

use Interop\Container\ContainerInterface;
use Nfe\Controller\NfeController;
use Nfe\Model\NfeTable;
use Nfe\Form\NfeForm;

class NfeControlerFactory
{
    function __invoke(ContainerInterface $container)
    {
        $nfeTable   = $container->get(NfeTable::class);
        $nfeForm    = $container->get(NfeForm::class);
                
        return new NfeController($nfeTable, $nfeForm, $test);
    }
}
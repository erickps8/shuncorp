<?php

namespace Nfe\Form\Factory;

use Interop\Container\ContainerInterface;
use Nfe\InputFilter\NfeInputFilter;
use Nfe\Form\NfeForm;

class NfeFormFactory
{
    function __invoke(ContainerInterface $container)
    {
        $inputFilter = new NfeInputFilter();
        $form   = new NfeForm();
        $form->setInputFilter($inputFilter);
        
        return $form;
    }
}
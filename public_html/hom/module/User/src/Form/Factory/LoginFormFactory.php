<?php

namespace User\Form\Factory;

use Interop\Container\ContainerInterface;
use User\InputFilter\LoginInputFilter;
use User\Form\LoginForm;

class LoginFormFactory
{
    function __invoke(ContainerInterface $container)
    {
        $inputFilter = new LoginInputFilter();
        $form        = new LoginForm();
        $form->setInputFilter($inputFilter);
        
        return $form;
    }
}
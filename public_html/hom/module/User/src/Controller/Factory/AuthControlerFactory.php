<?php

namespace User\Controller\Factory;

use Interop\Container\ContainerInterface;
use User\Form\LoginForm;
use User\Controller\AuthController;
use Zend\Authentication\AuthenticationServiceInterface;

class AuthControlerFactory
{
    function __invoke(ContainerInterface $container)
    {
        
        $authService  = $container->get(AuthenticationServiceInterface::class);
        $loginForm    = $container->get(LoginForm::class);
                
        return new AuthController($loginForm, $authService);
    }
}
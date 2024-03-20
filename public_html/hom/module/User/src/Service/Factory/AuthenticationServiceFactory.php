<?php

namespace User\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Authentication\Adapter\DbTable\CallbackCheckAdapter;
use Zend\Authentication\Storage\Session;
use Zend\Authentication\AuthenticationService;

class AuthenticationServiceFactory
{
    function __invoke(ContainerInterface $container)
    {
        $senhaCallbackVerify = function($senhaBanco, $senha){
            return password_verify($senha, $senhaBanco);
        };
        
        $dbAdapter = $container->get(AdapterInterface::class);
        $authAdapter = new CallbackCheckAdapter($dbAdapter, 'tb_usuarios', 'email','senha',$senhaCallbackVerify);
        
        $storage = new Session();
                
        return new AuthenticationService($storage, $authAdapter);
    }
}
<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Nfe;

use Nfe\Model\Factory\NfeTableGatewayFactory;
use Nfe\Controller\NfeController;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Nfe\Model\Factory\NfeTableFactory;
use Nfe\Controller\Factory\NfeControlerFactory;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ModuleManager\Feature\ControllerProviderInterface;
use Nfe\Form\NfeForm;
use Nfe\Form\Factory\NfeFormFactory;

class Module implements ConfigProviderInterface, ServiceProviderInterface, ControllerProviderInterface
{
    const VERSION = '3.0.0dev';

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
    
    public function getServiceConfig()
    {
        return [
            'factories' => [
                Model\NfeTable::class => NfeTableFactory::class,
                Model\NfeTableGateway::class => NfeTableGatewayFactory::class,
                NfeForm::class  => NfeFormFactory::class,
            ]
        ];
    }
    
    public function getControllerConfig()
    {
        return [
            'factories' => [
                NfeController::class => NfeControlerFactory::class
            ]
        ];
    }
}

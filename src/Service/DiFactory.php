<?php

/**
 * @see       https://github.com/laminas/laminas-mvc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Service;

use Laminas\Di\Config as DiConfig;
use Laminas\Di\Di;
use Laminas\ServiceManager\Di\DiAbstractServiceFactory;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\ServiceManager\ServiceManager;

/**
 * @category   Laminas
 * @package    Laminas_Mvc
 * @subpackage Service
 */
class DiFactory implements FactoryInterface
{
    /**
     * Create and return abstract factory seeded by dependency injector
     *
     * Creates and returns an abstract factory seeded by the dependency
     * injector. If the "di" key of the configuration service is set, that
     * sub-array is passed to a DiConfig object and used to configure
     * the DI instance. The DI instance is then used to seed the
     * DiAbstractServiceFactory, which is then registered with the service
     * manager.
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return Di
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $di     = new Di();
        $config = $serviceLocator->get('Config');

        if (isset($config['di'])) {
            $di->configure(new DiConfig($config['di']));
        }

        if ($serviceLocator instanceof ServiceManager) {
            /* @var $serviceLocator ServiceManager */
            $serviceLocator->addAbstractFactory(
                new DiAbstractServiceFactory($di, DiAbstractServiceFactory::USE_SL_BEFORE_DI)
            );
        }

        return $di;
    }
}

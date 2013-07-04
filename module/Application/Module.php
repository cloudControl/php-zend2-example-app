<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Session\Container;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
    	// configure session to use database
    	$config = $e->getApplication()->getServiceManager()->get('config');
    	$dbAdapter = new \Zend\Db\Adapter\Adapter($config['db']);
    	$sessionOptions = new \Zend\Session\SaveHandler\DbTableGatewayOptions();
    	$sessionTableGateway = new \Zend\Db\TableGateway\TableGateway('session', $dbAdapter);
    	$saveHandler = new \Zend\Session\SaveHandler\DbTableGateway($sessionTableGateway, $sessionOptions);
    	$sessionManager = new \Zend\Session\SessionManager(NULL, NULL, $saveHandler);
    	Container::setDefaultManager($sessionManager);
    	
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}

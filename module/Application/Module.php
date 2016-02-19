<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use Application\Model\EnglishWord;
use Application\Model\SpanishWord;
use Application\Model\EnglishSpanishTranslation;

use Application\Model\EnglishWordTable;
use Application\Model\SpanishWordTable;
use Application\Model\EnglishSpanishTranslationTable;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
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

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Application\Model\EnglishWordTable' =>  function($sm) {
                    $tableGateway = $sm->get('EnglishWordTableGateway');
                    $table = new EnglishWordTable($tableGateway);
                    return $table;
                },
                'Application\Model\SpanishWordTable' =>  function($sm) {
                    $tableGateway = $sm->get('SpanishWordTableGateway');
                    $table = new SpanishWordTable($tableGateway);
                    return $table;
                },
                'Application\Model\EnglishSpanishTranslationTable' =>  function($sm) {
                    $tableGateway = $sm->get('EnglishSpanishTranslationTableGateway');
                    $table = new EnglishSpanishTranslationTable($tableGateway);
                    return $table;
                },
                'EnglishWordTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new EnglishWord());
                    return new TableGateway('englishWord', $dbAdapter, null, $resultSetPrototype);
                },
                'SpanishWordTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new SpanishWord());
                    return new TableGateway('spanishWord', $dbAdapter, null, $resultSetPrototype);
                },
                'EnglishSpanishTranslationTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new EnglishSpanishTranslation());
                    return new TableGateway('englishSpanishTranslation', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }
}

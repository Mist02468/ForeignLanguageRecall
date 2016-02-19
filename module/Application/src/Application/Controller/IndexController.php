<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    //static home page
    public function indexAction()
    {
        $serviceManager = $this->getServiceLocator();
        $englishWord    = $serviceManager->get('Application\Model\EnglishWordTable')->getRandomEnglishWord();
        return new ViewModel(array('englishWord' => $englishWord));
    }
}

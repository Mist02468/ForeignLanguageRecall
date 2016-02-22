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
    public function indexAction()
    {
        $serviceManager = $this->getServiceLocator();
        $result         = null;
        $resultString   = null;

        if ($this->params()->fromPost('englishWordId') && $this->params()->fromPost('spanishToCheck')) {
            list($result, $resultString) = checkAnswer($this->params()->fromPost('englishWordId'), $this->params()->fromPost('spanishToCheck'), $serviceManager);
        }

        $englishWord = $serviceManager->get('Application\Model\EnglishWordTable')->getRandomEnglishWord();
        return new ViewModel(array('englishWord' => $englishWord, 'result' => $result, 'resultString' => $resultString));
    }
}

function checkAnswer($englishWordId, $spanishToCheck, $serviceManager) {
    $englishWordTable = $serviceManager->get('Application\Model\EnglishWordTable');
    $englishWord      = $englishWordTable->getEnglishWord($englishWordId);

    $translationTable = $serviceManager->get('Application\Model\EnglishSpanishTranslationTable');
    $translations     = $translationTable->fetchAllWithEnglishWordId($englishWord->id);

    $spanishWordTable = $serviceManager->get('Application\Model\SpanishWordTable');
    $spanishWords     = array();

    foreach ($translations as $translation) {
        $spanishWord     = $spanishWordTable->getSpanishWord($translation->spanishWord_id);
        $spanishWords    = array_merge($spanishWords, $spanishWord->toStrings());
    }
    $spanishWords = array_unique($spanishWords);

    $resultVerbage = array('correct'   => array('Correct! ', ' also translates to: '),
                           'incorrect' => array('Incorrect. ', ' translates to: '));
    $result        = 'incorrect';
    $otherWords    = '';
    foreach ($spanishWords as $spanishWord) {
        if (strtolower($spanishToCheck) === strtolower($spanishWord)) {
            $result = 'correct';
        } else {
            $otherWords .= ', ' . $spanishWord;
        }
    }

    if ($result === 'correct') {
        $englishWord->numTimesCorrectlyTranslated++;
    } else {
        $englishWord->numTimesIncorrectlyTranslated++;
    }
    $englishWordTable->saveEnglishWord($englishWord);

    $otherWords   = substr($otherWords, 2);
    $resultString = $resultVerbage[$result][0] . (!empty($otherWords) ? ucfirst($englishWord->word) . $resultVerbage[$result][1] . $otherWords : '');

    return array($result, $resultString);
}

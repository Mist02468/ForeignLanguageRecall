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
        //defaults, these will not get set when the page is first loaded (before an answer has been submitted)
        $result          = null;
        $resultString    = null;
        $lastEnglishWord = null;

        //if this is a post request (after an answer has been submitted)
        if ($this->params()->fromPost('englishWordId') && $this->params()->fromPost('spanishToCheck')) {
            list($result, $resultString, $lastEnglishWord) = checkAnswer($this->params()->fromPost('englishWordId'),
                                                                         $this->params()->fromPost('spanishToCheck'),
                                                                         $serviceManager);
        }

        //either way, get a new random english word, to give to the view along with the result and resultString
        $englishWord = $serviceManager->get('Application\Model\EnglishWordTable')->getRandomEnglishWord();
        return new ViewModel(array('englishWord' => $englishWord,
                                   'result' => $result, 'resultString' => $resultString, 'lastEnglishWord' => $lastEnglishWord));
    }
}

//utility function to process the post request data, to check if the answer for the previous english word was correct
function checkAnswer($englishWordId, $spanishToCheck, $serviceManager) {
    //get the english word object from the database
    $englishWordTable = $serviceManager->get('Application\Model\EnglishWordTable');
    $englishWord      = $englishWordTable->getEnglishWord($englishWordId);

    //get all the translations objects, with this english word, from the database
    $translationTable = $serviceManager->get('Application\Model\EnglishSpanishTranslationTable');
    $translations     = $translationTable->fetchAllWithEnglishWordId($englishWord->id);

    $spanishWordTable = $serviceManager->get('Application\Model\SpanishWordTable');
    $spanishWords     = array();

    //from each of the translations, get its spanish word from the database, collect their string representations in an array
    foreach ($translations as $translation) {
        $spanishWord     = $spanishWordTable->getSpanishWord($translation->spanishWord_id);
        $spanishWords    = array_merge($spanishWords, $spanishWord->toStrings());
    }
    $spanishWords = array_unique($spanishWords); //keep only the unique strings

    $resultVerbage = array('correct'   => array('Correct! ', ' also translates to: '),
                           'incorrect' => array('Incorrect. ', ' translates to: '));
    $result        = 'incorrect';
    $otherWords    = '';
    foreach ($spanishWords as $spanishWord) {
        //if one of the translations matches the user input, they got it correct
        if (strtolower($spanishToCheck) === strtolower($spanishWord)) {
            $result = 'correct';
        } else {
            $otherWords .= ', ' . $spanishWord; //keep track of the other words in a string, to show (other) correct answers
        }
    }

    //update the english word, incrementing its numTimesCorrectly or numTimesIncorrectly
    if ($result === 'correct') {
        $englishWord->numTimesCorrectlyTranslated++;
    } else {
        $englishWord->numTimesIncorrectlyTranslated++;
    }
    $englishWordTable->saveEnglishWord($englishWord);

    //remove the first ", " from the $otherWords string
    $otherWords   = substr($otherWords, 2);
    //combine the strings, the Correct/Incorrect message as well as the (other) answers, to create the $resultString
    $resultString = $resultVerbage[$result][0] . (!empty($otherWords) ? ucfirst($englishWord->word) . $resultVerbage[$result][1] . $otherWords : '');

    return array($result, $resultString, $englishWord);
}

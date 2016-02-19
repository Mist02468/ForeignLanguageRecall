<?php

namespace Application\Model;

class EnglishWord
{
    public $id;
    public $word;
    public $type;
    public $numTimesCorrectlyTranslated;
    public $numTimesIncorrectlyTranslated;
    public $isCoreVocab;

    public function exchangeArray($data)
    {
        $this->id                            = (!empty($data['id']))                            ? $data['id'] : null;
        $this->word                          = (!empty($data['word']))                          ? $data['word'] : null;
        $this->type                          = (!empty($data['type']))                          ? $data['type'] : null;
        $this->numTimesCorrectlyTranslated   = (!empty($data['numTimesCorrectlyTranslated']))   ? $data['numTimesCorrectlyTranslated'] : null;
        $this->numTimesIncorrectlyTranslated = (!empty($data['numTimesIncorrectlyTranslated'])) ? $data['numTimesIncorrectlyTranslated'] : null;
        $this->isCoreVocab                   = (!empty($data['isCoreVocab']))                   ? $data['isCoreVocab'] : null;
    }
}

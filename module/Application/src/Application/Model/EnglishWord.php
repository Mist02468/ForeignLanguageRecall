<?php

namespace Application\Model;

//Model for the englishword database table, each has an id, word, type (ex. n, v, adj), numTimesCorrectlyTranslated (defaults to 0), numTimesIncorrectlyTranslated (defaults to 0), and isCoreVocab (defaults to false/0)
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
        $this->numTimesCorrectlyTranslated   = (!empty($data['numTimesCorrectlyTranslated']) || ($data['numTimesCorrectlyTranslated'] == '0'))     ? $data['numTimesCorrectlyTranslated'] : null;
        $this->numTimesIncorrectlyTranslated = (!empty($data['numTimesIncorrectlyTranslated']) || ($data['numTimesIncorrectlyTranslated'] == '0')) ? $data['numTimesIncorrectlyTranslated'] : null;
        $this->isCoreVocab                   = (!empty($data['isCoreVocab']))                   ? $data['isCoreVocab'] : null;
    }
}

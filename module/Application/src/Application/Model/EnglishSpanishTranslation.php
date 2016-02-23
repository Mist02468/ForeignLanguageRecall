<?php

namespace Application\Model;

//Model for the englishSpanishtranslation database table, each has an id and foreign keys to the english word and spanish word it connects as a translation
class EnglishSpanishTranslation
{
    public $id;
    public $englishWord_id;
    public $spanishWord_id;

    public function exchangeArray($data)
    {
        $this->id             = (!empty($data['id']))             ? $data['id'] : null;
        $this->englishWord_id = (!empty($data['englishWord_id'])) ? $data['englishWord_id'] : null;
        $this->spanishWord_id = (!empty($data['spanishWord_id'])) ? $data['spanishWord_id'] : null;
    }
}

<?php

namespace Application\Model;

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

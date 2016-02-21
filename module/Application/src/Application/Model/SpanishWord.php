<?php

namespace Application\Model;

class SpanishWord
{
    public $id;
    public $word;
    public $gender;
    private $genderAssociation = array('feminine'  => 'la ', 'masculine' => 'el ');

    public function exchangeArray($data)
    {
        $this->id     = (!empty($data['id']))     ? $data['id'] : null;
        $this->word   = (!empty($data['word']))   ? $data['word'] : null;
        $this->gender = (!empty($data['gender'])) ? $data['gender'] : null;
    }

    public function toStrings() {
        if ($this->gender === null) {
            return array($this->word);
        } elseif ($this->gender === 'both') {
            return array('la ' . $this->word, 'el ' . $this->word);
        } else {
            return array($this->genderAssociation[$this->gender] . $this->word);
        }
    }
}

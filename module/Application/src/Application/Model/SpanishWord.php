<?php

namespace Application\Model;

//Model for the spanishword database table, each has an id, word, and gender (feminine, feminine-plural, masculine, or masculine-plural)
class SpanishWord
{
    public $id;
    public $word;
    public $gender;
    //association from the database category to the spanish word
    private $genderAssociation = array('feminine'  => 'la ', 'masculine' => 'el ',
                                       'feminine-plural'  => 'las ', 'masculine-plural' => 'los ');

    public function exchangeArray($data)
    {
        $this->id     = (!empty($data['id']))     ? $data['id'] : null;
        $this->word   = (!empty($data['word']))   ? $data['word'] : null;
        $this->gender = (!empty($data['gender'])) ? $data['gender'] : null;
    }

    //utility function toString which takes into consideration the gender and creates multiple strings in the case of both or both-plural 
    public function toStrings() {
        if ($this->gender === null) {
            return array($this->word);
        } elseif ($this->gender === 'both') {
            return array('la ' . $this->word, 'el ' . $this->word);
        } elseif ($this->gender === 'both-plural') {
              return array('las ' . $this->word, 'los ' . $this->word);
        } else {
            return array($this->genderAssociation[$this->gender] . $this->word);
        }
    }
}

<?php

namespace Application\Model;

class SpanishWord
{
    public $id;
    public $word;
    public $gender;

    public function exchangeArray($data)
    {
        $this->id     = (!empty($data['id']))     ? $data['id'] : null;
        $this->word   = (!empty($data['word']))   ? $data['word'] : null;
        $this->gender = (!empty($data['gender'])) ? $data['gender'] : null;
    }
}

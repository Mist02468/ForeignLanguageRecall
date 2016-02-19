<?php

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

class SpanishWordTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function getSpanishWord($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        return $row;
    }

    public function getSpanishWordsByWordAndGender($word, $gender = null)
    {
        $word = (string) $word;
        if (is_null($gender) === false) {
            $gender = (string) $gender;
        }
        $resultSet = $this->tableGateway->select(array('word' => $word, 'gender' => $gender));
        return $resultSet;
    }

    public function saveSpanishWord(SpanishWord $spanishWord)
    {
        $data = array(
          'word'   => $spanishWord->word,
          'gender' => $spanishWord->type
        );

        $id = (int) $spanishWord->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
            $spanishWord->id = $this->tableGateway->getLastInsertValue();
        } else {
            if ($this->getSpanishWord($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('SpanishWord id does not exist');
            }
        }
        return $spanishWord;
    }

    public function deleteSpanishWord($id)
    {
        $this->tableGateway->delete(array('id' => (int) $id));
    }
}

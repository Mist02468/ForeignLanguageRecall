<?php

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

class EnglishSpanishTranslationTable
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

    public function fetchAllWithEnglishWordId($englishWord_id) {
        $englishWord_id = (int) $englishWord_id;
        $resultSet = $this->tableGateway->select(array('englishWord_id' => $englishWord_id));
        return $resultSet;
    }

    public function getEnglishSpanishTranslation($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        return $row;
    }

    public function saveEnglishSpanishTranslation(EnglishSpanishTranslation $englishSpanishTranslation)
    {
        $data = array(
          'englishWord_id' => $englishSpanishTranslation->englishWord_id,
          'spanishWord_id' => $englishSpanishTranslation->spanishWord_id
        );

        $id = (int) $englishSpanishTranslation->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
            $englishSpanishTranslation->id = $this->tableGateway->getLastInsertValue();
        } else {
            if ($this->getEnglishSpanishTranslation($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('EnglishSpanishTranslation id does not exist');
            }
        }
        return $englishSpanishTranslation;
    }

    public function deleteEnglishSpanishTranslation($id)
    {
        $this->tableGateway->delete(array('id' => (int) $id));
    }
}

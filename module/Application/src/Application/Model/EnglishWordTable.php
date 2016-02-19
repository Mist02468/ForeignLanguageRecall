<?php

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;

class EnglishWordTable
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

    public function getEnglishWord($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        return $row;
    }

    public function getRandomEnglishWord($onlyCoreVocab = true)
    {
        $adapter = $this->tableGateway->getAdapter();
        $sql     = new Sql($adapter);
        $select  = $sql->select();
        $select->from($this->tableGateway->getTable())
               ->columns(array('num' => new Expression('COUNT(*)')));
        if ($onlyCoreVocab === true) {
            $select->where(array('isCoreVocab' => 1));
        }

        $stmt    = $sql->getSqlStringForSqlObject($select);
        $results = $adapter->query($stmt, $adapter::QUERY_MODE_EXECUTE)->current()->getArrayCopy();
        $num     = (int) $results['num'];

        $offset = rand(0, $num - 1);

        $rowset = $this->tableGateway->select(function ($select) use ($offset) {
            $select->where(array('isCoreVocab' => 1));
            $select->offset($offset);
            $select->limit(1);
        });
        $row = $rowset->current();
        return $row;
    }

    public function getEnglishWordsByWordAndType($word, $type = null)
    {
        $word = (string) $word;
        if (is_null($type) === false) {
            $type = (string) $type;
        }
        $resultSet = $this->tableGateway->select(array('word' => $word, 'type' => $type));
        return $resultSet;
    }

    public function saveEnglishWord(EnglishWord $englishWord)
    {
        $data = array(
          'word'                          => $englishWord->word,
          'type'                          => $englishWord->type,
          'numTimesCorrectlyTranslated'   => $englishWord->numTimesCorrectlyTranslated,
          'numTimesIncorrectlyTranslated' => $englishWord->numTimesIncorrectlyTranslated,
          'isCoreVocab'                   => $englishWord->isCoreVocab
        );

        $id = (int) $englishWord->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
            $englishWord->id = $this->tableGateway->getLastInsertValue();
        } else {
            if ($this->getEnglishWord($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('EnglishWord id does not exist');
            }
        }
        return $englishWord;
    }

    public function deleteEnglishWord($id)
    {
        $this->tableGateway->delete(array('id' => (int) $id));
    }
}

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

    //Utility function to return a random english word, by default only from those marked with isCoreVocab
    public function getRandomEnglishWord($onlyCoreVocab = true)
    {
        //need more flexibility, so creating my own Sql object
        $adapter = $this->tableGateway->getAdapter();
        $sql     = new Sql($adapter);
        $select  = $sql->select();

        //create a select count(*) on this table, where isCoreVocab = 1 if $onlyCoreVocab is true
        $select->from($this->tableGateway->getTable())
               ->columns(array('num' => new Expression('COUNT(*)')));
        if ($onlyCoreVocab === true) {
            $select->where(array('isCoreVocab' => 1));
        }
        //execute that select and store the result as $num
        $stmt    = $sql->getSqlStringForSqlObject($select);
        $results = $adapter->query($stmt, $adapter::QUERY_MODE_EXECUTE)->current()->getArrayCopy();
        $num     = (int) $results['num'];

        //randomly choose a number between 0 and $num-1 inclusive
        $offset = rand(0, $num - 1);

        //select the randomly chosen english word, using offset and limit
        $rowset = $this->tableGateway->select(function ($select) use ($offset, $onlyCoreVocab) {
            if ($onlyCoreVocab === true) {
                $select->where(array('isCoreVocab' => 1));
            }
            $select->offset($offset);
            $select->limit(1);
        });
        $row = $rowset->current();
        return $row;
    }

    //select (and could possibly return multiple) by word and type
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

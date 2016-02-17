<?php

use Phinx\Migration\AbstractMigration;

class InitialMigrationCreateTranslationsTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $englishTable = $this->table('englishWord');
        $englishTable->addColumn('word', 'string')
                     ->addColumn('type', 'string')
                     ->addColumn('numTimesCorrectlyTranslated', 'integer', array('default' => 0))
                     ->addColumn('numTimesIncorrectlyTranslated', 'integer', array('default' => 0))
                     ->create();

        $spanishTable = $this->table('spanishWord');
        $spanishTable->addColumn('word', 'string')
                     ->addColumn('gender', 'string', array('null' => true))
                     ->create();

        $translationTable = $this->table('englishSpanishTranslation');
        $translationTable->addColumn('englishWord_id', 'integer')
                         ->addForeignKey('englishWord_id', 'englishWord', 'id', array('delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION'))
                         ->addColumn('spanishWord_id', 'integer')
                         ->addForeignKey('spanishWord_id', 'spanishWord', 'id', array('delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION'))
                         ->create();
    }
}

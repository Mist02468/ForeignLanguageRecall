<?php

//descripters for the csv columns
$columns = array(
            'word' => 0,
            'type' => 1
           );

//setup the database and csv file handle
$secrets  = parse_ini_file('config/autoload/localSecrets.ini');
$dbHandle = new PDO('mysql:host=' . $secrets['Host'] . ';port=' . $secrets['Port'] . ';dbname=' . $secrets['Database'], $secrets['Username'], $secrets['Password']);
$csvFileHandle = fopen("coreVocabulary.csv", "r");

if ($csvFileHandle !== False) {
    //for each row of the csv, find that word in the database and set its isCoreVocab field to 1
    while (($csvRow = fgetcsv($csvFileHandle)) !== False) {

        $coreVocabularyWord = trim($csvRow[$columns['word']]);
        $coreVocabularyType = trim($csvRow[$columns['type']]);

        //basic update, will be used unless the type column is not empty
        $query = 'UPDATE englishword SET isCoreVocab = 1 WHERE word = :word';
        $stmt  = $dbHandle->prepare($query);

        $hasType = false;
        if (empty($coreVocabularyType) == false) {
            $hasType = true;
            //add these to the basic update, since the type column is not empty
            $stmt = $dbHandle->prepare($query . ' AND type = :type');
            $stmt->bindParam(':type', $coreVocabularyType, PDO::PARAM_STR);
        }

        $stmt->bindParam(':word', $coreVocabularyWord, PDO::PARAM_STR);
        $stmt->execute();

        //using the type might have been too specific, try just using the word if the last update did not change any rows
        if ($hasType && ($stmt->rowCount() == 0)) {
            $stmt = $dbHandle->prepare($query);
            $stmt->bindParam(':word', $coreVocabularyWord, PDO::PARAM_STR);
            $stmt->execute();
        }
    }
    fclose($csvFileHandle);
} else {
    print("Was not able to open coreVocabulary.csv");
}

<?php

$columns = array(
            'word' => 0,
            'type' => 1
           );

$secrets  = parse_ini_file('config/autoload/localSecrets.ini');
$dbHandle = new PDO('mysql:host=' . $secrets['Host'] . ';port=' . $secrets['Port'] . ';dbname=' . $secrets['Database'], $secrets['Username'], $secrets['Password']);
$csvFileHandle = fopen("coreVocabulary.csv", "r");

if ($csvFileHandle !== False) {
    while (($csvRow = fgetcsv($csvFileHandle)) !== False) {

        $coreVocabularyWord = trim($csvRow[$columns['word']]);
        $coreVocabularyType = trim($csvRow[$columns['type']]);

        $query = 'UPDATE englishword SET isCoreVocab = 1 WHERE word = :word';
        $stmt  = $dbHandle->prepare($query);

        $hasType = false;
        if (empty($coreVocabularyType) == false) {
            $hasType = true;
            $stmt = $dbHandle->prepare($query . ' AND type = :type');
            $stmt->bindParam(':type', $coreVocabularyType, PDO::PARAM_STR);
        }

        $stmt->bindParam(':word', $coreVocabularyWord, PDO::PARAM_STR);
        $stmt->execute();

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

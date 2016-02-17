<?php

$columns = array(
            'english'    => 0,
            'wordType'   => 1,
            'spanish'    => 2,
            'definition' => 3
           );

$secrets  = parse_ini_file('config/autoload/localSecrets.ini');
$dbHandle = new PDO('mysql:host=' . $secrets['Host'] . ';port=' . $secrets['Port'] . ';dbname=' . $secrets['Database'], $secrets['Username'], $secrets['Password']);
$csvFileHandle = fopen("bilingualDictionaryFromWikitonary.csv", "r");

if ($csvFileHandle !== False) {
    while (($csvRow = fgetcsv($csvFileHandle)) !== False) {

        $englishWord     = trim($csvRow[$columns['english']]);
        $englishWordType = trim($csvRow[$columns['wordType']]);

        $stmt = $dbHandle->prepare('INSERT INTO englishword (word, type) VALUES (:word, :type)');
        $stmt->bindParam(':word', $englishWord, PDO::PARAM_STR);
        $stmt->bindParam(':type', $englishWordType, PDO::PARAM_STR);
        $stmt->execute();
        $englishWord_id = (int) $dbHandle->lastInsertId();

        $spanishWords = trim($csvRow[$columns['spanish']]);
        $spanishWords = explode(',', $spanishWords);
        foreach ($spanishWords as $spanishWord) {
            $stmt = $dbHandle->prepare('INSERT INTO spanishword (word) VALUES (:word)');

            list($gender, $spanishWord) = identifyAndRemoveGender($spanishWord);

            if ($gender !== null) {
                $stmt = $dbHandle->prepare('INSERT INTO spanishword (word, gender) VALUES (:word, :gender)');
                $stmt->bindParam(':gender', $gender, PDO::PARAM_STR);
            }
            $stmt->bindParam(':word', $spanishWord, PDO::PARAM_STR);
            $stmt->execute();
            $spanishWord_id = (int) $dbHandle->lastInsertId();

            $stmt = $dbHandle->prepare('INSERT INTO englishspanishtranslation (englishWord_id, spanishWord_id) VALUES (:englishWord_id, :spanishWord_id)');
            $stmt->bindParam(':englishWord_id', $englishWord_id, PDO::PARAM_INT);
            $stmt->bindParam(':spanishWord_id', $spanishWord_id, PDO::PARAM_INT);
            $stmt->execute();
        }
    }
    fclose($csvFileHandle);
} else {
    print("Was not able to open bilingualDictionaryFromWikitonary.csv");
}

function identifyAndRemoveGender($spanish) {
    $gender = null;
    if (strpos($spanish, '{m}') !== false) {
        $gender = 'masculine';
    }
    if (strpos($spanish, '{f}') !== false) {
        if ($gender == 'masculine') {
            $gender = 'both';
        } else {
            $gender = 'feminine';
        }
    }
    $spanish = str_replace(array('{m}', '{f}'), '', $spanish);
    $spanish = trim($spanish);
    return array($gender, $spanish);
}

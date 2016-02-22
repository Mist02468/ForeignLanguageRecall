<?php

$columns = array(
            'english'    => 0,
            'wordType'   => 1,
            'spanish'    => 2,
            'definition' => 3
           );

$secrets  = parse_ini_file('config/autoload/localSecrets.ini');
$dbHandle = new PDO('mysql:host=' . $secrets['Host'] . ';port=' . $secrets['Port'] . ';dbname=' . $secrets['Database'] . ';charset=utf8', $secrets['Username'], $secrets['Password']);
$csvFileHandle = fopen("bilingualDictionaryFromWikitonary.csv", "r");

if ($csvFileHandle !== False) {
    while (($csvRow = fgetcsv($csvFileHandle)) !== False) {

        $englishWord     = trim($csvRow[$columns['english']]);
        $englishWordType = trim($csvRow[$columns['wordType']]);

        $stmt = $dbHandle->prepare('SELECT id FROM englishword WHERE word = :word AND type = :type');
        $stmt->bindParam(':word', $englishWord, PDO::PARAM_STR);
        $stmt->bindParam(':type', $englishWordType, PDO::PARAM_STR);
        $stmt->execute();
        $results = $stmt->fetchAll();

        if (count($results) === 0) {
            //this english word has not been added to the database yet, do so and note the id
            $stmt = $dbHandle->prepare('INSERT INTO englishword (word, type) VALUES (:word, :type)');
            $stmt->bindParam(':word', $englishWord, PDO::PARAM_STR);
            $stmt->bindParam(':type', $englishWordType, PDO::PARAM_STR);
            $stmt->execute();
            $englishWord_id = (int) $dbHandle->lastInsertId();
        } elseif (count($results) === 1) {
            //this english word is in the database, note the id
            $englishWord_id = (int) $results[0]['id'];
        } else {
            //this english word is in the database more than once, this should not occur
            throw new Exception('Found more than one row in the englishword table with the word ' . $englishWord . ' and type ' . $englishWordType);
        }

        $spanishWords = trim($csvRow[$columns['spanish']]);
        $spanishWords = str_replace(';', ',', $spanishWords);
        $spanishWords = preg_replace('#(\([^\)]*\)|\[[^\]]*\])#', '', $spanishWords);
        $spanishWords = trim($spanishWords);
        $spanishWords = explode(',', $spanishWords);

        foreach ($spanishWords as $rawSpanishWord) {
            list($gender, $spanishWord) = identifyAndRemoveGender($rawSpanishWord);
            if (empty($spanishWord) === true) {
                continue;
            }

            $stmt = $dbHandle->prepare('SELECT id FROM spanishword WHERE word = :word AND gender = :gender');
            $stmt->bindParam(':word', $spanishWord, PDO::PARAM_STR);
            $stmt->bindParam(':gender', $gender, PDO::PARAM_STR);
            $stmt->execute();
            $results = $stmt->fetchAll();

            if (count($results) === 0) {
                //this spanish word has not been added to the database yet, do so and note the id
                $stmt = $dbHandle->prepare('INSERT INTO spanishword (word) VALUES (:word)');
                if ($gender !== null) {
                    $stmt = $dbHandle->prepare('INSERT INTO spanishword (word, gender) VALUES (:word, :gender)');
                    $stmt->bindParam(':gender', $gender, PDO::PARAM_STR);
                }
                $stmt->bindParam(':word', $spanishWord, PDO::PARAM_STR);
                $stmt->execute();
                $spanishWord_id = (int) $dbHandle->lastInsertId();
            } elseif (count($results) === 1) {
                //this spanish word is in the database, note the id
                $spanishWord_id = (int) $results[0]['id'];
            } else {
                //this spanish word is in the database more than once, this should not occur
                throw new Exception('Found more than one row in the spanishword table with the word ' . $spanishWord . ' and gender ' . $gender);
            }

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
    } elseif (strpos($spanish, '{m-p}') !== false) {
        $gender = 'masculine-plural';
    }

    if (strpos($spanish, '{f}') !== false) {
        if ($gender !== null) {
            $gender = 'both';
        } else {
            $gender = 'feminine';
        }
    } elseif (strpos($spanish, '{f-p}') !== false) {
        if ($gender !== null) {
            $gender = 'both-plural';
        } else {
            $gender = 'feminine-plural';
        }
    }

    $spanish = str_replace(array('{m}', '{f}', '{m-p}', '{f-p}'), '', $spanish);
    $spanish = trim($spanish);
    return array($gender, $spanish);
}

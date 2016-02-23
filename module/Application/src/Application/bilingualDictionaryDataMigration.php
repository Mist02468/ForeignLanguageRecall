<?php

//descripters for the csv columns
$columns = array(
            'english'    => 0,
            'wordType'   => 1,
            'spanish'    => 2,
            'definition' => 3
           );

//setup the database and csv file handle
$secrets  = parse_ini_file('config/autoload/localSecrets.ini');
$dbHandle = new PDO('mysql:host=' . $secrets['Host'] . ';port=' . $secrets['Port'] . ';dbname=' . $secrets['Database'] . ';charset=utf8', $secrets['Username'], $secrets['Password']);
$csvFileHandle = fopen("bilingualDictionaryFromWikitonary.csv", "r");

if ($csvFileHandle !== False) {
    //for each row of the csv, insert (if necessary) the english word, spanish word(s), and the translation link
    while (($csvRow = fgetcsv($csvFileHandle)) !== False) {

        $englishWord     = trim($csvRow[$columns['english']]);
        $englishWordType = trim($csvRow[$columns['wordType']]);

        //check if this english word is already in the database
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

        //clean the spanish words column
        $spanishWords = trim($csvRow[$columns['spanish']]); //trim whitespace
        $spanishWords = str_replace(';', ',', $spanishWords); //there are some semicolon delimiters, replace with commans
        $spanishWords = preg_replace('#(\([^\)]*\)|\[[^\]]*\])#', '', $spanishWords); //remove ()s and []s information
        $spanishWords = trim($spanishWords); //trim whitespace, now that some other characters have been removed
        $spanishWords = explode(',', $spanishWords); //turn the string of spanish words into an array

        foreach ($spanishWords as $rawSpanishWord) {
            list($gender, $spanishWord) = identifyAndRemoveGender($rawSpanishWord);
            //should not happen often, but continue (do not add this spanish word to the database) if we're left with an empty string after cleaning
            if (empty($spanishWord) === true) {
                continue;
            }

            //check if this spanish word is already in the database
            $stmt = $dbHandle->prepare('SELECT id FROM spanishword WHERE word = :word AND gender = :gender');
            $stmt->bindParam(':word', $spanishWord, PDO::PARAM_STR);
            $stmt->bindParam(':gender', $gender, PDO::PARAM_STR);
            $stmt->execute();
            $results = $stmt->fetchAll();

            if (count($results) === 0) {
                //this spanish word has not been added to the database yet, do so and note the id
                $stmt = $dbHandle->prepare('INSERT INTO spanishword (word) VALUES (:word)'); //basic insert, to use unless a gender designator was provided
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

            //create the translation link
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

//function which looks for {m}, {f}, {m-p}, or {f-p}, notes it as the gender and removes that designation from the word
function identifyAndRemoveGender($spanish) {
    $gender = null;

    if (strpos($spanish, '{m}') !== false) {
        $gender = 'masculine';
    } elseif (strpos($spanish, '{m-p}') !== false) {
        $gender = 'masculine-plural';
    }

    if (strpos($spanish, '{f}') !== false) {
        if ($gender !== null) {
            $gender = 'both'; //some words will include {m} {f}
        } else {
            $gender = 'feminine';
        }
    } elseif (strpos($spanish, '{f-p}') !== false) {
        if ($gender !== null) {
            $gender = 'both-plural'; //some words will include {m-p} {f-p}
        } else {
            $gender = 'feminine-plural';
        }
    }

    //remove the designations and trim
    $spanish = str_replace(array('{m}', '{f}', '{m-p}', '{f-p}'), '', $spanish);
    $spanish = trim($spanish);

    return array($gender, $spanish); //return both the extracted gender and the cleaned word
}

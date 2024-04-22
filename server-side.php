<?php

// Function to read JSON data from a file
function readJsonFile($filename) {
    $jsonData = file_get_contents($filename);
    return json_decode($jsonData, true);
}

// Function to replace placeholders in the answer
function replacePlaceholders($answer) {
    $answer = str_replace('{model}', '1', $answer);
    $answer = str_replace('{modell}', '1', $answer);
    $answer = str_replace('{modeli}', '1', $answer);
    $answer = str_replace('{muaj}', 'Tetorë', $answer);
    $answer = str_replace('{datë}', '2024', $answer);
    $answer = str_replace('{monat}', 'Oktober', $answer);
    $answer = str_replace('{datum}', '2024', $answer);
    $answer = str_replace('{month}', 'October', $answer);
    $answer = str_replace('{date}', '2024', $answer);
    
    // Replace {number} with 10
    $answer = str_replace('{number}', '10', $answer);
    
    // Replace {section} with Million
    $answer = str_replace('{section}', 'Million', $answer);
    
    return $answer;
}

// Function to replace "WhiteGPT" with "WhiteDASo-1" in the answer
function replaceWhiteGPT($answer) {
    return str_replace('WhiteGPT', 'AIU', $answer);
}

// Function to replace "WhiteAi-GPT" with "DOSaAI" in the answer
function replaceWhiteAi_GPT($answer) {
    return str_replace('WhiteAi-GPT', 'DOSaAI', $answer);
}

// Function to find a similar result for the user's question
function findSimilarResult($dataArray1, $dataArray2, $curseArray, $resultsArray, $userQuestion) {
    // Check if the user's question contains explicit words
    foreach ($curseArray as $curseWord) {
        if (stripos($userQuestion, $curseWord) !== false) {
            // User has used explicit words, randomly retrieve answer from results.json
            $responses = $resultsArray["explicit_words_responses"];
            $randomIndex = array_rand($responses);
            return $responses[$randomIndex];
        }
    }

    // Split the user's question into words
    $userWords = preg_split('/\s+/', $userQuestion);

    // Initialize variables to store the best matching question and its score
    $bestMatch = null;
    $bestScore = 0;

    // Check if the question exists in either array
    foreach ([$dataArray1, $dataArray2] as $dataArray) {
        foreach ($dataArray as $qa) {
            $questionWords = preg_split('/\s+/', $qa["question"]);
            $score = count(array_intersect($userWords, $questionWords));
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $qa["answer"];
            } elseif ($score == $bestScore) {
                // If multiple questions have the same score, randomly choose one
                if (rand(0, 1) == 1) {
                    $bestMatch = $qa["answer"];
                }
            }
        }
    }

    return $bestMatch;
}

// Get the user's question from the request
$inputData = json_decode(file_get_contents("php://input"));

// Check if decoding is successful
if ($inputData === null && json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["error" => "Failed to decode input data. Check JSON format."]);
    exit;
}

// Check if the question property exists in the input data
if (isset($inputData->question)) {
    $userQuestion = $inputData->question;

    // Save the user question to the "questions.json" file
    $questionsData = json_decode(file_get_contents("https://raw.githubusercontent.com/DOSaAI/English-Machine-Database-JSON/main/questions.json"), true);
    $questionsData[] = $userQuestion;
    file_put_contents("https://raw.githubusercontent.com/DOSaAI/English-Machine-Database-JSON/main/questions.json", json_encode($questionsData, JSON_PRETTY_PRINT));

    // Read JSON data from the files
    $dataArray1 = readJsonFile('https://raw.githubusercontent.com/DOSaAI/English-Machine-Database-JSON/main/machine_learning_1.json');
    $dataArray2 = readJsonFile('https://raw.githubusercontent.com/DOSaAI/English-Machine-Database-JSON/main/machine_learning_2.json');
    $curseArray = readJsonFile('https://raw.githubusercontent.com/DOSaAI/English-Machine-Database-JSON/main/curse.json');
    $resultsArray = readJsonFile('https://raw.githubusercontent.com/DOSaAI/English-Machine-Database-JSON/main/results.json');

    // Find a similar result for the user's question
    $similarAnswer = findSimilarResult($dataArray1, $dataArray2, $curseArray, $resultsArray, $userQuestion);

    // If no similar result, show a default response
    if (!$similarAnswer) {
        $similarAnswer = "Sorry, I wasn't trained on more Dataset to be able to understand you.";
    }

    // Replace "WhiteGPT" with "WhiteDASo-1" in the answer
    $similarAnswer = replaceWhiteGPT($similarAnswer);

    // Replace "WhiteAi-GPT" with "DOSaAI" in the answer
    $similarAnswer = replaceWhiteAi_GPT($similarAnswer);

    // Replace specific placeholders in the answer
    $similarAnswer = replacePlaceholders($similarAnswer);

    echo json_encode(["answer" => $similarAnswer]);
} else {
    // If the question property is not present in the input data
    echo json_encode(["error" => "Invalid input data. 'question' property not found."]);
}

?>

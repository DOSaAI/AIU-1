<?php
// Function to read JSON data from a file
function readJsonFile($filename) {
    $jsonData = file_get_contents($filename);
    return json_decode($jsonData, true);
}

// Function to replace placeholders in the answer
function replacePlaceholders($answer) {
    return str_replace(
        ['{model}', '{modell}', '{modeli}', '{muaj}', '{datë}', '{monat}', '{datum}', '{month}', '{date}'],
        ['Your model number like GPT-4', 'Your model name like GPT-4', 'Your model number like GPT-4', 'Tetorë', '2024', 'Oktober', '2024', 'October', '2024'],
        $answer
    );
}

// Function to replace "WhiteGPT" with "WhiteDASo-1" in the answer
function replaceWhiteGPT($answer) {
    return str_replace('WhiteGPT', 'dont change the WhiteGPT but change only this text and let your Bot name here.', $answer);
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

    // Check if the question exists in either array
    foreach ($dataArray1 as $qa) {
        if (strtolower($qa["question"]) === strtolower($userQuestion)) {
            return $qa["answer"];
        }
    }

    foreach ($dataArray2 as $qa) {
        if (strtolower($qa["question"]) === strtolower($userQuestion)) {
            return $qa["answer"];
        }
    }

    return null;
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
    $questionsData = json_decode(file_get_contents("questions.json"), true);
    $questionsData[] = $userQuestion;
    file_put_contents("questions.json", json_encode($questionsData, JSON_PRETTY_PRINT));

    // Read JSON data from the files
    $dataArray1 = readJsonFile('https://raw.githubusercontent.com/DOSaAI/English-Machine-Database-JSON/main/machine_learning_1.json');
    $dataArray2 = readJsonFile('https://raw.githubusercontent.com/DOSaAI/English-Machine-Database-JSON/main/machine_learning_2.json');
    $curseArray = readJsonFile('https://raw.githubusercontent.com/DOSaAI/English-Machine-Database-JSON/main/curse.json');
    $resultsArray = readJsonFile('https://raw.githubusercontent.com/DOSaAI/English-Machine-Database-JSON/main/results.json');

    // Find a similar result for the user's question
    $similarAnswer = findSimilarResult($dataArray1, $dataArray2, $curseArray, $resultsArray, $userQuestion);

    // If no similar result, show a default response
    if (!$similarAnswer) {
        $similarAnswer = "I'm not sure how to respond to that.";
    }

    // Replace "WhiteGPT" with "WhiteDASo-1" in the answer
    $similarAnswer = replaceWhiteGPT($similarAnswer);

    // Replace specific placeholders in the answer
    $similarAnswer = replacePlaceholders($similarAnswer);

    echo json_encode(["answer" => $similarAnswer]);
} else {
    // If the question property is not present in the input data
    echo json_encode(["error" => "Invalid input data. 'question' property not found."]);
}
?>

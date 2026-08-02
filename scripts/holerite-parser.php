<?php

require dirname(__DIR__, 1).'/base.php';

if(empty($_SERVER['OPENAI_API_KEY']))
    exit("The API key could not be found!\n");

if(empty($_SERVER['API_URL']))
    exit("The API url could not be found!\n");

$Skil = getSkil(pathinfo(__FILE__, PATHINFO_FILENAME));

if(empty($Skil))
    exit("The skil could not be found!\n");

if(empty($_SERVER['MODEL']))
    exit("The Model could not be found!\n");

$Schema = getSchema('holerite');

if(empty($Schema))
    exit("The schema could not be found!\n");

$MaxTokens = 300;

//sudo snap install tabula

$InputFolder = isset($argv[1]) ? $argv[1] : exit;
$OutputFolder = isset($argv[2]) ? $argv[2] : '';

if(!is_dir($InputFolder))
    exit("The specified input path does not lead to a folder!\n");

if(substr($InputFolder, -1) != '/')
    $InputFolder .= '/';

if(empty($OutputFolder)){

    echo "Output folder not specified; using the input folder as the output folder!\n";
    $OutputFolder = $InputFolder;

} else {

    if(!is_dir($OutputFolder))
        exit("The specified input path does not lead to a folder!\n");

    if(substr($OutputFolder, -1) != '/')
        $OutputFolder .= '/';

}

$Paths = glob($InputFolder.'*.pdf');

if(empty($Paths))
    exit("No files found!\n");

foreach($Paths as $Path){


    $Command = "tabula -p 1 '$Path'";
    $Output = [];
    $ResultCode = 0;

    exec($Command, $Output, $ResultCode);

    if(!empty($ResultCode) || empty($Output))
        exit("File issue: $Path");

    $Content = basename($Path)."\n";

    foreach($Output as $Line){
        $Content .= $Line."\n";
    }

    $Body = [
        "model" => $_SERVER['MODEL'],
        "input" => [["role" => "system","content" => [["type" => "input_text","text" => $Skil]]],["role" => "user","content" => [["type" => "input_text","text" => $Content]]]],
        "text" => ["format" => $Schema]
    ];

    $Options = ['http' => ['ignore_errors' => true, 'timeout' => 60,'header'  => "Content-type: application/json\r\nAuthorization: Bearer ".$_SERVER['OPENAI_API_KEY'],'method'  => 'POST', 'content' => json_encode($Body)]];
    $Result = @file_get_contents($_SERVER['API_URL'].'/v1/responses', false, stream_context_create($Options));

    if(empty($Result))
        continue;

    $Result = json_decode($Result,true);

    if(!empty($Result['error'])){

        echo $Path.' - '.$Result['error']['message']."\n";
        continue;

    }

    if(empty($Result["output"][0]["content"][0]["text"])){

        echo $Path." - No message content!\n";
        continue;

    }

    $Response = json_decode($Result["output"][0]["content"][0]["text"], true);


    var_dump($Response);exit;

}
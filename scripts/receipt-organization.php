<?php

require dirname(__DIR__, 1).'/base.php';

if(empty($_SERVER['OPENAI_API_KEY']))
    exit("The API key could not be found!\n");

if(empty($_SERVER['API_URL']))
    exit("The API url could not be found!\n");

$Skil = getSkil(pathinfo(__FILE__, PATHINFO_FILENAME));

if(empty($Skil))
    exit("The skil could not be found!\n");

$Model = 'gpt-4o';
$MaxTokens = 300;

$InputFolder = isset($argv[1]) ? $argv[1] : exit;
$OutputFolder = isset($argv[2]) ? $argv[2] : '';

if(!is_dir($InputFolder))
    exit("The specified input path does not lead to a folder!\n");

$Last = substr($InputFolder, -1);

if(substr($InputFolder, -1) != '/')
    $InputFolder .= '/';

if(empty($OutputFolder)){

    echo "Output folder not specified; using the input folder as the output folder!\n";
    $OutputFolder = $InputFolder;

} else {

    if(!is_dir($OutputFolder))
        exit("The specified input path does not lead to a folder!\n");

    $Last = substr($OutputFolder, -1);

    if(substr($OutputFolder, -1) != '/')
        $OutputFolder .= '/';

}

$Paths = glob($InputFolder.'*.png');

if(empty($Paths))
    exit("No files found!\n");

foreach($Paths as $Path){

    $Extension = pathinfo($Path, PATHINFO_EXTENSION);
    $Content = file_get_contents($Path);
    $DataUri = "data:image/$Extension;base64,".base64_encode($Content);
    $Data = ['model' => $Model, 'messages' => [['role' => 'user','content' => [['type' => 'text','text' => $Skil], ['type' => 'image_url', 'image_url' => ['url' => $DataUri]]]]],'max_tokens' => $MaxTokens, "response_format" => ['type' => 'json_object']];

    $Options = ['http' => ['ignore_errors' => true, 'timeout' => 5,'header'  => "Content-type: application/json\r\nAuthorization: Bearer ".$_SERVER['OPENAI_API_KEY'],'method'  => 'POST', 'content' => json_encode($Data)]];
    $Result = @file_get_contents($_SERVER['API_URL'].'/v1/chat/completions', false, stream_context_create($Options));

    if(empty($Result))
        continue;

    $Result = json_decode($Result,true);

    if(!empty($Result['error'])){

        echo $Path.' - '.$Result['error']['message']."\n";
        continue;

    }

    if(empty($Result['choices'][0]['message']['content'])){

        echo $Path." - No message content!\n";
        continue;

    }

    $Response = json_decode($Result['choices'][0]['message']['content'], true);

    $FileName = '';

    if(!empty($Response['identifier'])){

        $Response['identifier'] = str_replace(".","",$Response['identifier']);
        $Response['identifier'] = str_replace("/","",$Response['identifier']);
        $Response['identifier'] = str_replace("-","",$Response['identifier']);
        $FileName = $Response['identifier'].'|';

    }

    $Response['amount'] = str_replace(".","-",$Response['amount']);

    $FileName .= $Response['payment_date'].'|'.$Response['payee'].'|'.$Response['amount'].".$Extension";
    $FileResult = file_put_contents($OutputFolder.$FileName, $Content);

}
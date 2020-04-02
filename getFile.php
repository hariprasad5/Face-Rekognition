<?php

$botKey = 'Bot_token'; //Put Your Bot Token ID

$userId = 'chat_id'; //Chat id or User id

$file_path_finder_url = 'https://api.telegram.org/bot'.$botKey.'/getFile?file_id=';

$telegram_file_link = 'https://api.telegram.org/file/bot'.$botKey.'/';

$request = json_decode(file_get_contents('php://input'),true);

$file_id = $request["message"]["photo"][1]["file_id"];

if(is_null($file_id)){
	$text = "Please Upload a Photo for Facial Recognition.\n\nNote: If there are multiple humans in the photo:\n\n->Human at the left-most corner is Person-1.\n->Human to the immediate right  of Person-1 is Person-2 and so on.";
        $request_params = [
                'chat_id' => $userId,
                'text' => $text
        ];

        $request_url = 'https://api.telegram.org/bot'.$botKey.'/sendmessage?'.http_build_query($request_params);

	file_get_contents($request_url);

	exit();
}


$file_path_json = json_decode(file_get_contents($file_path_finder_url.$file_id),true);

file_put_contents('s.jpg',file_get_contents($telegram_file_link.$file_path_json["result"]["file_path"]));


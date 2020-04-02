<?php
	include 'getFile.php';
	// Installed the need packages with Composer by running:
	// $ composer require aws/aws-sdk-php
	// Minimum version of PHP>=5.5 and AWS-SDK above 3
        require 'vendor/autoload.php';

        use Aws\Rekognition\RekognitionClient;
	include 'EC2-S3.php';
	try {
		if($imageUrl) {
		//echo "Image upload done... Here is the URL: " . $imageUrl;
		$rekognition = new RekognitionClient([
			'region' 	=> 'us-east-2',
			'version' 	=> 'latest',
		]);

		$result = $rekognition->detectFaces([
			'Attributes'	=> ["ALL"], //"DEFAULT" value for faster response
			'Image' => [
				'S3Object' => [
					'Bucket' => $bucketName,
					'Name' 	=> 	$keyName,
					'Key' 	=> 	$keyName,
				],
			],
		]);
		unlink($keyName);
		unlink($tempFilePath);
		$numbers = count($result["FaceDetails"]);
		$text_to_telegram = "Totally there are ".$numbers." faces\n\n";
		$json = $result["FaceDetails"];
		usort($json, function($a,$b) {
		        return $a["BoundingBox"]["Left"] < $b["BoundingBox"]["Left"] ? -1 : 1;
		});
		$indexes = [];
		for($k=0;$k<$numbers;$k++){
			$j = 0;
			$max = $json[$k]["Emotions"][0]["Confidence"];
			for($i=1;$i<8;$i++){
				if ($json[$k]["Emotions"][$i]["Confidence"]>$max){
					$max = $json[$k]["Emotions"][$i]["Confidence"];
					$j=$i;
				}
			}
		array_push($indexes,$j);
		}
		$count = 1;
		foreach($json as $person){
			$text_to_telegram = $text_to_telegram."Person ".$count." - Age Range : ".$person['AgeRange']['Low']." - ".$person['AgeRange']['High'].", Gender : ".$person['Gender']['Value'].", Emotion : ".$person['Emotions'][$indexes[$count-1]]['Type'].", Confidence : ".$person['Confidence']." \n\n";
			$count++;
		}
		$request_params = [
        		'chat_id' => $userId,
        		'text' => $text_to_telegram
		];


		$request_url = 'https://api.telegram.org/bot'.$botKey.'/sendmessage?'.http_build_query($request_params);

		file_get_contents($request_url);


		}
	} catch (Exception $e) {
		echo $e->getMessage();
	}


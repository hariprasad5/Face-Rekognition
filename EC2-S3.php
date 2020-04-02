<?php
	require 'vendor/autoload.php';
        use Aws\S3\S3Client;
        use Aws\S3\Exception\S3Exception;

        $bucketName = 'bucket_name'; //Your Bucket Name
        $filePath = 'Path_to_file(image.jpg)'; //File path
        $keyName = basename($filePath);

        //Enable IAM Role to use factory method without using 'credentials' in the method
        // Set Amazon S3 Credentials
        $s3 = S3Client::factory(array(
                        'version' => 'latest',
                        'region'  => 'us-east-2',
                        'signature' =>'v4'
                )
                                );

  // The region matters. I'm using "US Ohio" so "us-east-2" is the corresponding
  // region code. You can google it or upload a file to the S3 bucket and look at
  // the public url. It will look like:
  // https://s3.us-east-2.amazonaws.com/YOUR_BUCKET_NAME/image.png
  // As you can see the us-east-2 in the url

        try {
                // So you need to move the file on $filePath to a temporary place.
                // The solution being used: http://stackoverflow.com/questions/21004691/downloading-a-file-and-saving-it-locally-with-php
                if (!file_exists('/tmp/tmpfile')) {
                        mkdir('/tmp/tmpfile');
                }

                // Create temp file
                $tempFilePath = '/tmp/tmpfile/' . basename($filePath);
                $tempFile = fopen($tempFilePath, "w") or die("Error: Unable to open file.");
                $fileContents = file_get_contents($filePath);
                $tempFile = file_put_contents($tempFilePath, $fileContents);


                // Put on S3
                $result = $s3->putObject(
                        array(
                                'Bucket'=>$bucketName,
                                'Key' =>  $keyName,
                                'SourceFile' => $tempFilePath,
                                'StorageClass' => 'REDUCED_REDUNDANCY'
                        )
                );
                $imageUrl = $result['ObjectURL'];
	}catch (Exception $e) {
                echo $e->getMessage();
        }





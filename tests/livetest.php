<?php

@ini_set('log_errors', 1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/php-errors.log');

use ErnestMarcinko\WaifuVault\WaifuApi;

require_once __DIR__ . "/../vendor/autoload.php";


$waifu = new WaifuApi();

try {
	$response = $waifu->uploadFile(array(
		'file' =>   __DIR__ . '/WaifuTests/image.jpg',
		'filename' => 'mypenisisveryshort.jpg'
	));
	$waifu->deleteEntry($response->token);

//	$response = $waifu->uploadFile(array(
//		'file' =>   __DIR__ . '/WaifuTests/image.jpg',
//		'filename' => 'mypenisisveryshort.jpg',
//		'password' => 'penis1'
//	));
//	var_dump($response);

	$response = $waifu->uploadFile(array(
		'file_contents' =>   file_get_contents(__DIR__ . '/WaifuTests/image.jpg'),
		'filename' => 'mypenisisverylongits1inchesgirl.jpg',
		'password' => 'penis2'
	));
	var_dump($response);

	var_dump($waifu->getFileInfo($response->token));

	$file_contents = $waifu->getFile(array(
		'token' => $response->token,
		'password' => 'penis1'
	));

	file_put_contents(__DIR__ . '/files/penis1.jpg', $file_contents);

	$response = $waifu->modifyEntry(array(
		'token' => $response->token,
		'hideFilename' => true,
		'customExpiry' => '1d'
	));
	var_dump($response);

	var_dump($waifu->getFileInfo($response->token));

	//$response = $waifu->getFileInfo( '13b2485a-1010-4e3e-8f75-20f2a0c50b56');

	/*$response = $waifu->modifyEntry( array(
		'token' => '13b2485a-1010-4e3e-8f75-20f2a0c50b56',
		'hideFilename' => true,
		'customExpiry' => '1d'
	));
	var_dump($response);*/

	/*$response1 = $waifu->deleteEntry($response->token );
	var_dump($response1);*/
	//$response = $waifu->getFileInfo( $response->token);
} catch (Exception $e) {
	var_dump($e->getMessage());
}

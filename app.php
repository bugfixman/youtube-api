<?php

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
  throw new \Exception('please run "composer require google/apiclient:~2.0" in "' . __DIR__ .'"');
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/sys/config.php';
require_once __DIR__ . '/sys/function.php';

$client  = clientInit();
$youtube = new Google_Service_YouTube($client);
$youApi  = new youApi($client, $youtube);
$youApi->autoRefreshToken();

if($client->getAccessToken()){
	//вызов методов youtube api
	$props = [
		'title'       => 'Тестовая заливка',
		'description' => 'Тестируем скрипт uploader',
		'tags'        => 'ништяк, ура, победа', //Тэги писать именно в таком виде через запятую
		'status'      => 'private' //Допустимые значения public, private, unlisted
	];

	$res = $youApi->uploadOnYoutube(VIDEO_PATH.'/video.mp4', $props);

	if(!is_array($res)){
		print 'Ошибка: '.$res;
	} else {
		print 'Видео успешно загружено на youtube! ID: '.$res['id'].' Заголовок: '.$res['title'];
		//Из id можно сделать ссылку на видео
	}

} else {
	print 'Cрок действия токена истёк необходима <a href="'.$client->createAuthUrl().'">Авторизация</a>';
}
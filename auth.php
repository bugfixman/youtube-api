<?php

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
  throw new \Exception('please run "composer require google/apiclient:~2.0" in "' . __DIR__ .'"');
}
require_once __DIR__ . '/vendor/autoload.php';
include_once __DIR__ . '/sys/config.php';

$client = clientInit();

if(isset($_GET['code'])){
  $client->authenticate($_GET['code']);
  $fp = fopen(ACCESS_TOKEN, 'w+');
  fwrite($fp, json_encode($client->getAccessToken()));
  fclose($fp);
  header('Location: http://' . $_SERVER['HTTP_HOST'] . '/app.php');
}

if(!isset($_GET['code'])){
	$currentToken = file_get_contents(ACCESS_TOKEN);
	$client->setAccessToken($currentToken);

	if(!$client->isAccessTokenExpired()){
		print 'Токен ещё жив авторизация не требуется';
	} else {
		print 'Срок действия токена истёк необходима <a href="'.$client->createAuthUrl().'">Авторизация</a>';
	}
}
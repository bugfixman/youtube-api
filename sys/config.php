<?php

const ACCESS_TOKEN  = './tokens/access_token.json';
const REFRESH_TOKEN = './tokens/refresh_token.json';
const VIDEO_PATH    = './videos/';

function clientInit(){
	$client = new Google_Client();
	$client->setAuthConfig('./client_secret.json');
	$client->setAccessType('offline');
	$client->setApprovalPrompt("force");
	$client->setScopes('https://www.googleapis.com/auth/youtube');
	$redirect = filter_var('http://' . $_SERVER['HTTP_HOST'] . '/youtube_api/auth.php', FILTER_SANITIZE_URL);
	$client->setRedirectUri($redirect);
	return $client;
}
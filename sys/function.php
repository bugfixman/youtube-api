<?php

function logger($str){
	$fp = fopen('./log.txt', 'a');
    fwrite($fp, PHP_EOL.'['.date('d.m.Y H:i:s').']: '.$str.PHP_EOL);
	fclose($fp);
}

class youApi{
    private $client;
    private $youtube;

    public function __construct($client, $youtube){
        $this->client  = $client;
        $this->youtube = $youtube;
    }

    public function uploadOnYoutube($videoPath, $videoMetaData){
        try{
            $snippet = new Google_Service_YouTube_VideoSnippet();
            $snippet->setTitle($videoMetaData['title']);
            $snippet->setDescription($videoMetaData['description']);
            $snippet->setTags(array($videoMetaData['tags']));

// Numeric video category. See
// https://developers.google.com/youtube/v3/docs/videoCategories/list

            $snippet->setCategoryId("22");

// Set the video's status to "public". Valid statuses are "public",
// "private" and "unlisted".

            $status = new Google_Service_YouTube_VideoStatus();
            $status->privacyStatus = $videoMetaData['status'];

            $video = new Google_Service_YouTube_Video();
            $video->setSnippet($snippet);
            $video->setStatus($status);

            $chunkSizeBytes = 1 * 1024 * 1024;
            $this->client->setDefer(true);
            $insertRequest = $this->youtube->videos->insert("status,snippet", $video);

            $media = new Google_Http_MediaFileUpload(
                $this->client,
                $insertRequest,
                'video/*',
                null,
                true,
                $chunkSizeBytes
            );

            $media->setFileSize(filesize($videoPath));

            $status = false;
            $handle = fopen($videoPath, "rb");

            while(!$status && !feof($handle)){
                $chunk = fread($handle, $chunkSizeBytes);
                $status = $media->nextChunk($chunk);
            }

            fclose($handle);
            $this->client->setDefer(false);

            return [
                'title' => $status['snippet']['title'],
                'id'    => $status['id']
            ];

        } catch (Google_Service_Exception $e) {
            $err = $e->getMessage();
            logger(sprintf('A service error occurred: %s', $err));
            return $err;

        } catch (Google_Exception $e) {
            $err = $e->getMessage();
            logger(sprintf('An client error occurred: %s', $err));
            return $err;
        }
    }

    public function autoRefreshToken($timeOut = 3570){
        //Здесь нужен только refresh_token
        $accessToken  = json_decode(file_get_contents(ACCESS_TOKEN));
        //Отсюда считываем свежий access_token
        $refreshToken = json_decode(file_get_contents(REFRESH_TOKEN));
        $newToken = null;

        if(time() - $accessToken->created < $timeOut){
            $jsonKey = $accessToken;
        } else {
            $jsonKey = $refreshToken;
        }

        if(time() - $jsonKey->created > $timeOut){
            $newToken = json_encode($this->client->refreshToken($accessToken->refresh_token));

            $fp = fopen(REFRESH_TOKEN, 'w+');
            fwrite($fp, $newToken);
            $newToken = fgets($fp);
            fclose($fp);
        }

        if(time() - $jsonKey->created < $timeOut){
            if($newToken){
                $this->client->setAccessToken($newToken);
            } else {
                $this->client->setAccessToken(json_encode($jsonKey));
            }
        }
    }
}
<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once(__DIR__ . '/vendor/LINEBotTiny.php');

function debugToLine($message)
{
    global $CONF;
	return logToLine($CONF['DEBUG_ROOM_ID'], $message);
}

function warnToLine($message)
{
    global $CONF;
	return logToLine($CONF['LISTEN_ROOM_ID'], $message);
}

function logToLine($room,$message)
{
    global $CONF;
    $channelAccessToken = $CONF['BOT_CHANNEL_ACCESS'];
    $channelSecret = $CONF['BOT_CHANNEL_SECRET'];
    $client = new LINEBotTiny($channelAccessToken, $channelSecret);
    $client->pushMessage(['to' =>$room, 'messages' => [['type' => 'text', 'text' => $message]]]);
    return;
}
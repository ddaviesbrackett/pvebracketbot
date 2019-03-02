<?php
require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/vendor/LINEBotTiny.php');
function trigger_aimbots($update)
{
	global $CONF;
	$channelAccessToken = $CONF['AIMBOT_CHANNEL_ACCESS'];
	$channelSecret = $CONF['AIMBOT_CHANNEL_SECRET'];
	$client = new LINEBotTiny($channelAccessToken, $channelSecret);
	if(mb_strtolower($update["count"]) == "flip" || filter_var($update["count"], FILTER_SANITIZE_NUMBER_INT) > $CONF["AIMBOT_THRESHOLD"])
	{
		foreach ($CONF["AIMBOT_ROOMS"] as $room)
		{
			$msg = $update["slice"] . " " . $update["count"] . " " . $update["lag"];
			$client->pushMessage(['to' =>$room, 'messages' => [['type' => 'text', 'text' => $msg]]]);
		}
	}
}
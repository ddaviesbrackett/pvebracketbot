<?php

require_once(__DIR__ . '/vendor/LINEBotTiny.php');
require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/config.php');

use Carbon\Carbon;
$channelAccessToken = $CONF['BOT_CHANNEL_ACCESS'];
$channelSecret = $CONF['BOT_CHANNEL_SECRET'];

$client = new LINEBotTiny($channelAccessToken, $channelSecret);

function sendMessage($destination, $msg) {
	global $client;
	$message = ['to' =>$destination, 'messages' => [['type' => 'text', 'text' => $msg]]];
	$client->pushMessage($message);
}

$messageText = "";

if(php_sapi_name() != 'cli'){return;}

if($argv[1] == 'sliceend') {
	$endingSlice = shell_exec("php " . __DIR__ . "/sheetclient.php getsliceend");
	if(!isset($endingSlice))
	{
		return;
	}
	if($endingSlice === 's1')
	{
		# s1 is ending: beginning of a new event!
		# unhide column A, update C3 to the format 'old / new'
	}

	# work for all slice ends:
	#	1) Clear the count (D), update time(H), time of last flip(K), # of flips(M) for each CL
	#	2) update the counts (D) with the most recent prejoin counts (Q) if it exists, with the appropriate update time (H)
	#	3) set the event moniker (A) appropriately
	#	4) update Formulas!W
	# 		S4, S5, S6, S7, S8 updates Slice 1, Slice 2, Slice 3, Slice 4, Slice 5 respectively
	# 		this updates the Updates worksheet with the new Event End Countdown for the slice

	if($endingSlice === 's5')
	{
		# s5 is ending: all slices on the new event
		# update C3 to the format 'new'
		# hide column A
		# update Formulas!W
	}
}

?>

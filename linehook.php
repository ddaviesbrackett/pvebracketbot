<?php
require_once(__DIR__ . '/vendor/LINEBotTiny.php');
require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/vendor/autoload.php');
$channelAccessToken = $CONF['BOT_CHANNEL_ACCESS'];
$channelSecret = $CONF['BOT_CHANNEL_SECRET'];

use Carbon\Carbon;
use Carbon\CarbonInterval;

$client = new LINEBotTiny($channelAccessToken, $channelSecret);
foreach ($client->parseEvents() as $event) {
    switch ($event['type']) {
        case 'message':
            $message = $event['message'];
            switch ($message['type']) {
                case 'text':
                    if(isset($event['source']['groupId']) && $event['source']['groupId'] == $CONF['LISTEN_ROOM_ID'])
                    {
                        $matches = [];
                        if(preg_match('/^([12345]\.[6789])\s*@\s*(\d+)(\s*,\s*(\d+d|\d+h\d+m?|\d+m)|)$/', trim($message['text']), $matches) == true)
                        {
                            $command = 'php ' . __DIR__ . '/sheetclient.php !countupdate ';

                            $slice = mb_strtolower($matches[1]);
                            $count = mb_strtolower($matches[2]);
                            $timestring = $matches[4];
                            if(preg_match('/^\d+h\d+$/', $timestring))
                            {
                                $timestring .= 'm';
                            }
                            if(empty($timestring))
                            {
                                $timestring = "now";
                            }
                            $updatetime = CarbonInterval::fromString($timestring);
                            $resp = shell_exec($command . ' "' . $slice . '" "' . $count . '" "' . $updatetime . '"');
                            $out = strpos($resp, "got it") !== false ? 'Score recorded, @' . $name : 'something went wrong, go find Serrated';
                            $client->replyMessage([
                                    'replyToken' => $event['replyToken'],
                                    'messages' => [
                                        [
                                            'type' => 'text',
                                            'text' => $out
                                        ]
                                    ]
                                ]);
                        }
                    }
                    break;
                default:
                    break;
            }
            $source = $event['source'];
            if(isset($event['source']['groupId']) && $event['source']['groupId'] == $CONF['DEBUG_ROOM_ID'])
            {
                $profile = $client->profile($event['source']['userId']);
                error_log('got a message from user ID ' . $event['source']['userId'] . ', displayName '.$profile->displayName.' message '.$message['text']);

                $client->replyMessage([
                    'replyToken' => $event['replyToken'],
                    'messages' => [
                        [
                            'type' => 'text',
                            'text' => 'from user ID:'. $event['source']['userId'] . '
                                echoing: '.$message['text'] .'
                                displayName: '. $profile->displayName
                            ]
                        ]
                    ]);

             }

            break;
        case 'join':
            $source = $event['source'];
            error_log('joined somewhere, type: '.$source['type'].', groupId:'.$source['groupId'].'');
            break;
        default:
            break;
    }
};
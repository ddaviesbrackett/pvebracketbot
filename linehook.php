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
                    $source = $event['source'];
                    if(isset($source['groupId']) && $source['groupId'] == $CONF['LISTEN_ROOM_ID'])
                    {
                        $matches = [];
                        if(preg_match('/^([12345]\.[6789])\s*@\s*(\d+|flip)(\s*,\s*(\d+d|\d+h\d+m?|\d+m)|)$/', trim($message['text']), $matches) == true)
                        {
                            $command = 'php72 ' . __DIR__ . '/sheetclient.php !countupdate ';

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
                            $updatelag = CarbonInterval::fromString($timestring);
                            $updatetime = Carbon::now('America/Toronto')->sub($updatelag);
                            $resp = shell_exec($command . ' "' . $slice . '" "' . $count . '" "' . $updatetime->format('m/d/Y H:i') . '"');
                            $out = strpos($resp, "got it") !== false ? 'update recorded!': 'something went wrong, go find Serrated';
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
                        else if (trim($message['text']) == '!currentstate')
                        {
                            $command = 'php72 ' . __DIR__ . '/sheetclient.php !currentstate ';
                            $resp = shell_exec($command . ' "' . $slice . '" "' . $count . '" "' . $updatetime . '"');
                            $client->replyMessage([
                                    'replyToken' => $event['replyToken'],
                                    'messages' => [
                                        [
                                            'type' => 'text',
                                            'text' => $resp
                                        ]
                                    ]
                                ]);

                        }
                    }
                    else if(isset($source['groupId']) && $source['groupId'] == $CONF['DEBUG_ROOM_ID'])
                    {
                        $profile = $client->profile($source['userId']);
                        error_log('got a message from user ID ' . $source['userId'] . ', displayName '.$profile->displayName.' message '.$message['text']);

                        $client->replyMessage([
                            'replyToken' => $event['replyToken'],
                            'messages' => [
                                [
                                    'type' => 'text',
                                    'text' => 'from user ID:'. $source['userId'] . '
                                        echoing: '.$message['text'] .'
                                        displayName: '. $profile->displayName
                                    ]
                                ]
                            ]);
                     }

                    break;
                default:
                    break;
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

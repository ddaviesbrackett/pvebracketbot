<?php
require_once(__DIR__ . '/vendor/LINEBotTiny.php');
require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/vendor/autoload.php');
$channelAccessToken = $CONF['BOT_CHANNEL_ACCESS'];
$channelSecret = $CONF['BOT_CHANNEL_SECRET'];

use Carbon\Carbon;
use Carbon\CarbonInterval;

$helptext=[
'' => 'available help:
!help: general usage (this message)
!help count: how to tell me to update a count
!help sliceend: how to tell me to alter the sheet for a new event'
,'count' => "
    count update formats:
    <slice><count>[report lag]
    <slice>@<count>[report lag]
    
    whitespace allowed between bracketed components

    <slice>: standard slice designators, e.g. 1.9 or 5.7
    
    <count>: any sequence of numbers, or the word 'flip' (not case sensitive)
    
    [report lag]:
        optional comma, followed by one of:
        
        ##h
        ##m
        ##h##m
        ##h##

        where ## is a 1- or 2-digit number. 

    examples: 

    1.9 333 2h
    1.9 @ 444 45m
    5.7@555, 1h15m
    3.6 666, 3h20"
,'sliceend' => "
    (not yet implemented)
"
];

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
                        if(preg_match('/^([12345]\.[6789])\s*[@ ]\s*(\d+|flip)(\s*,?\s*(\d+h|\d+h\s*\d+m?|\d+m)|)$/i', trim($message['text']), $matches) == true)
                        {
                            $command = 'php72 ' . __DIR__ . '/sheetclient.php countupdate ';

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
                            $args = ' "' . $slice . '" "' . $count . '" "' . $updatetime->format('m/d/Y H:i') . '"';

                            $resp = shell_exec($command . $args);

                            $out = strpos($resp, "got it") !== false ? 'update recorded as '. $args .'': 'something went wrong, go find Serrated';
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
                        else
                        {
                            switch (explode(" ", trim($message['text']))[0]) 
                            {
                                case '!currentstate':
                                    $command = 'php72 ' . __DIR__ . '/sheetclient.php currentstate ';
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
                                    break;
                                case '!help':
                                    $helpsubset=explode(" ", trim($message['text']))[1];
                                    if(empty($helpsubset))
                                    {
                                        $helpsubset='';
                                    }
                                    $resp = $helptext[$helpsubset];
                                    $client->replyMessage([
                                            'replyToken' => $event['replyToken'],
                                            'messages' => [
                                                [
                                                    'type' => 'text',
                                                    'text' => $resp
                                                ]
                                            ]
                                        ]);
                                    break;
                            }
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

<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once(__DIR__ . '/vendor/LINEBotTiny.php');


define('SHEETS_APPLICATION_NAME', 'PVE sheet bot backend client');
define('SHEETS_CLIENT_SECRET_PATH', __DIR__ . '/client_secret.json');
// If modifying these scopes, delete your previously saved credentials
// at SHEETS_CREDENTIALS_PATH (defined in config.php)
define('SHEETS_SCOPES', implode(' ', array(
  Google_Service_Sheets::SPREADSHEETS)
));

if (php_sapi_name() != 'cli') {
  throw new Exception('This application must be run on the command line.');
}

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient() {
  $client = new Google_Client();
  $client->setApplicationName(SHEETS_APPLICATION_NAME);
  $client->setScopes(SHEETS_SCOPES);
  $client->setAuthConfig(SHEETS_CLIENT_SECRET_PATH);
  $client->setAccessType('offline');

  // Load previously authorized credentials from a file.
  $credentialsPath = expandHomeDirectory(SHEETS_CREDENTIALS_PATH);
  if (file_exists($credentialsPath)) {
    $accessToken = json_decode(file_get_contents($credentialsPath), true);
  } else {
    // Request authorization from the user.
    $authUrl = $client->createAuthUrl();
    printf("Open the following link in your browser:\n%s\n", $authUrl);
    print 'Enter verification code: ';
    $authCode = trim(fgets(STDIN));

    // Exchange authorization code for an access token.
    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

    // Store the credentials to disk.
    if(!file_exists(dirname($credentialsPath))) {
      mkdir(dirname($credentialsPath), 0700, true);
    }
    file_put_contents($credentialsPath, json_encode($accessToken));
    printf("Credentials saved to %s\n", $credentialsPath);
  }
  $client->setAccessToken($accessToken);

  // Refresh the token if it's expired.
  if ($client->isAccessTokenExpired()) {
    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
    file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
  }
  return $client;
}

/**
 * Expands the home directory alias '~' to the full path.
 * @param string $path the path to expand.
 * @return string the expanded path.
 */
function expandHomeDirectory($path) {
  $homeDirectory = getenv('HOME');
  if (empty($homeDirectory)) {
    $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
  }
  return str_replace('~', realpath($homeDirectory), $path);
}

// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Sheets($client);

$spreadsheetId = $CONF['SHEET_TO_UPDATE'];

//hardcode locations in the sheet - this could be looked up based on the rowindex of column B, but it's faster to hardcode. (saves a network round trip)
$slice2row = [ 
  '1.9' => '4'
  ,'2.9' => '5'
  ,'3.9' => '6'
  ,'4.9' => '7'
  ,'5.9' => '8'
  ,'1.8' => '10'
  ,'2.8' => '11'
  ,'3.8' => '12'
  ,'4.8' => '13'
  ,'5.8' => '14'
  ,'1.7' => '16'
  ,'2.7' => '17'
  ,'3.7' => '18'
  ,'4.7' => '19'
  ,'5.7' => '20'
  ,'1.6' => '22'
  ,'2.6' => '23'
  ,'3.6' => '24'
  ,'4.6' => '25'
  ,'5.6' => '26'
  ,'cl9' => '28' //PVP
  ,'cl8' => '29' //PVP
  ,'cl7' => '30' //PVP
  ,'cl6' => '31' //PVP
];


if ($argv[1] == 'countupdate' || $argv[1] == 'pvpupdate') { //!countupdate 2.9 338 "7/19/2018 6:22:00"
  $slice = $argv[2];
  $row = $slice2row[$slice];

  $countcell = 'D' . $row;
  $updatetimecell = 'H' . $row;
  $fliptimecell = 'K' . $row;
  $flipcountcell = 'M' . $row;

  $count = $argv[3];
  $updatetime = $argv[4];

  $previous = $service->spreadsheets_values->batchGet($spreadsheetId, ["ranges"=>[$countcell, $updatetimecell]]);

  $data = [
    ['range' => $updatetimecell, 'values' => [[$updatetime]]]
  ];
  if($count == 'flip' || $count == 'flip-update'){
    if($count == 'flip') {
      $flipcount = $service->spreadsheets_values->get($spreadsheetId, $flipcountcell)[0][0];
      if(empty($flipcount)) {
        $flipcount = 1;
      }
      else {
        $flipcount += 1;
      }
      $data[] = ['range' => $flipcountcell, 'values' => [[$flipcount]]];
    }
    $data[] = ['range' => $fliptimecell, 'values' => [[$updatetime]]];
  }
  if($count != 'lag-update' && $count != 'flip-update') {
    $data[] = ['range' => $countcell, 'values' => [[$count]]];
  }
  $requestBody = new Google_Service_Sheets_BatchUpdateValuesRequest();
  $requestBody->setData($data);

  $requestBody->setValueInputOption('USER_ENTERED');
  $requestBody->setIncludeValuesInResponse(true);

  $new = $service->spreadsheets_values->batchUpdate($spreadsheetId, $requestBody);

  $newtime = $new[0]["updatedData"][0][0]; //time is the first
  $newcount = $new[count($data) - 1]["updatedData"][0][0]; //count is the last

  print "got it: ". $slice . " updated\nfrom " . $previous[0][0][0] . ' ' . $previous[1][0][0] . "\nto " . $newcount . ' ' . $newtime;
}
else if ($argv[1] == 'nextevent')
{
  $ev = $argv[2];
  $data = [
    ['range' => 'Formulas!W18', 'values' => [[$ev]]]
  ];

  $requestBody = new Google_Service_Sheets_BatchUpdateValuesRequest();
  $requestBody->setData($data);

  $requestBody->setValueInputOption('USER_ENTERED');
  $requestBody->setIncludeValuesInResponse(true);

  $new = $service->spreadsheets_values->batchUpdate($spreadsheetId, $requestBody);
  print "got it: next event set to " . $new[0]["updatedData"][0][0];
}
else if ($argv[1] == 'sliceend')
{
  $sheetData = $service->spreadsheets_values->batchGet($spreadsheetId, [
    'ranges'=>['Formulas!U4:U8', 'Formulas!W16:W19', 'Updates!P4:Q26', 'Formulas!S4:S8', 'Formulas!X19'],
    'majorDimension' => 'COLUMNS',
    'dateTimeRenderOption' => 'SERIAL_NUMBER',
    'valueRenderOption' => 'UNFORMATTED_VALUE'
    ]);
  $slicesToProcess = [];
  $endedSlices = $sheetData['valueRanges'][0]["values"][0];
  $currentEvent = $sheetData['valueRanges'][1]["values"][0][1];
  $nextEventNumber = $sheetData['valueRanges'][1]["values"][0][2];
  $nextEvent = $sheetData['valueRanges'][1]["values"][0][3];
  $prejoinInfo = $sheetData['valueRanges'][2]["values"];
  $eventEndTimes = $sheetData['valueRanges'][3]["values"][0];
  $nextEventLength = $sheetData['valueRanges'][4]["values"][0][0];
  
  for( $i = 0; $i < 5; $i++)
  {
    $endedSlice = $endedSlices[$i];
    if($endedSlice === 'over')
    {
      $slicesToProcess[] = $i + 1;
    }
  }

  if(count($slicesToProcess) === 0)
  {
    print 'got it: no slice ends to process right now';
    return; //nothing to do
  }


  if($currentEvent === $nextEvent)
  {
    $channelAccessToken = $CONF['BOT_CHANNEL_ACCESS'];
    $channelSecret = $CONF['BOT_CHANNEL_SECRET'];
    $client = new LINEBotTiny($channelAccessToken, $channelSecret);
    $msg = 'uhoh: the next event hasn\'t been set yet, and at least one slice is over! time to go look up the id and tell me what the next event is ("next event ##").  Don\'t worry, I\'ll do the slice-end stuff as soon as you tell me what the new event is.';
    $client->pushMessage(['to' =>$CONF['LISTEN_ROOM_ID'], 'messages' => [['type' => 'text', 'text' => $msg]]]);
    print 'got it: updaters nagged';
    return;
  }

  $nextEventAbbrev = preg_replace('/[^A-Z]/', '', $nextEvent);

  $operations = [];
  $updates = [];

  if(in_array(1, $slicesToProcess))
  {
    # when the first slice ends, a new event starts - unhide Updates!A, add new event to Updates!C3
    $operations["updateDimensionProperties"] = changeColumnVisibility(true);
    $updates[] = ['range' => 'Updates!C3', 'values' => [[$nextEvent . ' / ' . $currentEvent]]];
  }

  foreach($slicesToProcess as $slice)
  {
    $row = $slice + 3;
    $updates[] = ['range' => 'Updates!A' . $row, 'values' => [[$nextEventAbbrev]]];
    $updates[] = ['range' => 'Formulas!S' . $row, 'values' => [[$eventEndTimes[$slice - 1] + $nextEventLength]]];
    for($i = 0; $i < 4; $i++)
    {
      //clear flip time + count
      $updates[] = ['range' => 'Updates!K' . $row, 'values' => [['']]];
      $updates[] = ['range' => 'Updates!M' . $row, 'values' => [['']]];
      
      //clear prejoin count + time
      //$updates[] = ['range' => 'Updates!P' . $row, 'values' => [['']]];
      //$updates[] = ['range' => 'Updates!Q' . $row, 'values' => [['']]];
      
      //move prejoin info to current info
      //$updates[] = ['range' => 'Updates!D' . $row, 'values' => [[$prejoinInfo[0][$row - 3]]]];
      //$updates[] = ['range' => 'Updates!H' . $row, 'values' => [[$prejoinInfo[1][$row - 3]]]];
      
      $row += 6;
    }
  }

  if(in_array(5, $slicesToProcess))
  {
    $operations["updateDimensionProperties"] = changeColumnVisibility(false);
    $updates[] = ['range' => 'Formulas!W16', 'values' => [[$nextEventNumber]]]; //make next event current event
    $updates[] = ['range' => 'Updates!C3', 'values' => [[$nextEvent]]];
  }

  $requestBody = new Google_Service_Sheets_BatchUpdateValuesRequest();
  $requestBody->setData($updates);
  $requestBody->setValueInputOption('USER_ENTERED');
  $requestBody->setIncludeValuesInResponse(false);
  $service->spreadsheets_values->batchUpdate($spreadsheetId, $requestBody);

  if(count($operations) > 0)
  {
    $requestBody = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest();
    $requestBody->setRequests($operations);
    $requestBody->setIncludeSpreadsheetInResponse(false);
    $requestBody->setResponseIncludeGridData(false);
    $service->spreadsheets->batchUpdate($spreadsheetId, $requestBody);
  }

  print "got it: slice changeover happened for slice(s) " . join(',', $slicesToProcess);
}
else {
  print "invalid parameters";
}

function changeColumnVisibility($show)
{
  $req = new Google_Service_Sheets_UpdateDimensionPropertiesRequest();
  
  $range = new Google_Service_Sheets_DimensionRange();
  $range->setDimension('COLUMNS');
  $range->setStartIndex(0);
  $range->setEndIndex(1);
  $range->setSheetId(0);
  $req->setRange($range);
  $req->setFields("*");

  $props = new Google_Service_Sheets_DimensionProperties();
  $props->setHiddenByUser(!$show);
  $props->setPixelSize($show?60:0);
  $req->setProperties($props);

  return $req;
}
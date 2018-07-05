<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

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

$commandtoRange = [
  '!currentstate' => 'Updates!B2:F26'
  //,!countupdate is special-cased, below
];


if (array_key_exists($argv[1], $commandtoRange)) {
  $range = $commandtoRange[$argv[1]];
  $response = $service->spreadsheets_values->get($spreadsheetId, $range);
  $values = $response->getValues();

  if (count($values) == 0) {
    print "No data found.\n";
  } else {
    foreach ($values as $row) {
      printf("%s %s%s\t%s (predicted:%s)\n", $row[0], $row[1], $row[2], $row[3], $row[4] );
    }
  }
} else if ($argv[1] == '!countupdate'){
  $countcell = $slice2countcell[$argv[2]];
  $updatetimecell = $slice2updatetimecell[$argv[2]];
  $count = $argv[3];
  $updatetime = $argv[4];

  $optParams=[
    'valueInputOption' => 'USER_ENTERED'
    ,'includeValuesInResponse' => false
  ];
  $requestBody = new Google_Service_Sheets_BatchUpdateValuesRequest();
  $requestBody.setData([
    ['range' => $countcell, 'values' => [$count]]
    ,['range' => $updatetimecell, 'values' => [$updatetime]]
  ]);

  $response = $service->spreadsheets_values->batchUpdate($spreadsheetId, $requestBody, $optParams);
  print "got it";

}

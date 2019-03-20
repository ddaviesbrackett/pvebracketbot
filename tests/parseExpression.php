<?php
require_once(__DIR__ . '/../parser.php');
if (php_sapi_name() != 'cli') {
  throw new Exception('This application must be run on the command line.');
}

$parser = new PhpPegJs\Parser;
var_dump($parser->parse($argv[1]));

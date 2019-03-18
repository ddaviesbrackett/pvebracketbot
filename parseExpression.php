<?php
require_once(__DIR__ . '/parser.php');

$parser = new PhpPegJs\Parser;
var_dump($parser->parse($argv[1]));
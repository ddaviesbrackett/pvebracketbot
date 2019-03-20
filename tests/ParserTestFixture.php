<?php
declare(strict_types=1);
require_once(__DIR__ . '\..\parser.php');

if (php_sapi_name() != 'cli') {
  throw new Exception('This application must be run on the command line.');
}

use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
	protected $parser;

	protected function setUp(): void
	{
		$this->parser = new PhpPegJs\Parser;
	}
}

<?php
declare(strict_types=1);
require_once(__DIR__ . '\..\parser.php');

use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
	protected $parser;

	protected function setUp(): void
	{
		$this->parser = new PhpPegJs\Parser;
	}
}

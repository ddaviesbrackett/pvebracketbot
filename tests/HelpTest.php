<?php
declare(strict_types=1);
require_once(__DIR__ . '/ParserTestFixture.php');

final class HelpTest extends ParserTest
{
	private function doTests($throwawaySuffix): void
	{
		$this->assertEquals(["h" => ["arg"=> NULL]], $this->parser->parse("!help" . $throwawaySuffix));
		$this->assertEquals(["h" => ["arg"=> "count"]], $this->parser->parse("!help count" . $throwawaySuffix));
		$this->assertEquals(["h" => ["arg"=> "nextevent"]], $this->parser->parse("!help nextevent" . $throwawaySuffix));
	}

	public function testHelp(): void
	{
		$this->doTests('');
		$this->doTests(' this is throwaway text that does not matter.');
	}

}

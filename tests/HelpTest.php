<?php
declare(strict_types=1);
require_once(__DIR__ . '/ParserTestFixture.php');

final class HelpTest extends ParserTest
{
	public function testHelp(): void
	{
		$this->assertEquals(
			$this->parser->parse("!help"), 
			["h" => ["arg"=> NULL]]
		);
		$this->assertEquals(
			$this->parser->parse("!help count"), 
			["h" => ["arg"=> "count"]]
		);
		$this->assertEquals(
			$this->parser->parse("!help nextevent"), 
			["h" => ["arg"=> "nextevent"]]
		);
	}

}

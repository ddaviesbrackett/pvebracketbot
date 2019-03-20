<?php
declare(strict_types=1);
require_once(__DIR__ . '/ParserTestFixture.php');

/*
Tests season counts with no report lag.
*/
final class ImmediatePvpTest extends ParserTest
{

	protected function setUp(): void
	{
		parent::setUp();
		$this->randomSeasonCount = rand(1,4999); //up to 4999 for pvp seasons
		$this->randomSeasonDesignator = 'cl' . rand(6,9);
	}

	public function testCount(): void
	{
		$desired = ["pvp" => [
				"slice"=> $this->randomSeasonDesignator
				,"count" => $this->randomSeasonCount
				,"lag" => NULL
				]
			];
		$this->assertEquals($desired, $this->parser->parse('pvp ' . $this->randomSeasonDesignator . " " . $this->randomSeasonCount));
		$this->assertEquals($desired, $this->parser->parse('pvp ' . $this->randomSeasonDesignator . "@" . $this->randomSeasonCount));
		$this->assertEquals($desired, $this->parser->parse('pvp ' . $this->randomSeasonDesignator . "@ " . $this->randomSeasonCount));
		$this->assertEquals($desired, $this->parser->parse('pvp ' . $this->randomSeasonDesignator . " @ " . $this->randomSeasonCount));
		$this->assertEquals($desired, $this->parser->parse('pvp ' . $this->randomSeasonDesignator . " @" . $this->randomSeasonCount));
	}

	public function testInvalidClearanceLevelLow(): void
	{
		$this->expectException(PhpPegJs\SyntaxError::class);
		$this->parser->parse("pvp cl3 111");
	}
		public function testInvalidClearanceLevelHigh(): void
	{
		$this->expectException(PhpPegJs\SyntaxError::class);
		$this->parser->parse("pvp cl13 111");
	}

	public function testInvalidCountLow(): void
	{
		$this->expectException(PhpPegJs\SyntaxError::class);
		$this->parser->parse('pvp ' . $this->randomSeasonDesignator . " " . 0);
	}

	public function testInvalidCountHigh(): void
	{
		$this->expectException(PhpPegJs\SyntaxError::class);
		$this->parser->parse('pvp ' . $this->randomSeasonDesignator . " " . 19999);
	}

	public function testFlip(): void
	{
		$desired = ["pvp" => [
				"slice"=> $this->randomSeasonDesignator
				,"count" => 'flip'
				,"lag" => NULL
				]
			];
		$this->assertEquals($desired, $this->parser->parse('pvp ' . $this->randomSeasonDesignator . " flip"));
		$this->assertEquals($desired, $this->parser->parse('pvp ' . $this->randomSeasonDesignator . " flipped"));
	}
}
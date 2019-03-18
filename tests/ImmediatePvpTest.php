<?php
declare(strict_types=1);
require_once(__DIR__ . '/ParserTestFixture.php');

final class ImmediatePvpTest extends ParserTest
{

	protected function setUp(): void
	{
		parent::setUp();
		$this->randompSeasonCount = rand(1,4999); //up to 4999 for pvp seasons
		$this->randomSeasonDesignator = 'cl' . rand(6,9);
	}

	public function testCount(): void
	{
		$desired = ["pvp" => [
				"slice"=> $this->randomSeasonDesignator
				,"count" => $this->randompSeasonCount
				,"lag" => NULL
				]
			];
		$this->assertEquals(
			$this->parser->parse('pvp ' . $this->randomSeasonDesignator . " " . $this->randompSeasonCount), 
			$desired
		);
		$this->assertEquals(
			$this->parser->parse('pvp ' . $this->randomSeasonDesignator . "@" . $this->randompSeasonCount), 
			$desired
		);
		$this->assertEquals(
			$this->parser->parse('pvp ' . $this->randomSeasonDesignator . "@ " . $this->randompSeasonCount), 
			$desired
		);
		$this->assertEquals(
			$this->parser->parse('pvp ' . $this->randomSeasonDesignator . " @ " . $this->randompSeasonCount), 
			$desired
		);
		$this->assertEquals(
			$this->parser->parse('pvp ' . $this->randomSeasonDesignator . " @" . $this->randompSeasonCount), 
			$desired
		);
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
		$this->assertEquals(
			$this->parser->parse('pvp ' . $this->randomSeasonDesignator . " flip"), 
			$desired
		);
		$this->assertEquals(
			$this->parser->parse('pvp ' . $this->randomSeasonDesignator . " flipped"), 
			$desired
		);
	}
}
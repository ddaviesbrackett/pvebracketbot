<?php
declare(strict_types=1);
require_once(__DIR__ . '/parser.php');

use PHPUnit\Framework\TestCase;

final class ParserTest extends TestCase
{
	protected $parser;

	protected function setUp(): void
	{
		$this->randomPVECount = rand(1,999); //yes, just plan rand, don't need crypto here; counts go up to 999 for pve
		$this->randompPvpSeasonCount = rand(1,4999); //up to 4999 for pvp seasons
		$this->randomSlice = rand(1,5) . '.' . rand(6,9);	 //slices can be between 1.6 and 5.9
		$this->randomPvpSeasonDesignator = 'cl' . rand(6,9);
		$this->parser = new PhpPegJs\Parser;
	}

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

	//_____________________________________________

	public function testImmediateCount(): void
	{
		$this->assertEquals(
			$this->parser->parse($this->randomSlice . " " . $this->randomPVECount), 
			["c" => [
				"slice"=> $this->randomSlice
				,"count" => $this->randomPVECount
				,"lag" => NULL
				]
			]
		);
	}

	public function testImmediateCountInvalidClearanceLevelLow(): void
	{
		$this->expectException(PhpPegJs\SyntaxError::class);
		$this->parser->parse("1.3 111");
	}
		public function testImmediateCountInvalidClearanceLevelHigh(): void
	{
		$this->expectException(PhpPegJs\SyntaxError::class);
		$this->parser->parse("1.13 111");
	}

	public function testImmediateCountInvalidSliceSingleDigit(): void
	{
		$this->expectException(PhpPegJs\SyntaxError::class);
		$this->parser->parse("6.9 111");

	}

	public function testImmediateCountInvalidSliceDoubleDigit(): void
	{
		$this->expectException(PhpPegJs\SyntaxError::class);
		$this->parser->parse("12.9 111");

	}

	public function testImmediateCountInvalidCountLow(): void
	{
		$this->expectException(PhpPegJs\SyntaxError::class);
		$this->parser->parse($this->randomSlice . " " . 0);
	}

	public function testImmediateCountInvalidCountHigh(): void
	{
		$this->expectException(PhpPegJs\SyntaxError::class);
		$this->parser->parse($this->randomSlice . " " . 9999);
	}

	public function testImmediateFlip(): void
	{
		$desired = ["c" => [
				"slice"=> $this->randomSlice
				,"count" => 'flip'
				,"lag" => NULL
				]
			];
		$this->assertEquals(
			$this->parser->parse($this->randomSlice . " flip"), 
			$desired
		);
		$this->assertEquals(
			$this->parser->parse($this->randomSlice . " flipped"), 
			$desired
		);
	}


	//_____________________________________________

	public function testImmediatePvpCount(): void
	{
		$this->assertEquals(
			$this->parser->parse('pvp ' . $this->randomPvpSeasonDesignator . " " . $this->randompPvpSeasonCount), 
			["pvp" => [
				"slice"=> $this->randomPvpSeasonDesignator
				,"count" => $this->randompPvpSeasonCount
				,"lag" => NULL
				]
			]
		);
	}

	public function testImmediatePvpCountInvalidClearanceLevelLow(): void
	{
		$this->expectException(PhpPegJs\SyntaxError::class);
		$this->parser->parse("pvp cl3 111");
	}
		public function testImmediatePvpCountInvalidClearanceLevelHigh(): void
	{
		$this->expectException(PhpPegJs\SyntaxError::class);
		$this->parser->parse("pvp cl13 111");
	}

	public function testImmediatePvpCountInvalidCountLow(): void
	{
		$this->expectException(PhpPegJs\SyntaxError::class);
		$this->parser->parse('pvp ' . $this->randomPvpSeasonDesignator . " " . 0);
	}

	public function testImmediatePvpCountInvalidCountHigh(): void
	{
		$this->expectException(PhpPegJs\SyntaxError::class);
		$this->parser->parse('pvp ' . $this->randomPvpSeasonDesignator . " " . 19999);
	}

	public function testImmediatePvpFlip(): void
	{
		$desired = ["pvp" => [
				"slice"=> $this->randomPvpSeasonDesignator
				,"count" => 'flip'
				,"lag" => NULL
				]
			];
		$this->assertEquals(
			$this->parser->parse('pvp ' . $this->randomPvpSeasonDesignator . " flip"), 
			$desired
		);
		$this->assertEquals(
			$this->parser->parse('pvp ' . $this->randomPvpSeasonDesignator . " flipped"), 
			$desired
		);
	}
}
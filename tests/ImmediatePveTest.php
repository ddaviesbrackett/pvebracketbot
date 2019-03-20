<?php
declare(strict_types=1);
require_once(__DIR__ . '/ParserTestFixture.php');

/*
Tests PVE counts with no report lag.
*/
final class ImmediatePveTest extends ParserTest
{

	protected function setUp(): void
	{
		parent::setUp();
		$this->randomSlice = rand(1,5) . '.' . rand(6,9);	 //slices can be between 1.6 and 5.9
		$this->randomCount = rand(1,999); //yes, just plan rand, don't need crypto here; counts go up to 999 for pve
	}

	public function testSuccess(): void
	{
		$desired = ["c" => [
				"slice"=> $this->randomSlice
				,"count" => $this->randomCount
				,"lag" => NULL
				]
			];
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " " . $this->randomCount));
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . "@" . $this->randomCount));
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . "@ " . $this->randomCount));
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " @ " . $this->randomCount));
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " at " . $this->randomCount));
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " @" . $this->randomCount));
	}

	public function testInvalidClearanceLevelLow(): void
	{
		$this->expectException(PhpPegJs\SyntaxError::class);
		$this->parser->parse("1.3 111");
	}
		public function testInvalidClearanceLevelHigh(): void
	{
		$this->expectException(PhpPegJs\SyntaxError::class);
		$this->parser->parse("1.13 111");
	}

	public function testInvalidSliceSingleDigit(): void
	{
		$this->expectException(PhpPegJs\SyntaxError::class);
		$this->parser->parse("6.9 111");

	}

	public function testInvalidSliceDoubleDigit(): void
	{
		$this->expectException(PhpPegJs\SyntaxError::class);
		$this->parser->parse("12.9 111");

	}

	public function testInvalidCountLow(): void
	{
		$this->expectException(PhpPegJs\SyntaxError::class);
		$this->parser->parse($this->randomSlice . " " . 0);
	}

	public function testInvalidCountHigh(): void
	{
		$this->expectException(PhpPegJs\SyntaxError::class);
		$this->parser->parse($this->randomSlice . " " . 9999);
	}

	public function testFlip(): void
	{
		$desired = ["c" => [
				"slice"=> $this->randomSlice
				,"count" => 'flip'
				,"lag" => NULL
				]
			];
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " flip"));
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " flipped"));
	}
}
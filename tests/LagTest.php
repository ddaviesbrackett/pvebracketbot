<?php
declare(strict_types=1);
require_once(__DIR__ . '/ParserTestFixture.php');

final class LagTest extends ParserTest
{

	protected function setUp(): void
	{
		parent::setUp();
		$this->randomSlice = rand(1,5) . '.' . rand(6,9);	 //slices can be between 1.6 and 5.9
		$this->randomCount = rand(1,999); //yes, just plan rand, don't need crypto here; counts go up to 999 for pve
	}

	public function testMinutesAlone(): void
	{
		$desired = ["c" => [
				"slice"=> $this->randomSlice
				,"count" => $this->randomCount
				,"lag" => "13m"
				]
			];
		$this->assertEquals(
			$this->parser->parse($this->randomSlice . " " . $this->randomCount . " 13m"), 
			$desired
		);
		$this->assertEquals(
			$this->parser->parse($this->randomSlice . " " . $this->randomCount . " 13min"), 
			$desired
		);
		$this->assertEquals(
			$this->parser->parse($this->randomSlice . " " . $this->randomCount . " 13mins"), 
			$desired
		);
		$this->assertEquals(
			$this->parser->parse($this->randomSlice . " " . $this->randomCount . " 13m ago"), 
			$desired
		);
		$this->assertEquals(
			$this->parser->parse($this->randomSlice . " " . $this->randomCount . " 13min ago"), 
			$desired
		);
		$this->assertEquals(
			$this->parser->parse($this->randomSlice . " " . $this->randomCount . " 13mins ago"), 
			$desired
		);
		$this->assertEquals(
			$this->parser->parse($this->randomSlice . " " . $this->randomCount . " 13 m"), 
			$desired
		);
		$this->assertEquals(
			$this->parser->parse($this->randomSlice . " " . $this->randomCount . " 13 min"), 
			$desired
		);
		$this->assertEquals(
			$this->parser->parse($this->randomSlice . " " . $this->randomCount . " 13 mins"), 
			$desired
		);
		$this->assertEquals(
			$this->parser->parse($this->randomSlice . " " . $this->randomCount . " 13 m ago"), 
			$desired
		);
		$this->assertEquals(
			$this->parser->parse($this->randomSlice . " " . $this->randomCount . " 13 min ago"), 
			$desired
		);
		$this->assertEquals(
			$this->parser->parse($this->randomSlice . " " . $this->randomCount . " 13 mins ago"), 
			$desired
		);
	}

	public function testHoursAlone(): void
	{
		$desired = ["c" => [
				"slice"=> $this->randomSlice
				,"count" => $this->randomCount
				,"lag" => "2h"
				]
			];
		$this->assertEquals(
			$this->parser->parse($this->randomSlice . " " . $this->randomCount . " 2h"), 
			$desired
		);
		$this->assertEquals(
			$this->parser->parse($this->randomSlice . " " . $this->randomCount . " 2 h"), 
			$desired
		);
		$this->assertEquals(
			$this->parser->parse($this->randomSlice . " " . $this->randomCount . " 2h ago"), 
			$desired
		);
		$this->assertEquals(
			$this->parser->parse($this->randomSlice . " " . $this->randomCount . " 2 h ago"), 
			$desired
		);
	}

	public function testHoursAndMinutes(): void
	{
		$desired = ["c" => [
				"slice"=> $this->randomSlice
				,"count" => $this->randomCount
				,"lag" => "2h10m"
				]
			];

		$this->assertEquals(
			$this->parser->parse($this->randomSlice . " " . $this->randomCount . " 2h10m"), 
			$desired
		);
	}
}
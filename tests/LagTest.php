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
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " " . $this->randomCount . " 13m"));
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " " . $this->randomCount . " 13min"));
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " " . $this->randomCount . " 13mins"));
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " " . $this->randomCount . " 13m ago"));
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " " . $this->randomCount . " 13min ago"));
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " " . $this->randomCount . " 13mins ago"));
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " " . $this->randomCount . " 13 m"));
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " " . $this->randomCount . " 13 min"));
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " " . $this->randomCount . " 13 mins"));
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " " . $this->randomCount . " 13 m ago"));
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " " . $this->randomCount . " 13 min ago"));
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " " . $this->randomCount . " 13 mins ago"));
	}

	public function testHoursAlone(): void
	{
		$desired = ["c" => [
				"slice"=> $this->randomSlice
				,"count" => $this->randomCount
				,"lag" => "2h"
				]
			];
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " " . $this->randomCount . " 2h"));
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " " . $this->randomCount . " 2 h"));
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " " . $this->randomCount . " 2h ago"));
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " " . $this->randomCount . " 2 h ago"));
	}

	public function testHoursAndMinutes(): void
	{
		$desired = ["c" => [
				"slice"=> $this->randomSlice
				,"count" => $this->randomCount
				,"lag" => "12h10m"
				]
			];
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " " . $this->randomCount . " 12h10m"));
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " " . $this->randomCount . " 12 h10m"));
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " " . $this->randomCount . " 12h10 m"));
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " " . $this->randomCount . " 12 h 10 m"));
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " " . $this->randomCount . " 12h 10m"));
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " " . $this->randomCount . " 12 h 10m"));
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " " . $this->randomCount . " 12h 10 m"));
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " " . $this->randomCount . " 12 h 10 m"));
	}
}
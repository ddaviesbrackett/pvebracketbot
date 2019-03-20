<?php
declare(strict_types=1);
require_once(__DIR__ . '/ParserTestFixture.php');

/*
These tests are just testing the ReportLag aspect of the grammar. Since ReportLag is not a start rule,
the rule needs to start with a reasonable, already-tested aspect: I chose a simple slice count.
*/
final class LagTest extends ParserTest
{

	protected function setUp(): void
	{
		parent::setUp();
		$this->randomSlice = rand(1,5) . '.' . rand(6,9);	 //slices can be between 1.6 and 5.9
		$this->randomCount = rand(1,999); //yes, just plan rand, don't need crypto here; counts go up to 999 for pve
	}

	private function doTest($desired, $suffix)
	{
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " " . $this->randomCount . $suffix));
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " " . $this->randomCount . $suffix . " this is extra stuff at the end"));
	}

	public function testMinutesAlone(): void
	{
		$desired = ["c" => [
				"slice"=> $this->randomSlice
				,"count" => $this->randomCount
				,"lag" => "13m"
				]
			];
		$this->doTest($desired, " 13m");
		$this->doTest($desired, " 13min");
		$this->doTest($desired, " 13mins");
		$this->doTest($desired, " 13m ago");
		$this->doTest($desired, " 13min ago");
		$this->doTest($desired, " 13mins ago");
		$this->doTest($desired, " 13 m");
		$this->doTest($desired, " 13 min");
		$this->doTest($desired, " 13 mins");
		$this->doTest($desired, " 13 m ago");
		$this->doTest($desired, " 13 min ago");
		$this->doTest($desired, " 13 mins ago");
	}

	public function testHoursAlone(): void
	{
		$desired = ["c" => [
				"slice"=> $this->randomSlice
				,"count" => $this->randomCount
				,"lag" => "2h"
				]
			];
		$this->doTest($desired, " 2h");
		$this->doTest($desired, " 2 h");
		$this->doTest($desired, " 2h ago");
		$this->doTest($desired, " 2 h ago");
	}

	public function testHoursAndMinutes(): void
	{
		$desired = ["c" => [
				"slice"=> $this->randomSlice
				,"count" => $this->randomCount
				,"lag" => "12h10m"
				]
			];
		$this->doTest($desired, " 12h10m");
		$this->doTest($desired, " 12 h10m");
		$this->doTest($desired, " 12h10 m");
		$this->doTest($desired, " 12 h 10 m");
		$this->doTest($desired, " 12h 10m");
		$this->doTest($desired, " 12 h 10m");
		$this->doTest($desired, " 12h 10 m");
		$this->doTest($desired, " 12 h 10 m");
	}

	public function testHoursAndMinutesWithoutFinalM(): void
	{
		$desired = ["c" => [
				"slice"=> $this->randomSlice
				,"count" => $this->randomCount
				,"lag" => "12h10"
				]
			];
		$this->doTest($desired, " 12h10");
		$this->doTest($desired, " 12 h10");
		$this->doTest($desired, " 12h 10");
	}
}
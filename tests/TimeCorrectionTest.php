<?php
declare(strict_types=1);
require_once(__DIR__ . '/ParserTestFixture.php');

/*
Tests 'last update' time corrections.
*/
final class TimeCorrectionTest extends ParserTest
{

	protected function setUp(): void
	{
		parent::setUp();
		$this->randomSlice = rand(1,5) . '.' . rand(6,9);	 //slices can be between 1.6 and 5.9
	}

	public function testUpdateTimeCorrection()
	{
		$desired = ["c" => [
				"slice"=> $this->randomSlice
				,"count" => 'lag-update'
				,"lag" => "13m"
				]
			];
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " last 13m"));
	}

	public function testFlipTimeCorrection()
	{
		$desired = ["c" => [
				"slice"=> $this->randomSlice
				,"count" => 'flip-update'
				,"lag" => "13m"
				]
			];
		$this->assertEquals($desired, $this->parser->parse($this->randomSlice . " last flip 13m"));
	}
}
<?php

namespace Tests\Wikibase\JsonDumpReader\Reader;

use Wikibase\JsonDumpReader\Reader\GzDumpReader;

/**
 * @covers Wikibase\JsonDumpReader\Reader\GzDumpReader
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class GzDumpReaderTest extends \PHPUnit_Framework_TestCase {

	public function testGivenInvalidGzPath_exceptionIsThrown() {
		$this->setExpectedException( 'RuntimeException' );
		$reader = new GzDumpReader( __DIR__ . '/../../data/does-not-exist.json.gz' );
		$reader->nextJsonLine();
	}

	private function assertFindsEntity( GzDumpReader $reader, $expectedId ) {
		$line = $reader->nextJsonLine();
		$this->assertJson( $line );
		$this->assertContains( $expectedId, $line );
	}

	public function testGivenGzFileWithNoEntities_nullIsReturned() {
		$reader = new GzDumpReader( ( new \JsonDumpData() )->getEmptyGzDumpPath() );
		$this->assertNull( $reader->nextJsonLine() );
	}

	public function testGivenGzFileWithFiveEntities_fiveEntityAreFound() {
		$reader = new GzDumpReader( ( new \JsonDumpData() )->getFiveEntitiesGzDumpPath() );

		$this->assertFindsEntity( $reader, 'Q1' );
		$this->assertFindsEntity( $reader, 'Q8' );
		$this->assertFindsEntity( $reader, 'P16' );
		$this->assertFindsEntity( $reader, 'P19' );
		$this->assertFindsEntity( $reader, 'P22' );
		$this->assertNull( $reader->nextJsonLine() );
	}

	public function testRewind() {
		$reader = new GzDumpReader( ( new \JsonDumpData() )->getFiveEntitiesGzDumpPath() );

		$this->assertFindsEntity( $reader, 'Q1' );
		$this->assertFindsEntity( $reader, 'Q8' );
		$reader->rewind();
		$this->assertFindsEntity( $reader, 'Q1' );
	}

}
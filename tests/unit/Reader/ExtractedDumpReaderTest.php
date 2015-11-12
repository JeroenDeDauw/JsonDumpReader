<?php

namespace Tests\Wikibase\JsonDumpReader\Reader;

use Wikibase\JsonDumpReader\Reader\ExtractedDumpReader;

/**
 * @covers Wikibase\JsonDumpReader\Reader\ExtractedDumpReader
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ExtractedDumpReaderTest extends \PHPUnit_Framework_TestCase {

	private function assertFindsAnotherJsonLine( ExtractedDumpReader $reader ) {
		$this->assertJson( $reader->nextJsonLine() );
	}

	private function assertFindsEntity( ExtractedDumpReader $reader, $expectedId ) {
		$line = $reader->nextJsonLine();
		$this->assertJson( $line );
		$this->assertContains( $expectedId, $line );
	}

	public function testGivenFileWithNoEntities_nullIsReturned() {
		$reader = new ExtractedDumpReader( ( new \JsonDumpData() )->getEmptyDumpPath() );

		$this->assertNull( $reader->nextJsonLine() );
	}

	public function testGivenFileWithOneEntity_oneEntityIsFound() {
		$reader = new ExtractedDumpReader( ( new \JsonDumpData() )->getOneItemDumpPath() );

		$this->assertFindsAnotherJsonLine( $reader );
		$this->assertNull( $reader->nextJsonLine() );
	}

	public function testGivenFileWithFiveEntities_fiveEntityAreFound() {
		$reader = new ExtractedDumpReader( ( new \JsonDumpData() )->getFiveEntitiesDumpPath() );

		$this->assertFindsAnotherJsonLine( $reader );
		$this->assertFindsAnotherJsonLine( $reader );
		$this->assertFindsAnotherJsonLine( $reader );
		$this->assertFindsAnotherJsonLine( $reader );
		$this->assertFindsAnotherJsonLine( $reader );
		$this->assertNull( $reader->nextJsonLine() );
	}

	public function testRewind() {
		$reader = new ExtractedDumpReader( ( new \JsonDumpData() )->getOneItemDumpPath() );

		$this->assertFindsAnotherJsonLine( $reader );
		$reader->rewind();
		$this->assertFindsAnotherJsonLine( $reader );
		$this->assertNull( $reader->nextJsonLine() );
	}

	public function testResumeFromPosition() {
		$reader = new ExtractedDumpReader( ( new \JsonDumpData() )->getFiveEntitiesDumpPath() );

		$this->assertFindsEntity( $reader, 'Q1' );
		$this->assertFindsEntity( $reader, 'Q8' );

		$position = $reader->getPosition();
		unset( $reader );

		$newReader = new ExtractedDumpReader( ( new \JsonDumpData() )->getFiveEntitiesDumpPath() );
		$newReader->seekToPosition( $position );

		$this->assertFindsEntity( $newReader, 'P16' );
	}

	public function testFindsAllEntitiesInBigFile() {
		$reader = new ExtractedDumpReader( ( new \JsonDumpData() )->getOneThousandEntitiesDumpPath() );

		foreach ( range( 0, 999 ) as $i ) {
			$this->assertFindsAnotherJsonLine( $reader );
		}

		$this->assertNull( $reader->nextJsonLine() );
	}

}
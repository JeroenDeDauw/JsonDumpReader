<?php

namespace Tests\Wikibase\JsonDumpReader;

use Wikibase\JsonDumpReader\JsonDumpReader;

/**
 * @covers Wikibase\JsonDumpReader\JsonDumpReader
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class JsonDumpReaderTest extends \PHPUnit_Framework_TestCase {

	private function assertFindsAnotherJsonLine( JsonDumpReader $reader ) {
		$this->assertJson( $reader->nextJsonLine() );
	}

	private function assertFindsEntity( JsonDumpReader $reader, $expectedId ) {
		$line = $reader->nextJsonLine();
		$this->assertJson( $line );
		$this->assertContains( $expectedId, $line );
	}

	public function testGivenFileWithNoEntities_nullIsReturned() {
		$reader = new JsonDumpReader( ( new \JsonDumpData() )->getEmptyDumpPath() );

		$this->assertNull( $reader->nextJsonLine() );
	}

	public function testGivenFileWithOneEntity_oneEntityIsFound() {
		$reader = new JsonDumpReader( ( new \JsonDumpData() )->getOneItemDumpPath() );

		$this->assertFindsAnotherJsonLine( $reader );
		$this->assertNull( $reader->nextJsonLine() );
	}

	public function testGivenFileWithFiveEntites_fiveEntityAreFound() {
		$reader = new JsonDumpReader( ( new \JsonDumpData() )->getFiveEntitiesDumpPath() );

		$this->assertFindsAnotherJsonLine( $reader );
		$this->assertFindsAnotherJsonLine( $reader );
		$this->assertFindsAnotherJsonLine( $reader );
		$this->assertFindsAnotherJsonLine( $reader );
		$this->assertFindsAnotherJsonLine( $reader );
		$this->assertNull( $reader->nextJsonLine() );
	}

	public function testRewind() {
		$reader = new JsonDumpReader( ( new \JsonDumpData() )->getOneItemDumpPath() );

		$this->assertFindsAnotherJsonLine( $reader );
		$reader->rewind();
		$this->assertFindsAnotherJsonLine( $reader );
		$this->assertNull( $reader->nextJsonLine() );
	}

	public function testResumeFromPosition() {
		$reader = new JsonDumpReader( ( new \JsonDumpData() )->getFiveEntitiesDumpPath() );

		$this->assertFindsEntity( $reader, 'Q1' );
		$this->assertFindsEntity( $reader, 'Q8' );

		$position = $reader->getPosition();
		unset( $reader );

		$newReader = new JsonDumpReader( ( new \JsonDumpData() )->getFiveEntitiesDumpPath() );
		$newReader->seekToPosition( $position );

		$this->assertFindsEntity( $newReader, 'P16' );
	}

	public function testFindsAllEntitiesInBigFile() {
		$reader = new JsonDumpReader( ( new \JsonDumpData() )->getOneThousandEntitiesDumpPath() );

		foreach ( range( 0, 20 ) as $i ) {
			$this->assertFindsAnotherJsonLine( $reader );
		}

		//$this->assertNull( $reader->nextEntity() );
	}

}
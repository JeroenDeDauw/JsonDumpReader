<?php

declare( strict_types = 1 );

namespace Wikibase\JsonDumpReader\Tests\Unit\Reader;

use PHPUnit\Framework\TestCase;
use Wikibase\JsonDumpReader\Reader\Bz2DumpReader;

/**
 * @covers \Wikibase\JsonDumpReader\Reader\Bz2DumpReader
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class Bz2DumpReaderTest extends TestCase {

	public function setUp() {
		if ( !function_exists( 'bzopen' ) ) {
			self::markTestSkipped( 'bz2 is not installed' );
		}
	}

	private function assertFindsEntity( Bz2DumpReader $reader, $expectedId ) {
		$line = $reader->nextJsonLine();
		$this->assertJson( $line );
		$this->assertContains( $expectedId, $line );
	}

	public function testGivenInvalidPath_exceptionIsThrown() {
		$this->expectException( 'RuntimeException' );
		$reader = new Bz2DumpReader( __DIR__ . '/../../data/does-not-exist.json.bz2' );
		$reader->nextJsonLine();
	}

	public function testGivenFileWithNoEntities_nullIsReturned() {
		$reader = new Bz2DumpReader( ( new \JsonDumpData() )->getEmptyBz2DumpPath() );

		$this->assertNull( $reader->nextJsonLine() );
	}

	public function testGivenFileWithFiveEntities_fiveEntityAreFound() {
		$reader = new Bz2DumpReader( ( new \JsonDumpData() )->getFiveEntitiesBz2DumpPath() );

		$this->assertFindsEntity( $reader, 'Q1' );
		$this->assertFindsEntity( $reader, 'Q8' );
		$this->assertFindsEntity( $reader, 'P16' );
		$this->assertFindsEntity( $reader, 'P19' );
		$this->assertFindsEntity( $reader, 'P22' );
		$this->assertNull( $reader->nextJsonLine() );
	}

	public function testRewind() {
		$reader = new Bz2DumpReader( ( new \JsonDumpData() )->getFiveEntitiesBz2DumpPath() );

		$this->assertFindsEntity( $reader, 'Q1' );
		$this->assertFindsEntity( $reader, 'Q8' );
		$reader->rewind();
		$this->assertFindsEntity( $reader, 'Q1' );
	}

}
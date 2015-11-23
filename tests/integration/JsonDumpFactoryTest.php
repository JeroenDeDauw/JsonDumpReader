<?php

namespace Tests\Wikibase\JsonDumpReader;

use JsonDumpData;
use Wikibase\JsonDumpReader\JsonDumpFactory;

/**
 * @covers Wikibase\JsonDumpReader\JsonDumpFactory
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class JsonDumpFactoryTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var JsonDumpFactory
	 */
	private $factory;

	/**
	 * @var JsonDumpData
	 */
	private $dumpData;

	public function setUp() {
		$this->factory = new JsonDumpFactory();
		$this->dumpData = new JsonDumpData();
	}

	public function testGzDumpReaderCanReadGzFile() {
		$reader = $this->factory->newGzDumpReader( $this->dumpData->getFiveEntitiesGzDumpPath() );

		$this->assertJson( $reader->nextJsonLine() );
		$this->assertJson( $reader->nextJsonLine() );
		$this->assertJson( $reader->nextJsonLine() );
		$this->assertJson( $reader->nextJsonLine() );
		$this->assertJson( $reader->nextJsonLine() );
		$this->assertNull( $reader->nextJsonLine() );
	}

	public function testBz2DumpReaderCanReadBz2File() {
		$reader = $this->factory->newBz2DumpReader( $this->dumpData->getFiveEntitiesBz2DumpPath() );

		$this->assertJson( $reader->nextJsonLine() );
		$this->assertJson( $reader->nextJsonLine() );
		$this->assertJson( $reader->nextJsonLine() );
		$this->assertJson( $reader->nextJsonLine() );
		$this->assertJson( $reader->nextJsonLine() );
		$this->assertNull( $reader->nextJsonLine() );
	}

	public function testExtractedDumpReaderCanReadJsonFile() {
		$reader = $this->factory->newExtractedDumpReader( $this->dumpData->getFiveEntitiesDumpPath() );

		$this->assertJson( $reader->nextJsonLine() );
		$this->assertJson( $reader->nextJsonLine() );
		$this->assertJson( $reader->nextJsonLine() );
		$this->assertJson( $reader->nextJsonLine() );
		$this->assertJson( $reader->nextJsonLine() );
		$this->assertNull( $reader->nextJsonLine() );
	}

	public function testStringDumpIteratorWithGzReader() {
		$iterator = $this->factory->newStringDumpIterator(
			$this->factory->newGzDumpReader( $this->dumpData->getFiveEntitiesGzDumpPath() )
		);

		$this->assertContainsOnly( 'string', $iterator );
		$this->assertCount( 5, $iterator );
	}

	public function testObjectDumpIteratorWithBz2Reader() {
		$iterator = $this->factory->newObjectDumpIterator(
			$this->factory->newBz2DumpReader( $this->dumpData->getFiveEntitiesBz2DumpPath() )
		);

		foreach ( $iterator as $json ) {
			$this->assertInternalType( 'array', $json );
			$this->assertArrayHasKey( 'id', $json );
		}
		$this->assertCount( 5, $iterator );
	}

	public function testGzReaderInitialPosition() {
		$reader = $this->factory->newGzDumpReader( $this->dumpData->getFiveEntitiesGzDumpPath() );

		$reader->nextJsonLine();
		$reader->nextJsonLine();
		$reader->nextJsonLine();

		$newReader = $this->factory->newGzDumpReader(
			$this->dumpData->getFiveEntitiesGzDumpPath(),
			$reader->getPosition()
		);

		$this->assertJson( $newReader->nextJsonLine() );
		$this->assertJson( $newReader->nextJsonLine() );
		$this->assertNull( $newReader->nextJsonLine() );
	}

}
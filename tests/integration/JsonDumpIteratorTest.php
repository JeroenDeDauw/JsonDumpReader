<?php

namespace Tests\Wikibase\JsonDumpReader;

use DataValues\Deserializers\DataValueDeserializer;
use Iterator;
use Wikibase\DataModel\DeserializerFactory;
use Wikibase\DataModel\Entity\BasicEntityIdParser;
use Wikibase\JsonDumpReader\JsonDumpIterator;
use Wikibase\JsonDumpReader\ExtractedDumpReader;

/**
 * @covers Wikibase\JsonDumpReader\JsonDumpIterator
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class JsonDumpIteratorTest extends \PHPUnit_Framework_TestCase {

	private function newIteratorForFile( $filePath ) {
		return new JsonDumpIterator(
			new ExtractedDumpReader( $filePath ),
			$this->newCurrentEntityDeserializer()
		);
	}

	private function newCurrentEntityDeserializer() {
		$factory = new DeserializerFactory(
			$this->newDataValueDeserializer(),
			new BasicEntityIdParser()
		);

		return $factory->newEntityDeserializer();
	}

	private function newDataValueDeserializer() {
		$dataValueClasses = [
			'boolean' => 'DataValues\BooleanValue',
			'number' => 'DataValues\NumberValue',
			'string' => 'DataValues\StringValue',
			'unknown' => 'DataValues\UnknownValue',
			'globecoordinate' => 'DataValues\Geo\Values\GlobeCoordinateValue',
			'monolingualtext' => 'DataValues\MonolingualTextValue',
			'multilingualtext' => 'DataValues\MultilingualTextValue',
			'quantity' => 'DataValues\QuantityValue',
			'time' => 'DataValues\TimeValue',
			'wikibase-entityid' => 'Wikibase\DataModel\Entity\EntityIdValue',
		];

		return new DataValueDeserializer( $dataValueClasses );
	}

	private function assertFindsEntities( array $expectedIds, Iterator $dumpIterator ) {
		$actualIds = [];

		foreach ( $dumpIterator as $entity ) {
			$actualIds[] = $entity->getId()->getSerialization();
		}

		$this->assertEquals( $expectedIds, $actualIds );
	}

	public function testGivenFileWithNoEntities_noEntitiesAreReturned() {
		$iterator = $this->newIteratorForFile( ( new \JsonDumpData() )->getEmptyDumpPath() );

		$this->assertFindsEntities( [], $iterator );
	}

	public function testGivenFileWithOneEntity_oneEntityIsFound() {
		$iterator = $this->newIteratorForFile( ( new \JsonDumpData() )->getOneItemDumpPath() );

		$this->assertFindsEntities( [ 'Q1' ], $iterator );
	}

	public function testGivenFileWithFiveEntites_fiveEntityAreFound() {
		$iterator = $this->newIteratorForFile( ( new \JsonDumpData() )->getFiveEntitiesDumpPath() );

		$this->assertFindsEntities( [ 'Q1', 'Q8', 'P16', 'P19', 'P22' ], $iterator );
	}

	public function testGivenFileWithInvalidEntity_noEntityIsFound() {
		$iterator = $this->newIteratorForFile( __DIR__ . '/../data/invalid-item.json' );
		$this->assertFindsEntities( [], $iterator );
	}

	public function testGivenFileWithInvalidEntities_validEntitiesAreFound() {
		$iterator = $this->newIteratorForFile( __DIR__ . '/../data/3valid-2invalid.json' );
		$this->assertFindsEntities( [ 'Q1', 'P16', 'P22' ], $iterator );
	}

	public function testCanDoMultipleIterations() {
		$iterator = $this->newIteratorForFile( ( new \JsonDumpData() )->getFiveEntitiesDumpPath() );

		$this->assertFindsEntities( [ 'Q1', 'Q8', 'P16', 'P19', 'P22' ], $iterator );
		$this->assertFindsEntities( [ 'Q1', 'Q8', 'P16', 'P19', 'P22' ], $iterator );
	}

	public function testInitialPosition() {
		$reader = new ExtractedDumpReader( ( new \JsonDumpData() )->getFiveEntitiesDumpPath() );

		$iterator = new JsonDumpIterator(
			$reader,
			$this->newCurrentEntityDeserializer()
		);

		$iterator->next();
		$iterator->next();

		$newIterator = new JsonDumpIterator(
			new ExtractedDumpReader( ( new \JsonDumpData() )->getFiveEntitiesDumpPath(), $reader->getPosition() ),
			$this->newCurrentEntityDeserializer()
		);

		$this->assertFindsEntities( [ 'P16', 'P19', 'P22' ], $newIterator );
	}

	public function testGivenFileWithInvalidEntities_errorsAreReported() {
		$iterator = $this->newIteratorForFile( __DIR__ . '/../data/3valid-2invalid.json' );
		$errors = [];

		$iterator->onError( function( $errorMessage ) use ( &$errors ) {
			$errors[] = $errorMessage;
		} );

		$iterator->rewind();
		while ( $iterator->valid() ) {
			$iterator->next();
		}

		$this->assertContainsOnly( 'string', $errors );
		$this->assertCount( 2, $errors );
	}

	public function testGivenNonJsonFile_errorsIsReported() {
		$iterator = $this->newIteratorForFile( __DIR__ . '/../data/invalid-json.json' );

		$errors = [];

		$iterator->onError( function( $errorMessage ) use ( &$errors ) {
			$errors[] = $errorMessage;
		} );

		$this->assertNull( $iterator->next() );

		$this->assertContainsOnly( 'string', $errors );
		$this->assertCount( 1, $errors );
	}


}
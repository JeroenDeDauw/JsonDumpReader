<?php

namespace Tests\Wikibase\JsonDumpReader;

use DataValues\Deserializers\DataValueDeserializer;
use Iterator;
use Wikibase\DataModel\DeserializerFactory;
use Wikibase\DataModel\Entity\BasicEntityIdParser;
use Wikibase\JsonDumpReader\Iterator\EntityDumpIterator;
use Wikibase\JsonDumpReader\JsonDumpFactory;
use Wikibase\JsonDumpReader\Reader\ExtractedDumpReader;

/**
 * @covers Wikibase\JsonDumpReader\Iterator\EntityDumpIterator
 * @covers Wikibase\JsonDumpReader\JsonDumpFactory
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EntityDumpIteratorTest extends \PHPUnit_Framework_TestCase {

	private function newIteratorForFile( $filePath, callable $onError = null ) {
		return ( new JsonDumpFactory() )->newEntityDumpIterator(
			new ExtractedDumpReader( $filePath ),
			$this->newCurrentEntityDeserializer(),
			$onError
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

	private function assertFindsEntities( array $expectedIds, Iterator $dumpIterator, $message = '' ) {
		$actualIds = [];

		foreach ( $dumpIterator as $entity ) {
			$actualIds[] = $entity->getId()->getSerialization();
		}

		$this->assertEquals( $expectedIds, $actualIds, $message );
	}

	public function testGivenFileWithNoEntities_noEntitiesAreReturned() {
		$iterator = $this->newIteratorForFile( ( new \JsonDumpData() )->getEmptyDumpPath() );

		$this->assertFindsEntities( [], $iterator );
	}

	public function testGivenFileWithOneEntity_oneEntityIsFound() {
		$iterator = $this->newIteratorForFile( ( new \JsonDumpData() )->getOneItemDumpPath() );

		$this->assertFindsEntities( [ 'Q1' ], $iterator );
	}

	public function testGivenFileWithFiveEntities_fiveEntityAreFound() {
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

		$this->assertFindsEntities( [ 'Q1', 'Q8', 'P16', 'P19', 'P22' ], $iterator, 'first iteration' );
		$this->assertFindsEntities( [ 'Q1', 'Q8', 'P16', 'P19', 'P22' ], $iterator, 'second iteration' );
	}

	public function testInitialPosition() {
		$reader = new ExtractedDumpReader( ( new \JsonDumpData() )->getFiveEntitiesDumpPath() );

		$iterator = new EntityDumpIterator(
			( new JsonDumpFactory() )->newObjectDumpIterator( $reader ),
			$this->newCurrentEntityDeserializer()
		);

		$iterator->rewind();
		$this->assertSame( 'Q1', $iterator->current()->getId()->getSerialization() );

		$iterator->next();
		$this->assertSame( 'Q8', $iterator->current()->getId()->getSerialization() );

		$newReader = new ExtractedDumpReader(
			( new \JsonDumpData() )->getFiveEntitiesDumpPath(),
			$reader->getPosition()
		);

		$newIterator = new EntityDumpIterator(
			( new JsonDumpFactory() )->newObjectDumpIterator( $newReader ),
			$this->newCurrentEntityDeserializer()
		);

		$this->assertFindsEntities( [ 'P16', 'P19', 'P22' ], $newIterator );
	}

	public function testGivenFileWithInvalidEntities_errorsAreReported() {
		$errors = [];

		$iterator = $this->newIteratorForFile(
			__DIR__ . '/../data/3valid-2invalid.json',
			function( $errorMessage ) use ( &$errors ) {
				$errors[] = $errorMessage;
			}
		);

		$iterator->rewind();
		while ( $iterator->valid() ) {
			$iterator->next();
		}

		$this->assertContainsOnly( 'string', $errors );
		$this->assertCount( 2, $errors );
	}

	public function testGivenFileWithInvalidJsonLine_errorIsRecorded() {
		$errors = [];

		$iterator = $this->newIteratorForFile(
			__DIR__ . '/../data/invalid-json.json',
			function( $errorMessage ) use ( &$errors ) {
				$errors[] = $errorMessage;
			}
		);

		iterator_to_array( $iterator );

		$this->assertContainsOnly( 'string', $errors );
		$this->assertCount( 1, $errors );
	}

}
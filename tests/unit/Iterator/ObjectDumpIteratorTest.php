<?php

namespace Tests\Wikibase\JsonDumpReader\Iterator;

use PHPUnit\Framework\TestCase;
use Wikibase\JsonDumpReader\JsonDumpFactory;
use Wikibase\JsonDumpReader\Reader\FakeDumpReader;

/**
 * @covers \Wikibase\JsonDumpReader\Iterator\ObjectDumpIterator
 * @covers \Wikibase\JsonDumpReader\Reader\FakeDumpReader
 * @covers \Wikibase\JsonDumpReader\JsonDumpFactory
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ObjectDumpIteratorTest extends TestCase {

	private function getIteratorWithReaderReturning( $lines ) {
		return ( new JsonDumpFactory() )->newObjectDumpIterator( new FakeDumpReader( $lines ) );
	}

	public function testGivenValidJson_allElementsAreDecodedInIteration() {
		$lines = [ '{"id": "Q1"}', '{"id": "Q2"}', '{"id": "Q3"}' ];

		$objectIterator = $this->getIteratorWithReaderReturning( $lines );

		$this->assertSame(
			[
				[ 'id' => 'Q1' ],
				[ 'id' => 'Q2' ],
				[ 'id' => 'Q3' ],
			],
			iterator_to_array( $objectIterator )
		);
	}

	public function testGivenValidJson_currentReturnsDecodedValues() {
		$lines = [ '{"id": "Q1"}', '{"id": "Q2"}', '{"id": "Q3"}' ];

		$objectIterator = $this->getIteratorWithReaderReturning( $lines );

		$this->assertNull( $objectIterator->current() );

		$objectIterator->rewind();
		$this->assertSame( [ 'id' => 'Q1' ], $objectIterator->current() );

		$objectIterator->next();
		$this->assertSame( [ 'id' => 'Q2' ], $objectIterator->current() );

		$objectIterator->next();
		$this->assertSame( [ 'id' => 'Q3' ], $objectIterator->current() );
	}

	public function testGivenValidJson_keyReturnsZeroBasedIntegers() {
		$lines = [ '{"id": "Q1"}', '{"id": "Q2"}', '{"id": "Q3"}' ];

		$objectIterator = $this->getIteratorWithReaderReturning( $lines );

		$this->assertSame( 0, $objectIterator->key() );

		$objectIterator->rewind();
		$this->assertSame( 0, $objectIterator->key() );

		$objectIterator->next();
		$this->assertSame( 1, $objectIterator->key() );

		$objectIterator->next();
		$this->assertSame( 2, $objectIterator->key() );
	}

	public function testGivenInvalidJson_invalidElementIsSkippedAndErrorRecorded() {
		$errors = [];

		$objectIterator = ( new JsonDumpFactory() )->newObjectDumpIterator(
			new FakeDumpReader( [
				'{"id": "Q1"}',
				'{why discriminate against non-JSON? the world is unfair}',
				'{"id": "Q3"}'
			] ),
			function( $errorMessage ) use ( &$errors ) {
				$errors[] = $errorMessage;
			}
		);

		$objectIterator->rewind();
		$this->assertCount( 0, $errors );
		$this->assertSame( [ 'id' => 'Q1' ], $objectIterator->current() );

		$objectIterator->next();
		$this->assertCount( 1, $errors );
		$this->assertContainsOnly( 'string', $errors );
		$this->assertSame( [ 'id' => 'Q3' ], $objectIterator->current() );
	}

}
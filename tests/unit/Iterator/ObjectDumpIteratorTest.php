<?php

namespace Tests\Wikibase\JsonDumpReader\Iterator;

use Wikibase\JsonDumpReader\JsonDumpFactory;
use Wikibase\JsonDumpReader\Reader\FakeDumpReader;

/**
 * @covers Wikibase\JsonDumpReader\Iterator\ObjectDumpIterator
 * @covers Wikibase\JsonDumpReader\Reader\FakeDumpReader
 * @covers Wikibase\JsonDumpReader\JsonDumpFactory
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ObjectDumpIteratorTest extends \PHPUnit_Framework_TestCase {

	public function testGivenFileWithFiveEntities_fiveEntityAreFound() {
		$lines = [ '{"id": "Q1"}', '{"id": "Q2"}', '{"id": "Q3"}' ];

		$objectIterator = ( new JsonDumpFactory() )->newObjectDumpIterator( new FakeDumpReader( $lines ) );

		$this->assertSame(
			[
				[ "id" => "Q1" ],
				[ "id" => "Q2" ],
				[ "id" => "Q3" ],
			],
			iterator_to_array( $objectIterator )
		);
	}

}
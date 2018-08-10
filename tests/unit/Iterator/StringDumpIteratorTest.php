<?php

declare( strict_types = 1 );

namespace Tests\Wikibase\JsonDumpReader\Iterator;

use PHPUnit\Framework\TestCase;
use Wikibase\JsonDumpReader\JsonDumpFactory;
use Wikibase\JsonDumpReader\Reader\FakeDumpReader;

/**
 * @covers \Wikibase\JsonDumpReader\JsonDumpFactory
 * @covers \Wikibase\JsonDumpReader\Reader\FakeDumpReader
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class StringDumpIteratorTest extends TestCase {

	public function testAdaptsDumpReaderToIterator() {
		$lines = [ 'foo', 'bar', 'baz' ];
		$iterator = ( new JsonDumpFactory() )->newStringDumpIterator( new FakeDumpReader( $lines ) );

		$this->assertSame(
			$lines,
			iterator_to_array( $iterator )
		);
	}

}
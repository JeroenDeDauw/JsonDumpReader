<?php

declare( strict_types = 1 );

namespace Wikibase\JsonDumpReader;

use Deserializers\Deserializer;
use Iterator;
use Wikibase\JsonDumpReader\Iterator\EntityDumpIterator;
use Wikibase\JsonDumpReader\Iterator\ObjectDumpIterator;
use Wikibase\JsonDumpReader\Reader\Bz2DumpReader;
use Wikibase\JsonDumpReader\Reader\ExtractedDumpReader;
use Wikibase\JsonDumpReader\Reader\GzDumpReader;

/**
 * Package public
 * @since 1.0.0
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class JsonDumpFactory {

	/**
	 * Creates a DumpReader that can read lines from a bz2 compressed JSON dump.
	 * @since 1.1.0
	 *
	 * @param string $dumpFilePath
	 * @param int $initialPosition since 1.3.0
	 *
	 * @return SeekableDumpReader
	 */
	public function newGzDumpReader( string $dumpFilePath, int $initialPosition = 0 ): SeekableDumpReader {
		return new GzDumpReader( $dumpFilePath, $initialPosition );
	}

	/**
	 * Creates a DumpReader that can read lines from an uncompressed JSON dump.
	 * @since 1.0.0
	 *
	 * @param string $dumpFilePath
	 * @param int $initialPosition
	 *
	 * @return SeekableDumpReader
	 */
	public function newExtractedDumpReader( string $dumpFilePath, int $initialPosition = 0 ): SeekableDumpReader {
		return new ExtractedDumpReader( $dumpFilePath, $initialPosition );
	}

	/**
	 * Creates a DumpReader that can read lines from a bz2 compressed JSON dump.
	 * @since 1.0.0
	 *
	 * @param string $dumpFilePath
	 *
	 * @return DumpReader
	 */
	public function newBz2DumpReader( string $dumpFilePath ): DumpReader {
		return new Bz2DumpReader( $dumpFilePath );
	}

	/**
	 * Creates an Iterator over each JSON serialized Entity in the dump.
	 * @since 1.0.0
	 *
	 * @param DumpReader $dumpReader
	 * @param callable $onError Gets called with a single string parameter on error
	 *
	 * @return Iterator string[]
	 */
	public function newStringDumpIterator( DumpReader $dumpReader, callable $onError = null ): Iterator {
		$iterator = new \RewindableGenerator( function() use ( $dumpReader, $onError ) {
			while ( true ) {
				try {
					$line = $dumpReader->nextJsonLine();

					if ( $line === null ) {
						return;
					}

					yield $line;
				}
				catch ( DumpReadingException $ex ) {
					if ( $onError !== null ) {
						call_user_func( $onError, $ex->getMessage() );
					}
				}
			}
		} );

		$iterator->onRewind( function() use ( $dumpReader ) {
			$dumpReader->rewind();
		} );

		return $iterator;
	}

	/**
	 * Creates an Iterator over each Entity in the dump as PHP array/object in the JSON format.
	 * This is essentially a json_decode map of the string dump iterator.
	 * @since 1.0.0
	 *
	 * @param DumpReader $dumpReader
	 * @param callable $onError Gets called with a single string parameter on error
	 *
	 * @return Iterator array[]
	 */
	public function newObjectDumpIterator( DumpReader $dumpReader, callable $onError = null ): Iterator {
		$iterator = new ObjectDumpIterator(
			$this->newStringDumpIterator( $dumpReader, $onError )
		);

		$iterator->onError( $onError );

		return $iterator;
	}

	/**
	 * Creates an Iterator over each Entity in the dump, fully deserialized as EntityDocument.
	 * @since 1.0.0
	 *
	 * @param DumpReader $dumpReader
	 * @param Deserializer $entityDeserializer
	 * @param callable $onError Gets called with a single string parameter on error
	 *
	 * @return Iterator EntityDocument[]
	 */
	public function newEntityDumpIterator( DumpReader $dumpReader, Deserializer $entityDeserializer,
		callable $onError = null ): Iterator {

		$iterator = new EntityDumpIterator(
			$this->newObjectDumpIterator( $dumpReader, $onError ),
			$entityDeserializer
		);

		$iterator->onError( $onError );

		return $iterator;
	}

}
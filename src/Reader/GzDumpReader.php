<?php

namespace Wikibase\JsonDumpReader\Reader;

use Wikibase\JsonDumpReader\DumpReader;
use Wikibase\JsonDumpReader\DumpReadingException;

/**
 * Package private
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class GzDumpReader implements DumpReader {

	/**
	 * @var string
	 */
	private $dumpFile;

	/**
	 * @var resource|null
	 */
	private $handle = null;

	/**
	 * @param string $dumpFilePath
	 */
	public function __construct( $dumpFilePath ) {
		$this->dumpFile = $dumpFilePath;
	}

	public function __destruct() {
		$this->closeReader();
	}

	private function closeReader() {
		if ( is_resource( $this->handle ) ) {
			gzclose( $this->handle );
			$this->handle = null;
		}
	}

	public function rewind() {
		$this->closeReader();
		$this->initReader();
	}

	private function initReader() {
		if ( $this->handle === null ) {
			$this->handle = @gzopen( $this->dumpFile, 'r' );

			if ( $this->handle === false ) {
				throw new DumpReadingException( 'Could not open file: ' . $this->dumpFile );
			}
		}
	}

	/**
	 * @return string|null
	 * @throws DumpReadingException
	 */
	public function nextJsonLine() {
		$this->initReader();

		do {
			if ( gzeof( $this->handle ) ) {
				return null;
			}

			$line = @gzgets( $this->handle );

			if ( $line === false ) {
				return null;
			}
		}
		while ( $line === '' || $line{0} !== '{' );

		return rtrim( $line, ",\n\r" );
	}

}

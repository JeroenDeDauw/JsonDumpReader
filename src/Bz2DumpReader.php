<?php

namespace Wikibase\JsonDumpReader;

/**
 * @since 1.0.0
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class Bz2DumpReader implements DumpLineReader {

	/**
	 * @var string
	 */
	private $dumpFile;

	/**
	 * @var resource
	 */
	private $handle;

	/**
	 * @param string $dumpFilePath
	 */
	public function __construct( $dumpFilePath ) {
		$this->dumpFile = $dumpFilePath;

		$this->initReader();
	}

	private function initReader() {
		$this->handle = @bzopen( $this->dumpFile, 'r' );

		if ( $this->handle === false ) {
			throw new \RuntimeException( 'Could not open file: ' . $this->dumpFile );
		}
	}

	public function __destruct() {
		$this->closeReader();
	}

	private function closeReader() {
		bzclose( $this->handle );
	}

	public function rewind() {
		$this->closeReader();
		$this->initReader();
	}

	/**
	 * @return string|null
	 */
	public function nextJsonLine() {
		do {
			$line = fgets( $this->handle );

			if ( $line === false ) {
				return null;
			}

			if ( $line{0} === '{' ) {
				return rtrim( $line, ",\n\r" );
			}
		} while ( true );

		return null;
	}

}

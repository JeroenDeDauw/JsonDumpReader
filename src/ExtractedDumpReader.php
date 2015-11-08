<?php

namespace Wikibase\JsonDumpReader;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ExtractedDumpReader implements DumpReader {

	/**
	 * @var string
	 */
	private $dumpFile;

	/**
	 * @var int
	 */
	private $initialPosition;

	/**
	 * @var resource
	 */
	private $handle;

	/**
	 * @param string $dumpFilePath
	 * @param int $initialPosition
	 */
	public function __construct( $dumpFilePath, $initialPosition = 0 ) {
		$this->dumpFile = $dumpFilePath;
		$this->initialPosition = $initialPosition;

		$this->initReader();
	}

	private function initReader() {
		$this->handle = fopen( $this->dumpFile, 'r' );
	}

	public function __destruct() {
		$this->closeReader();
	}

	private function closeReader() {
		fclose( $this->handle );
	}

	public function rewind() {
		fseek( $this->handle, $this->initialPosition );
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

	/**
	 * @return int
	 */
	public function getPosition() {
		if ( PHP_INT_SIZE < 8 ) {
			throw new \RuntimeException( 'Cannot reliably get the file position on 32bit PHP' );
		}

		return ftell( $this->handle );
	}

	/**
	 * @param int $position
	 */
	public function seekToPosition( $position ) {
		fseek( $this->handle, $position );
	}

}

<?php

namespace Wikibase\JsonDumpReader\Reader;

use RuntimeException;
use Wikibase\JsonDumpReader\DumpReader;
use Wikibase\JsonDumpReader\DumpReadingException;

/**
 * Package public
 * @since 1.0.0
 *
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
	}

	private function initReader() {
		if ( $this->handle === null ) {
			$this->handle = @fopen( $this->dumpFile, 'r' );

			if ( !is_resource( $this->handle ) ) {
				throw new DumpReadingException( 'Could not open file: ' . $this->dumpFile );
			}

			fseek( $this->handle, $this->initialPosition );
		}
	}

	public function __destruct() {
		$this->closeReader();
	}

	private function closeReader() {
		if ( is_resource( $this->handle ) ) {
			fclose( $this->handle );
		}
	}

	public function rewind() {
		if ( $this->handle !== null ) {
			fseek( $this->handle, $this->initialPosition );
		}
	}

	/**
	 * @return string|null
	 * @throws DumpReadingException
	 */
	public function nextJsonLine() {
		$this->initReader();

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
	 * @throws RuntimeException
	 */
	public function getPosition() {
		if ( PHP_INT_SIZE < 8 ) {
			throw new RuntimeException( 'Cannot reliably get the file position on 32bit PHP' );
		}

		$this->initReader();
		return ftell( $this->handle );
	}

	/**
	 * @param int $position
	 */
	public function seekToPosition( $position ) {
		$this->initReader();
		fseek( $this->handle, $position );
	}

}

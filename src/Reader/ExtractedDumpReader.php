<?php

declare( strict_types = 1 );

namespace Wikibase\JsonDumpReader\Reader;

use Wikibase\JsonDumpReader\DumpReadingException;
use Wikibase\JsonDumpReader\SeekableDumpReader;

/**
 * Package private
 * Was public in 1.0.x and 1.1.x. No breaking changes before 2.0.
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ExtractedDumpReader implements SeekableDumpReader {

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

			$this->seekToPosition( $this->initialPosition );
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

	public function rewind(): void {
		if ( $this->handle !== null ) {
			fseek( $this->handle, $this->initialPosition );
		}
	}

	/**
	 * @return string|null
	 * @throws DumpReadingException
	 */
	public function nextJsonLine(): ?string {
		$this->initReader();

		while ( true ) {
			$line = fgets( $this->handle );

			if ( $line === false ) {
				return null;
			}

			if ( $line[0] === '{' ) {
				return rtrim( $line, ",\n\r" );
			}
		}

		return null;
	}

	/**
	 * @return int
	 * @throws DumpReadingException
	 */
	public function getPosition(): int {
		if ( PHP_INT_SIZE < 8 ) {
			throw new DumpReadingException( 'Cannot reliably get the file position on 32bit PHP' );
		}

		$this->initReader();
		$position = @ftell( $this->handle );

		if ( !is_int( $position ) ) {
			throw new DumpReadingException( 'Could not tell the position of the file handle' );
		}

		return $position;
	}

	/**
	 * @param int $position
	 * @throws DumpReadingException
	 */
	public function seekToPosition( int $position ): void {
		$this->initReader();
		$seekResult = @fseek( $this->handle, $position );

		if ( $seekResult !== 0 ) {
			throw new DumpReadingException( 'Seeking to position failed' );
		}
	}

}

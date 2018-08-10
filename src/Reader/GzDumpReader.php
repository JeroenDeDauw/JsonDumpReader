<?php

declare( strict_types = 1 );

namespace Wikibase\JsonDumpReader\Reader;

use Wikibase\JsonDumpReader\SeekableDumpReader;
use Wikibase\JsonDumpReader\DumpReadingException;

/**
 * Package private
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class GzDumpReader implements SeekableDumpReader {

	/**
	 * @var string
	 */
	private $dumpFile;

	/**
	 * @var int
	 */
	private $initialPosition;

	/**
	 * @var resource|null
	 */
	private $handle = null;

	/**
	 * @param string $dumpFilePath
	 * @param int $initialPosition
	 */
	public function __construct( $dumpFilePath, $initialPosition = 0 ) {
		$this->dumpFile = $dumpFilePath;
		$this->initialPosition = $initialPosition;
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

	public function rewind(): void {
		$this->closeReader();
		$this->initReader();
	}

	private function initReader() {
		if ( $this->handle === null ) {
			$this->handle = @gzopen( $this->dumpFile, 'r' );

			if ( $this->handle === false ) {
				throw new DumpReadingException( 'Could not open file: ' . $this->dumpFile );
			}

			$this->seekToPosition( $this->initialPosition );
		}
	}

	/**
	 * @return string|null
	 * @throws DumpReadingException
	 */
	public function nextJsonLine(): ?string {
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

	/**
	 * @return int
	 * @throws DumpReadingException
	 */
	public function getPosition(): int {
		if ( PHP_INT_SIZE < 8 ) {
			throw new DumpReadingException( 'Cannot reliably get the file position on 32bit PHP' );
		}

		$this->initReader();
		$position = @gztell( $this->handle );

		if ( !is_int( $position ) ) {
			throw new DumpReadingException( 'Could not tell the position of the file handle' );
		}

		return $position;
	}

	/**
	 * @param int $position
	 * @throws DumpReadingException
	 */
	public function seekToPosition( $position ) {
		$this->initReader();
		$seekResult = @gzseek( $this->handle, $position );

		if ( $seekResult !== 0 ) {
			throw new DumpReadingException( 'Seeking to position failed' );
		}
	}

}

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
class Bz2DumpReader implements DumpReader {

	/**
	 * @var string
	 */
	private $dumpFile;

	/**
	 * @var resource|null
	 */
	private $handle = null;

	private $lines = [ '' ];

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
			bzclose( $this->handle );
			$this->handle = null;
		}
	}

	public function rewind() {
		$this->closeReader();
		$this->initReader();
		$this->lines = [ '' ];
	}

	private function initReader() {
		if ( $this->handle === null ) {
			$this->handle = @bzopen( $this->dumpFile, 'r' );

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

		while ( true ) {
			$line = $this->nextLine();

			if ( $line === null ) {
				return null;
			}

			if ( $line === '' ) {
				continue;
			}

			if ( $line{0} === '{' ) {
				return rtrim( $line, ",\n\r" );
			}
		}

		return null;
	}

	private function nextLine() {
		while ( !feof( $this->handle ) && count( $this->lines ) === 1 ) {
			$readString = bzread( $this->handle, 4096 );

			if( bzerrno( $this->handle ) !== 0 ) {
				throw new DumpReadingException( 'Decompression error: ' . bzerrstr( $this->handle ) );
			}

			if ( $readString === false ) {
				break;
			}

			$this->lines[0] .= $readString;
			$this->lines = explode( "\n", $this->lines[0] );
		}

		return array_shift( $this->lines );
	}

}

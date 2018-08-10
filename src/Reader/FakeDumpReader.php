<?php

declare( strict_types = 1 );

namespace Wikibase\JsonDumpReader\Reader;

use Wikibase\JsonDumpReader\DumpReader;

/**
 * Package public
 * @since 1.0.0
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class FakeDumpReader implements DumpReader {

	/**
	 * @var string[]
	 */
	private $lines;

	/**
	 * @param string $lines
	 */
	public function __construct( $lines ) {
		$this->lines = $lines;
		$this->rewind();
	}

	public function rewind(): void {
		reset( $this->lines );
	}

	/**
	 * @return string|null
	 */
	public function nextJsonLine(): ?string {
		$current = current( $this->lines );

		if ( $current === false ) {
			return null;
		}
		else {
			next( $this->lines );
			return $current;
		}
	}

}

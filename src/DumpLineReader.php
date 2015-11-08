<?php

namespace Wikibase\JsonDumpReader;

/**
 * @since 1.0.0
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface DumpLineReader {

	/**
	 * Returns the next JSON object (as string) or null if the end of the dump has been reached.
	 *
	 * @return string|null
	 */
	public function nextJsonLine();

	/**
	 * Rewinds the reader to its initial position.
	 */
	public function rewind();

}
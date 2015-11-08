<?php

namespace Wikibase\JsonDumpReader;

use Deserializers\Deserializer;
use Deserializers\Exceptions\DeserializationException;
use Wikibase\DataModel\Entity\EntityDocument;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class JsonDumpIterator implements \Iterator {

	/**
	 * @var Deserializer
	 */
	private $deserializer;

	/**
	 * @var JsonDumpReader
	 */
	private $dumpReader;

	/**
	 * @var callable|null
	 */
	private $errorReporter = null;

	/**
	 * @var EntityDocument|null
	 */
	private $current = null;

	/**
	 * @var string|null
	 */
	private $currentJson = null;

	public function __construct( DumpLineReader $dumpReader, Deserializer $entityDeserializer ) {
		$this->dumpReader = $dumpReader;
		$this->deserializer = $entityDeserializer;
	}

	/**
	 * Sets a callback that will be called with a string message when an error occurs.
	 * Overrides previously set callbacks.
	 *
	 * @since 1.0.0
	 *
	 * @param callable $errorReporter
	 */
	public function onError( callable $errorReporter ) {
		$this->errorReporter = $errorReporter;
	}

	/**
	 * @return EntityDocument|null
	 */
	public function current() {
		return $this->current;
	}

	/**
	 * @return EntityDocument|null
	 */
	public function getCurrentJson() {
		return $this->currentJson;
	}

	/**
	 * @return EntityDocument|null
	 */
	public function next() {
		$data = $this->getNextFromJson();

		$this->currentJson = $data[0];
		$this->current = $data[1];

		return $this->current;
	}

	private function getNextFromJson() {
		do {
			$json = $this->dumpReader->nextJsonLine();
			if ( $json === null ) {
				return null;
			}

			$data = json_decode( $json, true );
			if ( $data === null ) {
				$this->reportError( json_last_error_msg() );
				return [ null, null ];
			}

			try {
				return [ $json, $this->deserializer->deserialize( $data ) ];
			}
			catch ( DeserializationException $ex ) {
				$this->reportError( $ex->getMessage() );
			}
		}
		while ( true );

		return null;
	}

	private function reportError( $errorMessage ) {
		if ( $this->errorReporter !== null ) {
			call_user_func( $this->errorReporter, $errorMessage );
		}
	}

	public function key() {
		return $this->current->getId()->getSerialization();
	}

	public function valid() {
		return $this->current !== null;
	}

	public function rewind() {
		$this->current = null;
		$this->currentJson = null;
		$this->dumpReader->rewind();
		$this->next();
	}

}

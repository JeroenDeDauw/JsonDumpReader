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
	 * @var DumpIterationWatcher
	 */
	private $watcher;

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
		//$this->watcher = $watcher;
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
			if ( $json === null ) return null;

			$data = json_decode( $json, true );
			if ( $data === null ) {
				//$this->watcher->onError( json_last_error_msg() );
				return null;
			}

			try {
				return [ $json, $this->deserializer->deserialize( $data ) ];
			}
			catch ( DeserializationException $ex ) {
				//$this->watcher->onError( $ex->getMessage() );
			}
		}
		while ( true );

		return null;
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

<?php

namespace Wikibase\JsonDumpReader\Iterator;

use Deserializers\Deserializer;
use Deserializers\Exceptions\DeserializationException;
use Iterator;
use Wikibase\DataModel\Entity\EntityDocument;

/**
 * Package private
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EntityDumpIterator implements Iterator {

	/**
	 * @var Deserializer
	 */
	private $deserializer;

	/**
	 * @var Iterator
	 */
	private $dumpIterator;

	/**
	 * @var callable|null
	 */
	private $errorReporter = null;

	/**
	 * @var EntityDocument|null
	 */
	private $current = null;

	public function __construct( Iterator $objectIterator, Deserializer $entityDeserializer ) {
		$this->dumpIterator = $objectIterator;
		$this->deserializer = $entityDeserializer;
	}

	/**
	 * @param callable|null $errorReporter
	 */
	public function onError( callable $errorReporter = null ) {
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
	public function next() {
		$data = $this->getNextFromObject();

		$this->current = $data;

		return $this->current;
	}

	private function getNextFromObject() {
		while ( true ) {
			$jsonData = $this->dumpIterator->current();
			$this->dumpIterator->next();

			if ( $jsonData === null ) {
				return null;
			}

			try {
				return $this->deserializer->deserialize( $jsonData );
			}
			catch ( DeserializationException $ex ) {
				$this->reportError( $ex->getMessage() );
			}
		}

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
		$this->dumpIterator->rewind();
		$this->next();
	}

}

<?php

namespace Wikibase\JsonDumpReader\Iterator;

use Iterator;

/**
 * Package private
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ObjectDumpIterator implements Iterator {

	/**
	 * @var callable|null
	 */
	private $errorReporter = null;

	/**
	 * @var string|null
	 */
	private $current = null;

	/**
	 * @var int
	 */
	private $key = 0;

	/**
	 * @var Iterator
	 */
	private $stringIterator;

	public function __construct( Iterator $stringIterator ) {
		$this->stringIterator = $stringIterator;
	}

	/**
	 * @param callable|null $errorReporter
	 */
	public function onError( callable $errorReporter = null ) {
		$this->errorReporter = $errorReporter;
	}

	/**
	 * @return string|null
	 */
	public function current() {
		return $this->current;
	}

	/**
	 * @return string|null
	 */
	public function next() {
		$data = $this->getNextFromString();

		$this->current = $data;
		$this->key++;

		return $this->current;
	}

	private function getNextFromString() {
		while ( true ) {
			$jsonString = $this->stringIterator->current();
			$this->stringIterator->next();

			if ( $jsonString === null ) {
				return null;
			}

			$data = json_decode( $jsonString, true );
			if ( $data === null ) {
				$this->reportError( json_last_error_msg() );
			}
			else {
				return $data;
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
		return $this->key;
	}

	public function valid() {
		return $this->current !== null;
	}

	public function rewind() {
		$this->current = null;
		$this->key = -1;
		$this->stringIterator->rewind();
		$this->next();
	}

}

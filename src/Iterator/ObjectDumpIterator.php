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
		$this->stringIterator->next();
		$this->getCurrentFromString();

		return $this->current;
	}

	private function getCurrentFromString() {
		while ( true ) {
			$jsonString = $this->stringIterator->current();

			if ( $jsonString === null ) {
				$this->current = null;
				return;
			}

			$data = json_decode( $jsonString, true );
			if ( $data === null ) {
				$this->reportError( json_last_error_msg() );
				$this->stringIterator->next();
			}
			else {
				$this->current = $data;
				return;
			}
		}
	}

	private function reportError( $errorMessage ) {
		if ( $this->errorReporter !== null ) {
			call_user_func( $this->errorReporter, $errorMessage );
		}
	}

	public function key() {
		return $this->stringIterator->key();
	}

	public function valid() {
		return $this->current !== null;
	}

	public function rewind() {
		$this->current = null;
		$this->stringIterator->rewind();
		$this->getCurrentFromString();
	}

}

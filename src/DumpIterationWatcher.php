<?php

namespace Wikibase\JsonDumpReader;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface DumpIterationWatcher {

	/**
	 * @param string $errorMessage
	 */
	public function onError( $errorMessage );

}
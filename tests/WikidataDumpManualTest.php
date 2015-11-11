<?php

include_once __DIR__ . '/../vendor/autoload.php';

use Wikibase\JsonDumpReader\JsonDumpFactory;

$factory = new JsonDumpFactory();
$iterator = $factory->newStringDumpIterator( $factory->newBz2DumpReader( $argv[1] ) );

foreach ( new \LimitIterator( $iterator, 0, 1010 ) as $i => $line ) {
	echo $i . ': ' . substr( $line, 0, 150 ) . "\n";
}
